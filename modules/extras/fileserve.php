<?php
/*
Plugin Name: FileServe API
Description: Check links to FileServe files using the FileServe API.
Version: 1.0
Author: Janis Elsts

ModuleID: fileserve-checker
ModuleCategory: checker
ModuleContext: on-demand
ModuleLazyInit: true
ModuleClassName: blcFileServeChecker
ModulePriority: 100

ModuleCheckerUrlPattern: @^http://(?:www\.)?fileserve\.com/file/([\w\d]+?)(?:/|$|[?#])@i
*/

/**
 * FileServe API link checker.
 *
 * @package Broken Link Checker
 * @author Janis Elsts
 * @access public
 */
class blcFileServeChecker extends blcChecker {
	private $fileserve_api_url = 'http://app.fileserve.com/api/download/free/';

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
	 * Check a FileServe link.
	 *
	 * See the FileServe API documentation for details:
	 * http://app.fileserve.com/api/download/
	 *
	 * @param string $url File URL.
	 * @return array
	 */
	function check($url){
		$result = array(
			'final_url' => $url,
			'redirect_count' => 0,
			'timeout' => false,
			'broken' => false,
			'log' => sprintf("<em>(%s)</em>\n\n", __('Using FileServe API', 'broken-link-checker')),
			'result_hash' => '',
			'status_code' => '',
			'status_text' => '',
		);

		//We know the URL will match because ModuleCheckerUrlPattern matched.
		preg_match('@^http://(?:www\.)?fileserve\.com/file/([\w\d]+?)(?:/|$|[?#])@i', $url, $matches);
		$file_id = $matches[1];

		$conf = blc_get_configuration();
		$args = array(
			'timeout' => $conf->options['timeout'],
			'body' => array(
				'shorten' => $file_id
			),
		);

		$start = microtime_float();
		$response = wp_remote_post($this->fileserve_api_url, $args);
		$result['request_duration'] = microtime_float() - $start;

		$error_code = 0;

		if ( is_wp_error($response) ){
			$result['log'] .= "Error : " . $response->get_error_message();
        	$result['broken'] = true;
        	$result['http_code'] = 0;
		} else {
			$result['http_code'] = intval($response['response']['code']);

			if ( $result['http_code'] == 200 ){
				//In this case, the HTTP code returned by is not meaningful in itself,
				//so we won't store it or display it to the user.
				$result['http_code'] = 0;

				$json = json_decode($response['body'], false);

				if ( isset($json->error_code) ) {
					$error_code = intval($json->error_code);
				}
				$failure_codes = array(
					310 => 'Invalid request',
					403 => 'Not premium',
					404 => 'Invalid link',
					601 => 'Limited free download',
					602 => 'Number of concurrent download exceeded',
					603 => 'Too many invalid capcha',
					605 => 'Expired premium',
					606 => 'Invalid file ID',
					607 => 'File not available',
					608 => 'File not available',
				);

				if ( array_key_exists($error_code, $failure_codes) ) {
					$result['broken'] = true;
					$result['status_code'] = BLC_LINK_STATUS_ERROR;
					$result['status_text'] = __('Not Found', 'broken-link-checker');

					$result['log'] .= sprintf(
						__('FileServe : %d %s', 'broken-link-checker') . "\n",
						$error_code,
						$failure_codes[$error_code]
					);
				} else {
					$result['status_code'] = BLC_LINK_STATUS_OK;
					$result['status_text'] = _x('OK', 'link status', 'broken-link-checker');
				}

				//$result['log'] .= "API response :\n" . htmlspecialchars(print_r((array)$json, true));
				$result['log'] .= "API response :\n<code>" . htmlspecialchars($response['body'])."</code>\n";

			} else {
				//Unexpected error.
				$result['log'] .= $response['body'];
				$result['broken'] = true;
			}
		}

		//Generate the result hash (used for detecting false positives)
        $result['result_hash'] = implode('|', array(
        	'fileserve',
			$result['http_code'],
			$result['broken']?'broken':'0',
			$result['timeout']?'timeout':'0',
			$error_code
		));

		return $result;
	}

}

