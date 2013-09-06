<?php

/*
Plugin Name: YouTube API
Description: Check links to YouTube videos and playlists using the YouTube API.
Version: 1.1
Author: Janis Elsts

ModuleID: youtube-checker
ModuleCategory: checker
ModuleContext: on-demand
ModuleLazyInit: true
ModuleClassName: blcYouTubeChecker
ModulePriority: 100

ModuleCheckerUrlPattern: @^http://(?:([\w\d]+\.)*youtube\.[^/]+/watch\?.*v=[^/#]|youtu\.be/[^/#\?]+|(?:[\w\d]+\.)*?youtube\.[^/]+/(playlist|view_play_list)\?[^/#]{15,}?)@i
*/

class blcYouTubeChecker extends blcChecker {
	var $youtube_developer_key = 'AI39si4OM05fWUMbt1g8hBdYPRTGpNbOWVD0-7sKwShqZTOpKigo7Moj1YGk7dMk95-VWB1Iue2aiTNJb655L32-QGM2xq_yVQ';
	var $api_grace_period = 0.3; //How long to wait between YouTube API requests.
	var $last_api_request = 0;   //Timestamp of the last request.
	
	function can_check($url, $parsed){
		return true;
	}
	
	function check($url){
		//Throttle API requests to avoid getting blocked due to quota violation.
		$delta = microtime_float() - $this->last_api_request; 
		if ( $delta < $this->api_grace_period ) {
			usleep(($this->api_grace_period - $delta) * 1000000);
		}
		
		$result = array(
			'final_url' => $url,
			'redirect_count' => 0,
			'timeout' => false,
			'broken' => false,
			'log' => "<em>(Using YouTube API)</em>\n\n",
			'result_hash' => '',
		);
		
		$components = @parse_url($url);
		if ( isset($components['query']) ) {
			parse_str($components['query'], $query);
		} else {
			$query = array();
		}

		//Extract the video or playlist ID from the URL
		$video_id = $playlist_id = null;
		if ( strtolower($components['host']) === 'youtu.be' ) {
			$video_id = trim($components['path'], '/');
		} else if ( (strpos($components['path'], 'watch') !== false) && isset($query['v']) ) {
			$video_id = $query['v'];
		} else if ( $components['path'] == '/playlist' ) {
			$playlist_id = $query['list'];
		} else if ( $components['path'] == '/view_play_list' ) {
			$playlist_id = $query['p'];
		}

		if ( empty($playlist_id) && empty($video_id) ) {
			$result['status_text'] = 'Unsupported URL Syntax';
			$result['status_code'] = BLC_LINK_STATUS_UNKNOWN;
			return $result;
		}

		//Fetch video or playlist from the YouTube API
		if ( !empty($video_id) ) {
			$api_url = $this->get_video_feed_url($video_id);
		} else {
			$api_url = $this->get_playlist_feed_url($playlist_id);
		}

		$conf = blc_get_configuration();
		$args = array( 'timeout' => $conf->options['timeout'], );
		
		$start = microtime_float();
		$response = wp_remote_get($api_url, $args);
		$result['request_duration'] = microtime_float() - $start;
		$this->last_api_request = $start;
		
		//Got anything?
		if ( is_wp_error($response) ){
			$result['log'] .= "Error.\n" . $response->get_error_message();
			//WP doesn't make it easy to distinguish between different internal errors.
        	$result['broken'] = true;
        	$result['http_code'] = 0;
		} else {
			$result['http_code'] = intval($response['response']['code']);

			if ( !empty($video_id) ) {
				$result = $this->check_video($response, $result);
			} else {
				$result = $this->check_playlist($response, $result);
			}
		}

		//The hash should contain info about all pieces of data that pertain to determining if the 
		//link is working.  
        $result['result_hash'] = implode('|', array(
        	'youtube',
			$result['http_code'],
			$result['broken']?'broken':'0', 
			$result['timeout']?'timeout':'0',
			isset($result['state_name']) ? $result['state_name'] : '-',
			isset($result['state_reason']) ? $result['state_reason'] : '-',
		));
        
        return $result;
	}

	/**
	 * Check API response for a single video.
	 *
	 * @param array $response WP HTTP API response.
	 * @param array $result Current result array.
	 * @return array New result array.
	 */
	protected function check_video($response, $result) {
		switch($result['http_code']){
			case 404 : //Not found
				$result['log'] .= __('Video Not Found', 'broken-link-checker');
				$result['broken'] = true;
				$result['http_code'] = 0;
				$result['status_text'] = __('Video Not Found', 'broken-link-checker');
				$result['status_code'] = BLC_LINK_STATUS_ERROR;
				break;

			case 403 : //Forbidden. Usually means that the video has been removed. Body contains details.
				$result['log'] .= $response['body'];
				$result['broken'] = true;
				$result['http_code'] = 0;
				$result['status_text'] = __('Video Removed', 'broken-link-checker');
				$result['status_code'] = BLC_LINK_STATUS_ERROR;
				break;

			case 400 : //Bad request. Usually means that the video ID is incorrect. Body contains details.
				$result['log'] .= $response['body'];
				$result['broken'] = true;
				$result['http_code'] = 0;
				$result['status_text'] = __('Invalid Video ID', 'broken-link-checker');
				$result['status_code'] = BLC_LINK_STATUS_WARNING;
				break;

			case 200 : //Video exists, but may be restricted. Check for <yt:state> tags.
				//See http://code.google.com/apis/youtube/2.0/reference.html#youtube_data_api_tag_yt:state

				//Can we count on an XML parser being installed? No, probably not.
				//Back to our makeshift tag "parser" we go.
				$state = blcUtility::extract_tags($response['body'], 'yt:state', false);
				if ( empty($state) ){
					//Phew, no restrictions.
					$result['log'] .= __("Video OK", 'broken-link-checker');
					$result['status_text'] = __('OK', 'link status', 'broken-link-checker');
					$result['status_code'] = BLC_LINK_STATUS_OK;
					$result['http_code'] = 0;
				} else {

					//Get the state name and code and append them to the log
					$state = reset($state);
					$state_name = $state['attributes']['name'];
					$state_reason = isset($state['attributes']['reasonCode'])?$state['attributes']['reasonCode']:'';

					$result['state_name'] = $state_name;
					$result['state_reason'] = $state_reason;

					$result['log'] .= sprintf(
						__('Video status : %s%s', 'broken-link-checker'),
						$state_name,
						$state_reason ? ' ['.$state_reason.']':''
					);

					if ( $this->is_state_ok($state_name, $state_reason) ) {
						$result['broken'] = false;
						$result['status_text'] = __('OK', 'link status', 'broken-link-checker');
						$result['status_code'] = BLC_LINK_STATUS_OK;
						$result['http_code'] = 0;
					} else {
						$result['broken'] = true;
						$result['status_text'] = __('Video Restricted', 'broken-link-checker');
						$result['status_code'] = BLC_LINK_STATUS_WARNING;
						$result['http_code'] = 0;
					}
				}

				//Add the video title to the log, purely for information.
				//http://code.google.com/apis/youtube/2.0/reference.html#youtube_data_api_tag_media:title
				$title = blcUtility::extract_tags($response['body'], 'media:title', false);
				if ( !empty($title) ){
					$result['log'] .= "\n\nTitle : \"" . $title[0]['contents'] . '"';
				}

				break;

			default:
				$result['log'] .= $result['http_code'] . $response['response']['message'];
				$result['log'] .= "\n" . __('Unknown YouTube API response received.');
				break;
		}

		return $result;
	}

	/**
	 * Check a YouTube API response that contains a single playlist.
	 *
	 * @param array $response
	 * @param array $result
	 * @return array
	 */
	protected function check_playlist($response, $result) {

		switch($result['http_code']){
			case 404 : //Not found
				$result['log'] .= __('Playlist Not Found', 'broken-link-checker');
				$result['broken'] = true;
				$result['http_code'] = 0;
				$result['status_text'] = __('Playlist Not Found', 'broken-link-checker');
				$result['status_code'] = BLC_LINK_STATUS_ERROR;
				break;

			case 403 : //Forbidden. We're unlikely to see this code for playlists, but lets allow it.
				$result['log'] .= $response['body'];
				$result['broken'] = true;
				$result['status_text'] = __('Playlist Restricted', 'broken-link-checker');
				$result['status_code'] = BLC_LINK_STATUS_ERROR;
				break;

			case 400 : //Bad request. Probably indicates a client error (invalid API request). Body contains details.
				$result['log'] .= $response['body'];
				$result['broken'] = true;
				$result['status_text'] = __('Invalid Playlist', 'broken-link-checker');
				$result['status_code'] = BLC_LINK_STATUS_WARNING;
				break;

			case 200 :
				//The playlist exists, but some of the videos may be restricted.
				//Check for <yt:state> tags.
				$video_states = blcUtility::extract_tags($response['body'], 'yt:state', false);
				if ( empty($video_states) ){

					//No restrictions. Does the playlist have any entries?
					$entries = blcUtility::extract_tags($response['body'], 'entry', false);
					if ( !empty($entries) ) {
						//All is well.
						$result['log'] .= __("Playlist OK", 'broken-link-checker');
						$result['status_text'] = __('OK', 'link status', 'broken-link-checker');
						$result['status_code'] = BLC_LINK_STATUS_OK;
						$result['http_code'] = 0;
					} else {
						//An empty playlist. It is possible that all of the videos
						//have been deleted. Treat it as a warning.
						$result['log'] .= __("This playlist has no entries or all entries have been deleted.", 'broken-link-checker');
						$result['status_text'] = __('Empty Playlist', 'link status', 'broken-link-checker');
						$result['status_code'] = BLC_LINK_STATUS_WARNING;
						$result['http_code'] = 0;
						$result['broken'] = true;
					}

				} else {

					//Treat the playlist as broken if at least one video is inaccessible.
					foreach($video_states as $state) {
						$state_name = $state['attributes']['name'];
						$state_reason = isset($state['attributes']['reasonCode'])?$state['attributes']['reasonCode']:'';

						if ( ! $this->is_state_ok($state_name, $state_reason) ) {
							$result['log'] .= sprintf(
								__('Video status : %s%s', 'broken-link-checker'),
								$state_name,
								$state_reason ? ' ['.$state_reason.']':''
							);

							$result['state_name'] = $state_name;
							$result['state_reason'] = $state_reason;

							$result['broken'] = true;
							$result['status_text'] = __('Video Restricted', 'broken-link-checker');
							$result['status_code'] = BLC_LINK_STATUS_WARNING;
							$result['http_code'] = 0;

							break;
						}
					}

					if ( ! $result['broken'] ) {
						$result['status_text'] = __('OK', 'link status', 'broken-link-checker');
						$result['status_code'] = BLC_LINK_STATUS_OK;
						$result['http_code'] = 0;
					}
				}

				//Add the playlist title to the log, purely for information.
				$title = blcUtility::extract_tags($response['body'], 'title', false);
				if ( !empty($title) ){
					$result['log'] .= "\n\nPlaylist title : \"" . $title[0]['contents'] . '"';
				}

				break;

			default:
				$result['log'] .= $result['http_code'] . $response['response']['message'];
				$result['log'] .= "\n" . __('Unknown YouTube API response received.');
				break;
		}

		return $result;
	}

	protected function get_video_feed_url($video_id) {
		return 'http://gdata.youtube.com/feeds/api/videos/' . $video_id . '?key=' . urlencode($this->youtube_developer_key);
	}

	protected function get_playlist_feed_url($playlist_id) {
		if ( strpos($playlist_id, 'PL') === 0 ) {
			$playlist_id = substr($playlist_id, 2);
		}
		$query = http_build_query(
			array(
				'key' => $this->youtube_developer_key,
				'v' => 2,
				'safeSearch' => 'none'
			),
			'', '&'
		);

		return 'http://gdata.youtube.com/feeds/api/playlists/' . $playlist_id . '?' . $query;
	}

	/**
	 * Check if a video is restricted due to some minor problem (e.g. it's still processing)
	 * or if we should treat it as broken.
	 *
	 * @param string $state_name
	 * @param string $state_reason
	 * @return bool
	 */
	protected function is_state_ok($state_name, $state_reason) {
		//A couple of restricted states are not that bad
		$state_ok = ($state_name == 'processing') ||    //Video still processing; temporary.
			(
				$state_name == 'restricted' &&
				$state_reason == 'limitedSyndication' //Only available in browser
			);
		return $state_ok;
	}

}
