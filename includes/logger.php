<?php

if ( !class_exists('blcLogger') ):

define('BLC_LEVEL_DEBUG', 0);
define('BLC_LEVEL_INFO', 1);
define('BLC_LEVEL_WARNING', 2);
define('BLC_LEVEL_ERROR', 3);

/**
 * Base class for loggers. Doesn't actually log anything anywhere.  
 * 
 * @package Broken Link Checker
 * @author Janis Elsts
 */
class blcLogger {
	
	function __construct($param = ''){
		
	}
	
	function blcLogger($param = ''){
		$this->__construct($param);
	}
	
	function log($message, $object = null, $level = BLC_LEVEL_DEBUG){
		
	}
	
	function debug($message, $object = null){
		$this->log($message, $object, BLC_LEVEL_DEBUG);
	}
	
	function info($message, $object = null){
		$this->log($message, $object, BLC_LEVEL_INFO);
	}
	
	function warn($message, $object = null){
		$this->log($message, $object, BLC_LEVEL_WARNING);
	}
	
	function error($message, $object = null){
		$this->log($message, $object, BLC_LEVEL_ERROR);
	}
	
	function get_messages($min_level = BLC_LEVEL_DEBUG){
		return array();
	}
	
	function get_log($min_level = BLC_LEVEL_DEBUG){
		return array();
	}
	
	function clear(){
		
	}
}

/**
 * A basic logger that uses WP options for permanent storage.
 * 
 * Log entries are initially stored in memory and need to explicitly
 * flushed to the database by calling blcCachedOptionLogger::save().  
 * 
 * @package Broken Link Checker
 * @author Janis Elsts
 */
class blcCachedOptionLogger extends blcLogger {
	var $option_name = '';
	var $log;
	var $filter_level = BLC_LEVEL_DEBUG;
	
	function __construct($option_name = ''){
		$this->option_name = $option_name;
		$oldLog = get_option($this->option_name);
		if ( is_array($oldLog) && !empty($oldLog) ){
			$this->log = $oldLog;
		} else {
			$this->log = array();
		}
	}
	
	function log($message, $object = null, $level = BLC_LEVEL_DEBUG){
		$new_entry = array($level, $message, $object);
		array_push($this->log, $new_entry);
	}
	
	function get_log($min_level = BLC_LEVEL_DEBUG){
		$this->filter_level = $min_level;
		return array_filter($this->log, array($this, '_filter_log'));
	}
	
	function _filter_log($entry){
		return ( $entry[0] >= $this->filter_level );
	}
	
	function get_messages($min_level = BLC_LEVEL_DEBUG){
		$messages = $this->get_log($min_level);
		return array_map( array($this, '_get_log_message'), $messages );
	}
	
	function _get_log_message($entry){
		return $entry[1];
	}
	
	function clear(){
		$this->log = array();
		delete_option($this->option_name);
	}
	
	function save(){
		update_option($this->option_name, $this->log);
	}
}

/**
 * A dummy logger that doesn't log anything.
 */
class blcDummyLogger extends blcLogger { }

endif;

