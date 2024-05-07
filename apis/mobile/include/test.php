<?php 
include('DbHandler.php') ;
$obj = new DbHandler();
echo "<pre>";
print_r($obj->get_customer_match_teams('284','46699'));
die;

//to get fee stucture of pricate contest
//get_private_contest_entry_fee

//get prize breakup for private contest only 
//get_private_contest_winning_breakup

//get matches by passing the paramater of match status like L R A F
//get_matches


//get match player bt passing match id as argument
//get_match_players

//get cricket team setting no argument required 
//get_team_settings

//to get match contest includes winners , entry fee, allowed entries, prizepool etc with joined_teams
//get_match_contest

//to get only contest details without team and prize breakup realtion
//get_match_contest_detail_mini

//get contest detail with prizebreakup and joined teams data
//get_match_contest_detail

//get detail of contest which are private
//get_match_private_contest_detail

//get details for sharing contest
//get_match_contest_share_detail


//to generate pdf url with contest id
//get_match_contest_pdf

//




