<?php
/*
Plugin Name: Embedded Megavideo videos
Description: Parse embedded videos from Megavideo
Version: 1.0
Author: Janis Elsts

ModuleCategory: parser
ModuleClassName: blcMegavideoEmbed
ModuleContext: on-demand
ModuleLazyInit: true

ModulePriority: 110
*/

if ( !class_exists('blcEmbedParserBase') ){
	require 'embed-parser-base.php';
}

class blcMegavideoEmbed extends blcEmbedParserBase {
	
	function init(){
		parent::init();
		$this->short_title = __('Megavideo Video', 'broken-link-checker');
		$this->long_title = __('Embedded Megavideo video', 'broken-link-checker');
		$this->url_search_string = 'megavideo.com/v/';
	}
	
	function link_url_from_src($src){
		//It doesn't really matter what URL we use here, since Megavideo has been
		//taken down and all URLs will fail anyway.
		return $src;
	}

	function ui_get_link_text($instance, $context = 'display'){
		return '[' . $this->short_title . ']';
	}
}

