<?php

/**
 * @author Janis Elsts
 * @copyright 2010
 */

//Appearify the survey notice to people who have used BLC for at least 2 weeks (doesn't need to be very accurate) 
$blc_config = blc_get_configuration();
$blc_show_survey = empty($blc_config->options['hide_surveyio_notice'])     
	           && !empty($blc_config->options['first_installation_timestamp'])  
               && ( time() - $blc_config->options['first_installation_timestamp'] > 2*7*24*60*60 ); 
               
if ( $blc_show_survey ){ 
	add_action('admin_notices', 'blc_display_survey_notice');
}

/**
* Display a notice asking the user to take the Broken Link Checker user survey.
*
* @return void
*/
function blc_display_survey_notice(){
	//Only people who can actually use the plugin will see the notice
	if ( !current_user_can('manage_links') ) return;
	
	if ( !empty($_GET['dismiss-blc-survey']) ){
		//The user has chosen to hide the survey notice
		$blc_config = blc_get_configuration();
		$blc_config->options['hide_surveyio_notice'] = true;
		$blc_config->save_options();
		return;
	}
	
	$survey_url = 'http://survey.io/survey/7fbf0';
	
	$msg = sprintf(
		'<strong>Help improve Broken Link Checker - <a href="%s" target="_blank" title="This link will open in a new window" id="blc-take-survey-link">take a user feedback survey!</a></strong>
		 <br><a href="%s">Hide this notice</a>',
		$survey_url,
		add_query_arg('dismiss-blc-survey', 1)
	);
	
	echo '<div id="update-nag" class="blc-survey-notice" style="text-align: left; padding-left: 10px;">'.$msg.'</div>';
	
	//Auto-hide the notice after the user clicks the survey link
	?>
	<script type="text/javascript">
	jQuery(function($){
		$('#blc-take-survey-link').click(function(){
			$('.blc-survey-notice').hide('fast');
			$.get('<?php echo esc_js(add_query_arg('dismiss-blc-survey', 1, admin_url())); ?>');
		});
	});
	</script>
	<?php
}
?>