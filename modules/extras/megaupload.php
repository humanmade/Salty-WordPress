<?php
/**
 * Megaupload.com has been seized by the USA government, rendering this module irrelevant.
 */

/*
Plugin Name: MegaUpload API
Description: Check links to MegaUpload files.
Version: 1.0
Author: Janis Elsts

ModuleID: megaupload-checker
ModuleCategory: checker
ModuleContext: on-demand
ModuleLazyInit: true
ModuleClassName: blcMegaUploadChecker
ModulePriority: 100

ModuleCheckerUrlPattern: @^http://[\w\.]*?megaupload\.com/.*?(?:\?|&)d=([0-9A-Za-z]+)@
*/

/**
 * MegaUpload API link checker.
 *
 * @package Broken Link Checker
 * @author Janis Elsts
 * @access public
 */
class blcMegaUploadChecker extends blcChecker {
	
	/**
	 * Determine if the checker can parse a specific URL.
	 * Always returns true because the ModuleCheckerUrlPattern header constitutes sufficient verification.
	 * 
	 * @param string $url
	 * @param array $parsed
	 * @return bool True.
	 */
	function can_check($url, $parsed){
		return true;
	}
	
	/**
	 * Check a MegaUpload link.
	 * 
	 * @param string $url
	 * @return array
	 */
	function check($url){
		$result = array(
			'final_url' => $url,
			'redirect_count' => 0,
			'timeout' => false,
			'broken' => false,
			'log' => "<em>(Using MegaUpload API)</em>\n\n",
			'http_code' => 0,
			'result_hash' => '',
		);
		
		//Extract the file ID from the URL (we know it's there because the module's URL pattern verifies that)
		$components = parse_url($url);
		parse_str($components['query'], $query);
		$file_id = $query['d'];
		
		$start = microtime_float();
		$info = $this->check_files($file_id);
		$result['request_duration'] = microtime_float() - $start;
				
		$file_status = 'unknown';
		if ( is_wp_error($info) ){
			
			//An unexpected error. Connection problems, IP blocks - it all goes here.
			$result['broken'] = true;
			if ( $data = $info->get_error_data() ){
				//Check for the "domain seized" message.
				$code = isset($data['response']['code']) ? $data['response']['code'] : 0;
				$body = isset($data['body']) ? $data['body'] : '';
				if ( ($code == 404) && (strpos($body, '<title>NOTICE</title>') !== false) ) {
					$result['log'] .= "The domain megaupload.com has been seized.";
					$result['status_code'] = BLC_LINK_STATUS_ERROR;
					$result['status_text'] = __('Not Found', 'broken-link-checker');
					$result['http_code'] = 404;
				} else {
					$result['log'] .= "Error : " . $info->get_error_message();
					$result['log'] .= "\n\nError data : " . print_r($data, true);
				}
			}
			
		} else {
			if ( array_key_exists($file_id, $info) ){
								
				$info = $info[$file_id];
				$file_status = $info['status'];
				
				switch($file_status){
					case '0': //OK
						$result['log'] .= 'File OK';
						$result['broken'] = false;
						
						if ( isset($info['name']) ){
							$result['log'] .= "\n" . sprintf(
								"Name : %s",
								$info['name']
							);
						}
						
						if ( isset($info['size']) ){
							$result['log'] .= "\n" . sprintf(
								"Size : %.0f KiB",
								round( floatval($info['size']) / 1024 )
							);
						}
						
						$result['status_code'] = BLC_LINK_STATUS_OK;
						$result['status_text'] = _x('OK', 'link status', 'broken-link-checker');
						
						break;
						
					case '1': //Invalid/removed
						$result['log'] .= 'File Not Found';
						$result['broken'] = true;
						$result['status_code'] = BLC_LINK_STATUS_ERROR;
						$result['status_text'] = __('Not Found', 'broken-link-checker');
						break;
						
					case '3': //Temporarily unavailable
						$result['log'] .= 'File Temporarily Unavailable';
						$result['broken'] = true;
						$result['status_code'] = BLC_LINK_STATUS_WARNING;
						$result['status_text'] = __('File Temporarily Unavailable', 'broken-link-checker');
						break;
						
					default: //Other codes are not documented anywhere.
						$result['log'] .= 'Received an unknown response code : ' . $file_status;
						$result['status_code'] = BLC_LINK_STATUS_INFO;
						$result['status_text'] = __('API Error', 'broken-link-checker');
				}
												
			} else {
				$result['log'] = "No info about file $file_id returned.";
			}
		}
		
		//Generate the result hash (used for detecting false positives)  
        $result['result_hash'] = implode('|', array(
        	'megaupload',
			$result['http_code'],
			$result['broken']?'broken':'0', 
			$result['timeout']?'timeout':'0',
			$file_status
		));
		
		return $result;
	}
    
    /**
     * Check the status of one or more MegaUpload files.
     * 
     * The MegaUpload API that is used in this function isn't documented anywhere.
     * The input and output data formats were reverse-engineered by sniffing the
	 * HTTP requests made by the "Mega Manager" tool. 
     * 
     * @param array|string $file_ids
     * @return array|WP_Error
     */
    function check_files($file_ids){
    	if ( is_string($file_ids) ){
    		$file_ids = array($file_ids);
    	}
    	
    	//The API expects input in this format : id0=file1id&id1=file2id&...
    	$request_ids = array();
    	$counter = 0;
    	
		foreach($file_ids as $file_id){
			$id = 'id' . $counter;
			$request_ids[$id] = $file_id; 
			$counter++;
		}
		
		$conf = blc_get_configuration();
		$args = array(
			'timeout' => $conf->options['timeout'],
			'body' => $request_ids,
		);
		
		//Submit the request
		$rez = wp_remote_post('http://www.megaupload.com/mgr_linkcheck.php', $args);
		
		if ( is_wp_error($rez) ){
			return $rez;
		}
		
		if ( ($rez['response']['code'] == 200) && (!empty($rez['body'])) ){
			$api_results = $this->parse_api_response($rez['body']);
			$results = array();
			
			//Resort the results by real file IDs			
			foreach($api_results as $id => $file_info){
				if ( !array_key_exists($id, $request_ids) ){
					continue;
				}
				
				$results[$request_ids[$id]] = $file_info;
			}
			
			return $results;
			
		} else {
			return new WP_Error(
				'megaupload_api_error', 
				"MegaUpload API Error", 
				$rez
			);
		}
    }
    
    /**
     * Parse a response received from the MegaUpload file check API 
     * 
     * @param string $response
     * @return array
     */
    function parse_api_response($response){
    	/*
    	The API response looks like this : 
    	0=www.megaupload.com&1=www.megaporn.com&id0=0&s=filesize&d=0&n=filename&id1=0&s=filesize&d=0&n=filename...
    	
		Despite appearances, it is not actually a valid query string. Each "idX=..." piece 
		needs to be parsed separately.
    	*/
    	
    	$pieces = preg_split('@&(?=id\d+=)@', $response);
    	$results = array();
    	
    	foreach($pieces as $piece){
    		//Skip the superfluous response fragments that don't begin with an ID
    		if ( substr($piece, 0, 2) != 'id' ){
    			continue;
    		}
    		
    		//Extract the "idX" key that identifies files in the request
    		$id = substr($piece, 0, strpos($piece, '='));
    		
    		//The per-file data can be parsed as a query string
    		parse_str($piece, $raw_data);
			    		
    		//Reformat
    		$file_data = array();
    		$file_data['status'] = $raw_data[$id];
    		if ( isset($raw_data['s']) ){
    			$file_data['size'] = $raw_data['s'];
    		}
    		if ( isset($raw_data['n']) ){
    			$file_data['name'] = $raw_data['n'];
    		}
    		if ( isset($raw_data['d']) ){ //No idea what this key is for.
    			$file_data['d'] = $raw_data['d']; 
    		}
    		
    		$results[$id] = $file_data;
   		}
   		
   		return $results;
    }
	
}
