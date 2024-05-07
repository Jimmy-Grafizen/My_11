<?php
if (IMAGE_UPLOAD_TYPE=="BUCKET") {
	//File upload directory constants
	define('FILES_UPLOAD_DIR', '');
	define('FILES_UPLOAD_URL', AWS_URL); 
} else {
	//File upload directory constants
	define('FILES_UPLOAD_DIR', ROOT_DIRECTORY . 'uploads/');
	define('FILES_UPLOAD_URL', APP_URL . 'uploads/');
}

// GAME type upload and show url
define('BTB_PRIFIX',  'veer11/');
define('GAME_IMAGE_PATH', FILES_UPLOAD_DIR .BTB_PRIFIX. 'game/');
define('GAME_IMAGE_THUMB_PATH', FILES_UPLOAD_DIR .BTB_PRIFIX. 'game/thumbnail/');
define('GAME_IMAGE_LARGE_PATH', FILES_UPLOAD_DIR .BTB_PRIFIX. 'game/large/');
define('GAME_IMAGE_THUMB_URL', FILES_UPLOAD_URL .BTB_PRIFIX. 'game/thumbnail/');
define('GAME_IMAGE_LARGE_URL', FILES_UPLOAD_URL .BTB_PRIFIX. 'game/large/');

// Customer type upload and show url
define('CUSTOMER_IMAGE_PATH', FILES_UPLOAD_DIR .BTB_PRIFIX. 'customer/');
define('CUSTOMER_IMAGE_THUMB_PATH', FILES_UPLOAD_DIR .BTB_PRIFIX. 'customer/thumbnail/');
define('CUSTOMER_IMAGE_LARGE_PATH', FILES_UPLOAD_DIR .BTB_PRIFIX. 'customer/large/');
define('CUSTOMER_IMAGE_THUMB_URL', FILES_UPLOAD_URL .BTB_PRIFIX. 'customer/thumbnail/');
define('CUSTOMER_IMAGE_LARGE_URL', FILES_UPLOAD_URL .BTB_PRIFIX. 'customer/large/');


define('CUSTOMERGALLERY_IMAGE_PATH', FILES_UPLOAD_DIR .BTB_PRIFIX. 'customer_gallery/');
define('CUSTOMERGALLERY_IMAGE_THUMB_PATH', FILES_UPLOAD_DIR .BTB_PRIFIX. 'customer_gallery/thumbnail/');
define('CUSTOMERGALLERY_IMAGE_LARGE_PATH', FILES_UPLOAD_DIR .BTB_PRIFIX. 'customer_gallery/large/');
define('CUSTOMERGALLERY_IMAGE_THUMB_URL', FILES_UPLOAD_URL .BTB_PRIFIX. 'customer_gallery/thumbnail/');
define('CUSTOMERGALLERY_IMAGE_LARGE_URL', FILES_UPLOAD_URL .BTB_PRIFIX. 'customer_gallery/large/');

// GAME type upload and show url
define('PLAYER_IMAGE_PATH', FILES_UPLOAD_DIR .BTB_PRIFIX. 'player/');
define('PLAYER_IMAGE_THUMB_PATH', FILES_UPLOAD_DIR .BTB_PRIFIX. 'player/thumbnail/');
define('PLAYER_IMAGE_LARGE_PATH', FILES_UPLOAD_DIR .BTB_PRIFIX. 'player/large/');
define('PLAYER_IMAGE_THUMB_URL', FILES_UPLOAD_URL .BTB_PRIFIX. 'player/thumbnail/');
define('PLAYER_IMAGE_LARGE_URL', FILES_UPLOAD_URL .BTB_PRIFIX. 'player/large/');
// TEAM type upload and show url
define('TEAMCRICKET_IMAGE_PATH', FILES_UPLOAD_DIR .BTB_PRIFIX. 'cricket_team/');
define('TEAMCRICKET_IMAGE_THUMB_PATH', FILES_UPLOAD_DIR .BTB_PRIFIX. 'cricket_team/thumbnail/');
define('TEAMCRICKET_IMAGE_LARGE_PATH', FILES_UPLOAD_DIR .BTB_PRIFIX. 'cricket_team/large/');
define('TEAMCRICKET_IMAGE_THUMB_URL', FILES_UPLOAD_URL .BTB_PRIFIX. 'cricket_team/thumbnail/');
define('TEAMCRICKET_IMAGE_LARGE_URL', FILES_UPLOAD_URL .BTB_PRIFIX. 'cricket_team/large/');
// SLIDER type upload and show url
define('SLIDER_IMAGE_PATH', FILES_UPLOAD_DIR .BTB_PRIFIX. 'sliders/');
define('SLIDER_IMAGE_THUMB_PATH', FILES_UPLOAD_DIR .BTB_PRIFIX. 'sliders/thumbnail/');
define('SLIDER_IMAGE_LARGE_PATH', FILES_UPLOAD_DIR .BTB_PRIFIX. 'sliders/large/');
define('SLIDER_IMAGE_THUMB_URL', FILES_UPLOAD_URL .BTB_PRIFIX. 'sliders/thumbnail/');
define('SLIDER_IMAGE_LARGE_URL', FILES_UPLOAD_URL .BTB_PRIFIX. 'sliders/large/');
// Context Categories
define('CONTEXTCATEGORY_IMAGE_PATH', FILES_UPLOAD_DIR .BTB_PRIFIX. 'context_categories/');
define('CONTEXTCATEGORY_IMAGE_THUMB_PATH', FILES_UPLOAD_DIR .BTB_PRIFIX. 'context_categories/thumbnail/');
define('CONTEXTCATEGORY_IMAGE_LARGE_PATH', FILES_UPLOAD_DIR .BTB_PRIFIX. 'context_categories/large/');
define('CONTEXTCATEGORY_IMAGE_THUMB_URL', FILES_UPLOAD_URL .BTB_PRIFIX. 'context_categories/thumbnail/');
define('CONTEXTCATEGORY_IMAGE_LARGE_URL', FILES_UPLOAD_URL .BTB_PRIFIX. 'context_categories/large/');
// pancards upload and show url
define('PANCARD_IMAGE_PATH', FILES_UPLOAD_DIR .BTB_PRIFIX. 'pancards/');
define('PANCARD_LARGE_IMAGE_URL', FILES_UPLOAD_URL .BTB_PRIFIX. 'pancards/large');

define('PANCARD_IMAGE_THUMB_PATH', FILES_UPLOAD_DIR .BTB_PRIFIX. 'pancards/thumbnail/');
define('PANCARD_IMAGE_LARGE_PATH', FILES_UPLOAD_DIR .BTB_PRIFIX. 'pancards/large/');
define('PANCARD_IMAGE_THUMB_URL', FILES_UPLOAD_URL .BTB_PRIFIX. 'pancards/thumbnail/');
define('PANCARD_IMAGE_LARGE_URL', FILES_UPLOAD_URL .BTB_PRIFIX. 'pancards/large/');
// bank upload and show url
define('BANK_IMAGE_PATH', FILES_UPLOAD_DIR .BTB_PRIFIX. 'bankproof/');
define('BANK_IMAGE_THUMB_PATH', FILES_UPLOAD_DIR .BTB_PRIFIX. 'bankproof/thumbnail/');
define('BANK_IMAGE_LARGE_PATH', FILES_UPLOAD_DIR .BTB_PRIFIX. 'bankproof/large/');
define('BANK_IMAGE_THUMB_URL', FILES_UPLOAD_URL .BTB_PRIFIX. 'bankproof/thumbnail/');
define('BANK_IMAGE_LARGE_URL', FILES_UPLOAD_URL .BTB_PRIFIX. 'bankproof/large/');
// Notifications
define('NOTIFICATION_IMAGE_PATH', FILES_UPLOAD_DIR .BTB_PRIFIX. 'notifications/');
define('NOTIFICATION_IMAGE_THUMB_PATH', FILES_UPLOAD_DIR .BTB_PRIFIX. 'notifications/thumbnail/');
define('NOTIFICATION_IMAGE_LARGE_PATH', FILES_UPLOAD_DIR .BTB_PRIFIX. 'notifications/large/');
define('NOTIFICATION_IMAGE_THUMB_URL', FILES_UPLOAD_URL .BTB_PRIFIX. 'notifications/thumbnail/');
define('NOTIFICATION_IMAGE_LARGE_URL', FILES_UPLOAD_URL .BTB_PRIFIX. 'notifications/large/');
//Email Notifications
define('EMAILS_NOTIFICATION_IMAGE_PATH', FILES_UPLOAD_DIR .BTB_PRIFIX. 'emails_notifications/');
define('EMAILS_NOTIFICATION_IMAGE_THUMB_PATH', FILES_UPLOAD_DIR .BTB_PRIFIX. 'emails_notifications/thumbnail/');
define('EMAILS_NOTIFICATION_IMAGE_LARGE_PATH', FILES_UPLOAD_DIR .BTB_PRIFIX. 'emails_notifications/large/');
define('EMAILS_NOTIFICATION_IMAGE_THUMB_URL', FILES_UPLOAD_URL .BTB_PRIFIX. 'emails_notifications/thumbnail/');
define('EMAILS_NOTIFICATION_IMAGE_LARGE_URL', FILES_UPLOAD_URL .BTB_PRIFIX. 'emails_notifications/large/');
//Refer Earn
define('REFER_EARN_IMAGE_PATH', FILES_UPLOAD_DIR .BTB_PRIFIX. 'refer_earn/');
define('REFER_EARN_IMAGE_THUMB_PATH', FILES_UPLOAD_DIR .BTB_PRIFIX. 'refer_earn/thumbnail/');
define('REFER_EARN_IMAGE_LARGE_PATH', FILES_UPLOAD_DIR .BTB_PRIFIX. 'refer_earn/large/');
define('REFER_EARN_IMAGE_THUMB_URL', FILES_UPLOAD_URL .BTB_PRIFIX. 'refer_earn/thumbnail/');
define('REFER_EARN_IMAGE_LARGE_URL', FILES_UPLOAD_URL .BTB_PRIFIX. 'refer_earn/large/');


define('MATCH_IMAGE_PATH', FILES_UPLOAD_DIR .BTB_PRIFIX. 'match/');
define('MATCH_IMAGE_THUMB_PATH', FILES_UPLOAD_DIR .BTB_PRIFIX. 'match/thumbnail/');
define('MATCH_IMAGE_LARGE_PATH', FILES_UPLOAD_DIR .BTB_PRIFIX. 'match/large/');
define('MATCH_IMAGE_THUMB_URL', FILES_UPLOAD_URL .BTB_PRIFIX. 'match/thumbnail/');
define('MATCH_IMAGE_LARGE_URL', FILES_UPLOAD_URL .BTB_PRIFIX. 'match/large/');


define('REACTION_IMAGE_PATH', FILES_UPLOAD_DIR .BTB_PRIFIX. 'reaction/');
define('REACTION_IMAGE_THUMB_PATH', FILES_UPLOAD_DIR .BTB_PRIFIX. 'reaction/thumbnail/');
define('REACTION_IMAGE_LARGE_PATH', FILES_UPLOAD_DIR .BTB_PRIFIX. 'reaction/large/');
define('REACTION_IMAGE_THUMB_URL', FILES_UPLOAD_URL .BTB_PRIFIX. 'reaction/thumbnail/');
define('REACTION_IMAGE_LARGE_URL', FILES_UPLOAD_URL .BTB_PRIFIX. 'reaction/large/');


define('APP_ICON_CUSTOMIZE_IMAGE_PATH', FILES_UPLOAD_DIR .BTB_PRIFIX. 'app_icon_customize/');
define('APP_ICON_CUSTOMIZE_IMAGE_THUMB_PATH', FILES_UPLOAD_DIR .BTB_PRIFIX. 'app_icon_customize/thumbnail/');
define('APP_ICON_CUSTOMIZE_IMAGE_LARGE_PATH', FILES_UPLOAD_DIR .BTB_PRIFIX. 'app_icon_customize/large/');
define('APP_ICON_CUSTOMIZE_IMAGE_THUMB_URL', FILES_UPLOAD_URL .BTB_PRIFIX. 'app_icon_customize/thumbnail/');
define('APP_ICON_CUSTOMIZE_IMAGE_LARGE_URL', FILES_UPLOAD_URL .BTB_PRIFIX. 'app_icon_customize/large/');


define('QUOTATIONS_IMAGE_PATH', FILES_UPLOAD_DIR .BTB_PRIFIX. 'quotations/');
define('QUOTATIONS_IMAGE_THUMB_PATH', FILES_UPLOAD_DIR .BTB_PRIFIX. 'quotations/thumbnail/');
define('QUOTATIONS_IMAGE_LARGE_PATH', FILES_UPLOAD_DIR .BTB_PRIFIX. 'quotations/large/');
define('QUOTATIONS_IMAGE_THUMB_URL', FILES_UPLOAD_URL .BTB_PRIFIX. 'quotations/thumbnail/');
define('QUOTATIONS_IMAGE_LARGE_URL', FILES_UPLOAD_URL .BTB_PRIFIX. 'quotations/large/');


//Contest PDF
define('PDF_PATH', FILES_UPLOAD_DIR .BTB_PRIFIX. 'contest_pdf/');
define('PDF_URL', FILES_UPLOAD_URL .BTB_PRIFIX. 'contest_pdf/');

//A common directory to save thumbnails temporarily. It should be used in case S3 Bucket is used for file uploading.
defined("LOCAL_THUMB_PATH") or define("LOCAL_THUMB_PATH", ROOT_DIRECTORY."uploads/");

$document_paths = array(
						"GM"=>array(
									"thumbnail_path" 	=> GAME_IMAGE_THUMB_PATH,
									"large_path" 		=> GAME_IMAGE_LARGE_PATH,
									"large_url" 		=> GAME_IMAGE_THUMB_URL,
									"thumbnail_url" 	=> GAME_IMAGE_LARGE_URL
								),
						"PR"=>array(
									"thumbnail_path" 	=> PLAYER_IMAGE_THUMB_PATH,
									"large_path" 		=> PLAYER_IMAGE_LARGE_PATH,
									"large_url" 		=> PLAYER_IMAGE_THUMB_URL,
									"thumbnail_url" 	=> PLAYER_IMAGE_LARGE_URL
								),
					);
/******Points Array*******/		
define('CRICKETPOINTS',serialize(array('being_part_of_eleven'=>'Being Part Of Eleven','every_run_scored'=>'Every Run Scored','wicket'=>'Wicket','catch'=>'Catch','catch_and_bowled'=>'Catch And Bowled','stumping'=>'Stumping','run_out'=>'Run Out','run_out_catcher'=>'Run Out Catcher','run_out_thrower'=>'Run Out Thrower','dismiss_for_a_duck'=>'Dismiss For A Duck','every_boundary_hit'=>'Every Boundary Hit','every_six_hit'=>'Every Six Hit','half_century'=>'Half Century','century'=>'Century','thirty_runs'=>'Thirty Runs','maiden_over'=>'Maiden Over','four_wicket'=>'Four Wicket','five_wicket'=>'Five Wicket','three_wicket'=>'Three Wicket','two_wicket'=>'Two Wicket','strike_rate'=>'Strike Rate','economy_rate'=>'Economy Rate','minimum_balls_for_strike_rate'=>'Minimum Balls for Strike Rate','minimum_overs_for_economy_rate'=>'Minimum Overs for Economy Rate')));

define('KABADDIPOINTS',serialize(array('Being_Part_Of_Seven'=>'Being Part Of Seven','Making_Substitute_Appearance'=>'Making Substitute Appearance','Each_Successful_Raid_Touch_Point'=>'Each Successful Raid Touch Point','Raid_Bonus'=>'Raid_Bonus','Each_Successful_Tackle'=>'Each_Successful_Tackle','Each_Unsuccessful_Raid'=>'Each_Unsuccessful_Raid','Super_Tackle'=>'Super_Tackle','Pushing_All_Out'=>'Pushing_All_Out','Getting_All_Out'=>'Getting_All_Out','Green_Card'=>'Green_Card','Yellow_Card'=>'Yellow_Card','Red_Card'=>'Red_Card')));

define('SOCCERPOINTS',serialize(array('Played_55_minutes_Or_More'=>'Played 55 minutes Or More','Played_Less_Than_55_Minutes'=>'Played Less Than 55 Minutes','Goal_Gk_Defender'=>'Goal Gk Defender','Goal_Midfielder'=>'Goal Midfielder','Goal_Forward'=>'Goal Forward','For_Every_Assist'=>'For Every Assist','For_Every_10_Passes_Completed'=>'For Every 10 Passes Completed','For_every_2_shots_On_Target'=>'For every 2 shots On Target','Clean_Sheet_Midfielder'=>'Clean Sheet Midfielder','Clean_Sheet_Gk_Defender'=>'Clean Sheet Gk Defender','For_Every_3_Shots_Saved_Gk'=>'For Every 3 Shots Saved Gk','For_Every_Panalty_Saved_Gk'=>'For Every Panalty Saved Gk','For_Every_3_Successful_Tackles_Made'=>'For Every 3 Successful Tackles Made','Yellow_Card'=>'Yellow Card','Red_Card'=>'Red Card','For_every_Own_goal'=>'For every Own goal','For_Every_2_Goals_Conceded_Gk_Defender'=>'For Every 2 Goals Conceded Gk Defender','For_Every_Penalty_Missed'=>'For Every Penalty Missed')));


define('EMAILTEMPLATE',serialize(array('S'=>'SMS','E'=>'Email')));		
define('EMAILTEMPLATEDEFAULT',serialize(array('Y'=>'Yes','N'=>'No')));							
defined("DOCUMENT_PATHS") or define("DOCUMENT_PATHS", serialize($document_paths));
?>
