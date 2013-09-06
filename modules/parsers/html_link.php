<?php

/*
Plugin Name: HTML links
Description: Example : <code>&lt;a href="http://example.com/"&gt;link text&lt;/a&gt;</code>
Version: 1.0
Author: Janis Elsts

ModuleID: link
ModuleCategory: parser
ModuleClassName: blcHTMLLink
ModuleContext: on-demand
ModuleLazyInit: true

ModulePriority: 1000
*/

class blcHTMLLink extends blcParser {
	var $supported_formats = array('html');
	
  /**
   * Parse a string for HTML links - <a href="URL">anchor text</a>
   *
   * @param string $content The text to parse.
   * @param string $base_url The base URL to use for normalizing relative URLs. If ommitted, the blog's root URL will be used. 
   * @param string $default_link_text 
   * @return array An array of new blcLinkInstance objects. The objects will include info about the links found, but not about the corresponding container entity. 
   */
	function parse($content, $base_url = '', $default_link_text = ''){
		$instances = array();
		
		//remove all <code></code> blocks first
		$content = preg_replace('/<code[^>]*>.+?<\/code>/si', ' ', $content);
		
		//Find links
		$params = array(
			'base_url' => $base_url,
			'default_link_text' => $default_link_text,
		);
		$instances = $this->map($content, array($this, 'parser_callback'), $params);
		
		//The parser callback returns NULL when it finds an invalid link. Filter out those nulls
		//from the list of instances.
		$instances = array_filter($instances);
		
		return $instances;
	}
	
  /**
   * blcHTMLLink::parser_callback()
   *
   * @access private
   *
   * @param array $link
   * @param array $params
   * @return blcLinkInstance|null
   */
	function parser_callback($link, $params){
		$base_url = $params['base_url'];
		
		$url = $raw_url = $link['href'];
		$url = trim($url);
		//FB::log($url, "Found link");
		
		//Sometimes links may contain shortcodes. Execute them.
		$url = do_shortcode($url);
		
		//Skip empty URLs
		if ( empty($url) ){
			return null;
		};
		
		//Attempt to parse the URL
		$parts = @parse_url($url);
	    if(!$parts) {
			return null; //Skip invalid URLs
		};
		
		if ( !isset($parts['scheme']) ){
			//No sheme - likely a relative URL. Turn it into an absolute one.
			$url = $this->relative2absolute($url, $base_url); //$base_url comes from $params
		}
		
		//Skip invalid links (again)
		if ( !$url || (strlen($url)<6) ) {
			return null;
		}
		
		$text = strip_tags( $link['#link_text'] ); 
	    
	    //The URL is okay, create and populate a new link instance.
	    $instance = new blcLinkInstance();
	    
	    $instance->set_parser($this);
		$instance->raw_url = $raw_url;
	    $instance->link_text = $text;
	    
	    $link_obj = new blcLink($url); //Creates or loads the link
	    $instance->set_link($link_obj);
	    
	    return $instance;
	}
	
  /**
   * Change all links that have a certain URL to a new URL. 
   *
   * @param string $content Look for links in this string.
   * @param string $new_url Change the links to this URL.
   * @param string $old_url The URL to look for.
   * @param string $old_raw_url The raw, not-normalized URL of the links to look for. Optional. 
   *
   * @return array|WP_Error If successful, the return value will be an associative array with two
   * keys : 'content' - the modified content, and 'raw_url' - the new raw, non-normalized URL used
   * for the modified links. In most cases, the returned raw_url will be equal to the new_url.
   */
	function edit($content, $new_url, $old_url, $old_raw_url){
		if ( empty($old_raw_url) ){
			$old_raw_url = $old_url;
		}
		
		//Save the old & new URLs for use in the edit callback.
		$args = array(
			'old_url' => $old_raw_url,
			'new_url' => $new_url,
		);
		
		//Find all links and replace those that match $old_url.
		$content = $this->multi_edit($content, array(&$this, 'edit_callback'), $args);
		
		return array(
			'content' => $content,
			'raw_url' => $new_url, 
		);
	}
	
	function edit_callback($link, $params){
		if ($link['href'] == $params['old_url']){
			return array(
				'href' => $params['new_url'],
			);
		} else {
			return $link['#raw'];
		}
	}
	
  /**
   * Remove all links that have a certain URL, leaving anchor text intact.
   *
   * @param string $content	Look for links in this string.
   * @param string $url The URL to look for.
   * @param string $raw_url The raw, non-normalized version of the URL to look for. Optional.
   * @return string Input string with all matching links removed. 
   */
	function unlink($content, $url, $raw_url){
		if ( empty($raw_url) ){
			$raw_url = $url;
		}
		
		$args = array(
			'old_url' => $raw_url,
		);
		
		//Find all links and remove those that match $raw_url.
		$content = $this->multi_edit($content, array(&$this, 'unlink_callback'), $args);
		
		return $content;
	}
	
  /**
   * blcHTMLLink::unlink_callback()
   *
   * @access private
   * 
   * @param array $link
   * @param array $params
   * @return string
   */
	function unlink_callback($link, $params){
		//Skip links that don't match the specified URL
		if ($link['href'] != $params['old_url']){
			return $link['#raw'];
		}
		
		$config = blc_get_configuration();
		if ( $config->options['mark_removed_links'] ){
			//Leave only the anchor text + the removed_link CSS class
			return sprintf(
				'<span class="removed_link" title="%s">%s</span>',
				esc_attr($link['href']),
				$link['#link_text']
			); 
		} else {
			//Just the anchor text
			return $link['#link_text']; 
		}
	}
	
  /**
   * Get the link text for printing in the "Broken Links" table.
   * Sub-classes should override this method and display the link text in a way appropriate for the link type.
   *
   * @param blcLinkInstance $instance
   * @return string HTML 
   */
	function ui_get_link_text($instance, $context = 'display'){
		return $instance->link_text;
	}
	
 /**
   * Apply a callback function to all HTML links found in a string and return the results.
   *
   * The link data array will contain at least these keys :
   *  'href' - the URL of the link (with htmlentitydecode() already applied).
   *  '#raw' - the raw link code, e.g. the entire '<a href="...">...</a>' tag of a HTML link.
   *  '#offset' - the offset within $content at which the first character of the link tag was found.
   *  '#link_text' - the link's anchor text, if any. May contain HTML tags.
   * 
   * Any attributes of the link tag will also be included in the returned array as attr_name => attr_value
   * pairs. This function will also automatically decode any HTML entities found in attribute values.   
   *
   * @see blcParser::map()
   *
   * @param string $content A text string to parse for links. 
   * @param callback $callback Callback function to apply to all found links.  
   * @param mixed $extra If the optional $extra param. is supplied, it will be passed as the second parameter to the function $callback. 
   * @return array An array of all detected links after applying $callback to each of them.
   */
	function map($content, $callback, $extra = null){
		$results = array();
		
		//Find all links
		$links = blcUtility::extract_tags($content, 'a', false, true);
		
		//Iterate over the links and apply $callback to each
		foreach($links as $link){
			
			//Massage the found link into a form required for the callback function
			$param = $link['attributes'];
			$param = array_merge(
				$param,
				array(
					'#raw' => $link['full_tag'],
					'#offset' => $link['offset'],
					'#link_text' => $link['contents'],
					'href' => isset($link['attributes']['href'])?$link['attributes']['href']:'',
				)
			);
			
			//Prepare arguments for the callback
			$params = array($param);
			if ( isset($extra) ){
				$params[] = $extra;
			}
			
			//Execute & store :)
			$results[] = call_user_func_array($callback, $params);
		}
		
		return $results;
	}
	
  /**
   * Modify all HTML links found in a string using a callback function.
   *
   * The callback function should return either an associative array or a string. If 
   * a string is returned, the parser will replace the current link with the contents
   * of that string. If an array is returned, the current link will be modified/rebuilt
   * by substituting the new values for the old ones.
   *
   * htmlentities() will be automatically applied to attribute values (but not to #link_text).
   *
   * @see blcParser::multi_edit()
   *
   * @param string $content A text string containing the links to edit.
   * @param callback $callback Callback function used to modify the links.
   * @param mixed $extra If supplied, $extra will be passed as the second parameter to the function $callback. 
   * @return string The modified input string. 
   */
	function multi_edit($content, $callback, $extra = null){
		//Just reuse map() + a little helper func. to apply the callback to all links and get modified links
		$modified_links = $this->map($content, array(&$this, 'execute_edit_callback'), array($callback, $extra));
		
		//Replace each old link with the modified one
		$offset = 0;
		foreach($modified_links as $link){
			if ( isset($link['#new_raw']) ){
				$new_html = $link['#new_raw'];
			} else {
				//Assemble the new link tag
				$new_html = '<a';
				foreach ( $link as $name => $value ){
					
					//Skip special keys like '#raw' and '#offset'
					if ( substr($name, 0, 1) == '#' ){
						continue; 
					}
					
					$new_html .= sprintf(' %s="%s"', $name, esc_attr( $value )); 
				}
				$new_html .= '>' . $link['#link_text'] . '</a>';
			}
			
			$content = substr_replace($content, $new_html, $link['#offset'] + $offset, strlen($link['#raw']));
			//Update the replacement offset
			$offset += ( strlen($new_html) - strlen($link['#raw']) );
		}
		
		return $content; 
	}
	
  /**
   * Helper function for blcHtmlLink::multi_edit()
   * Applies the specified callback function to each link and merges 
   * the result with the current link attributes. If the callback returns
   * a replacement HTML tag instead, it will be stored in the '#new_raw'
   * key of the return array. 
   *
   * @access protected
   *
   * @param array $link
   * @param array $info The callback function and the extra argument to pass to that function (if any).
   * @return array
   */
	function execute_edit_callback($link, $info){
		list($callback, $extra) = $info;
		
		//Prepare arguments for the callback
		$params = array($link);
		if ( isset($extra) ){
			$params[] = $extra;
		}
		
		$new_link = call_user_func_array($callback, $params);
		
		if ( is_array($new_link) ){
			$link = array_merge($link, $new_link);
		} elseif (is_string($new_link)) {
			$link['#new_raw'] = $new_link;
		}
		
		return $link;
	}	
}