<?php


class Entitysport {

    function __construct() {
        require_once  '../global_constants.php';
    }

    public function fantasy_squade($match_unique_id) {

            $curl = curl_init();

            curl_setopt_array($curl, array(
            CURLOPT_URL => ENTITYSPORT_MATCHE_PLAYER.$match_unique_id."/squads?token=".ENTITYSPORT_APIKEY,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
            CURLOPT_HTTPHEADER => array(
                "Postman-Token: e8439a8d-4242-457f-b187-56adf512fbe6",
                "cache-control: no-cache"
              ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            $api_data=$response;
            

            $api_data_array=json_decode($api_data,true);
            $api_data_array=isset($api_data_array['response'])?$api_data_array['response']:array();


             $palyers=array();
            if(!empty($api_data_array['teama']) && !empty($api_data_array['teama']['squads'])){

                foreach($api_data_array['teama']['squads'] as $team){

                   
                    if($team['playing11']==="true") {
                        $palyers[]=$team['player_id'];
                    }

                }                      
            }

            if(!empty($api_data_array['teamb']) && !empty($api_data_array['teamb']['squads'])){

                foreach($api_data_array['teamb']['squads'] as $team){

                   
                    if($team['playing11']==="true") {
                        $palyers[]=$team['player_id'];
                    }
                   

                }                      
            }
            return $palyers;

  }
  
  
  
  
      public function fantasy_squade_football($match_unique_id) {

            $curl = curl_init();

            curl_setopt_array($curl, array(
            CURLOPT_URL => "https://soccer.entitysport.com/matches/$match_unique_id/info?token=fa5fe330735d2249a1323b5772529035",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
            CURLOPT_HTTPHEADER => array(
                "Postman-Token: e8439a8d-4242-457f-b187-56adf512fbe6",
                "cache-control: no-cache"
              ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            $api_data=$response;
            

            $api_data_array=json_decode($api_data,true);
            $api_data_array=isset($api_data_array['response']['items'])?$api_data_array['response']['items']:array();

            $matchinfo = $api_data_array['match_info'];

            $api_data_array_new = isset($api_data_array['lineup'])?$api_data_array['lineup']:array(); 
            
                     $teamadata = isset($api_data_array_new['home']['lineup'])?$api_data_array_new['home']['lineup']:array(); 
   
             $palyers=array();
            if(!empty($teamadata) && !empty($teamadata['player'])){

                foreach($teamadata['player'] as $team){
                        $palyers[]=$team['pid'];
                }                      
            }
            
                    $teamadata = isset($api_data_array_new['home']['substitutes'])?$api_data_array_new['home']['substitutes']:array(); 

               if(!empty($teamadata)){

                foreach($teamadata as $team){

                   
                        $palyers[]=$team['pid'];

                }                      
            }
            
 $teambdata = isset($api_data_array_new['away']['lineup'])?$api_data_array_new['away']['lineup']:array(); 


            if(!empty($teambdata) && !empty($teambdata['player'])){

                foreach($teambdata['player'] as $team){

                            $palyers[]=$team['pid'];

                }                      
            }
            
            
            
             $teambdata = isset($api_data_array_new['away']['substitutes'])?$api_data_array_new['away']['substitutes']:array(); 


            if(!empty($teambdata)){
                foreach($teambdata as $team){
                            $palyers[]=$team['pid'];
                }                      
            }
            $ar =[];
            $ar['player']=$palyers;
              $ar['matchinfo']=$matchinfo[0];
          return $ar;
  }



    public function getmatchfootballstatus($match_unique_id)
    {
        echo "https://soccer.entitysport.com/matches/$match_unique_id/info?token=fa5fe330735d2249a1323b5772529035";
         $curl = curl_init();
            curl_setopt_array($curl, array(
            CURLOPT_URL => "https://soccer.entitysport.com/matches/$match_unique_id/info?token=fa5fe330735d2249a1323b5772529035",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
            CURLOPT_HTTPHEADER => array(
                "Postman-Token: e8439a8d-4242-457f-b187-56adf512fbe6",
                "cache-control: no-cache"
              ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            $api_data=$response;
            
            $innings = array();
            $api_data_array=json_decode($api_data,true);
    
            
            return isset($api_data_array['response']['items']['match_info'][0])?$api_data_array['response']['items']['match_info'][0]:array();
            
    }
  public function fantasy_summary_football($match_unique_id, $game_type_point) {
      echo "https://soccer.entitysport.com/matches/$match_unique_id/info?token=1ae70ef4de6fbc308cc7699b3ec6aca5";
            $curl = curl_init();
            curl_setopt_array($curl, array(
            CURLOPT_URL => "https://soccer.entitysport.com/matches/$match_unique_id/info?token=1ae70ef4de6fbc308cc7699b3ec6aca5",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
            CURLOPT_HTTPHEADER => array(
                "Postman-Token: e8439a8d-4242-457f-b187-56adf512fbe6",
                "cache-control: no-cache"
              ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            $api_data=$response;
            
            $innings = array();
            $api_data_array=json_decode($api_data,true);
            
         //   print_r($api_data_array);die;
            if(empty($api_data_array) || $api_data_array['status']!='ok'){
              $output=array();
              $output['players']=array();
              $output['scorecard_data']=NULL;
              $output['api_response']=array();
              $output['innings'] = array();
              $output['man-of-the-match']=array();
              return $output;
            }
            


            $team1_run=0;
            $team1_wicket=0;
            $team1_overs=0;
            $team2_run=0;
            $team2_wicket=0;
            $team2_overs=0;
            $score_board_notes="";

          
            $contCommentary = count($api_data_array['response']['items']['commentary']);
            $score_board_notes=$api_data_array['response']['items']['commentary'][$contCommentary-1]['sentence'];
 $team1_run =$api_data_array['response']['items']['match_info'][0]['result']['home'];
$team2_run =$api_data_array['response']['items']['match_info'][0]['result']['away'];

$status =$api_data_array['response']['items']['match_info'][0]['status'];
$innings = $api_data_array['response']['items']['commentary'];
            $scorecardData=array();
            $scorecardData['team1_run']=$team1_run;
            $scorecardData['team2_run']=$team2_run;
            $scorecardData['score_board_notes']=$score_board_notes;
                   $scorecardData['match_status']=$status;
     
            
           $palyers=array();


            foreach($api_data_array['response']['items']['lineup'] as $dataresponse)
            {
                foreach($dataresponse['lineup']['player'] as $dataresponse2)
                {
                   $palyers[$dataresponse2['pid']]['Being_Part_Of_Eleven']=$game_type_point['In_Starting_11']; 
                $palyers[$dataresponse2['pid']]['Being_Part_Of_Eleven_Value']=1; 
                
                $palyers[$dataresponse2['pid']]['Yellow_card']=$game_type_point['Yellow_card']; 
                $palyers[$dataresponse2['pid']]['Yellow_card_Value']=1;
                
                $palyers[$dataresponse2['pid']]['Red_card']=$game_type_point['Yellow_card']; 
                $palyers[$dataresponse2['pid']]['Red_card_Value']=1;
                
                
                
                  $palyers[$dataresponse2['pid']]['Goal_Scored']=$game_type_point['Yellow_card']; 
                $palyers[$dataresponse2['pid']]['Goal_Scored_Value']=1;
                
                
                  $palyers[$dataresponse2['pid']]['Own_Goal']=$game_type_point['Yellow_card']; 
                $palyers[$dataresponse2['pid']]['Own_Goal_Value']=1;
                
       
                
                

                    $palyers[$dataresponse2['pid']]['total_points']= $palyers[$dataresponse2['pid']]['Being_Part_Of_Eleven']; 
                }
                
                foreach($dataresponse['substitutes'] as $dataresponse2)
                {
                    
                   $palyers[$dataresponse2['pid']]['Coming_on_as_a_substitute']=$game_type_point['Coming_on_as_a_substitute']; 
                $palyers[$dataresponse2['pid']]['Coming_on_as_a_substitute_value']=1; 
                
                
                $palyers[$dataresponse2['pid']]['Yellow_card']=$game_type_point['Yellow_card']; 
                $palyers[$dataresponse2['pid']]['Yellow_card_Value']=1;
                
                $palyers[$dataresponse2['pid']]['Red_card']=$game_type_point['Yellow_card']; 
                $palyers[$dataresponse2['pid']]['Red_card_Value']=1;
                
                
                
                  $palyers[$dataresponse2['pid']]['Goal_Scored']=$game_type_point['Yellow_card']; 
                $palyers[$dataresponse2['pid']]['Goal_Scored_Value']=1;
                
                
                  $palyers[$dataresponse2['pid']]['Own_Goal']=$game_type_point['Yellow_card']; 
                $palyers[$dataresponse2['pid']]['Own_Goal_Value']=1;
        $palyers[$dataresponse2['pid']]['total_points']= $palyers[$dataresponse2['pid']]['Coming_on_as_a_substitute']; 
    }
            }
            
          
            $output=array();
            $output['players']=$palyers;
            $output['innings']=$innings;
            $output['scorecard_data']=$scorecardData;

            
             $man_of_the_match=array();
            if(($status==2 || $status==4)){
                $man_of_the_match['pid']="";
                $man_of_the_match['name']="";
            }
            $output['man-of-the-match']=$man_of_the_match;


            return $output;

  }



  public function fantasy_summary($match_unique_id, $game_type_point) {
            $curl = curl_init();
            curl_setopt_array($curl, array(
            CURLOPT_URL => ENTITYSPORT_MATCHE_PLAYER.$match_unique_id."/scorecard?token=".ENTITYSPORT_APIKEY,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
            CURLOPT_HTTPHEADER => array(
                "Postman-Token: e8439a8d-4242-457f-b187-56adf512fbe6",
                "cache-control: no-cache"
              ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            $api_data=$response;
            
            $innings = array();
            $api_data_array=json_decode($api_data,true);
            
            
            if(empty($api_data_array) || $api_data_array['status']!='ok'){
              $output=array();
              $output['players']=array();
              $output['scorecard_data']=NULL;
              $output['api_response']=$api_data_array;
              $output['innings'] = array();
              $output['man-of-the-match']=array();
              return $output;
            }
            
            $api_data_array=isset($api_data_array['response'])?$api_data_array['response']:array();


            $team1_run=0;
            $team1_wicket=0;
            $team1_overs=0;
            $team2_run=0;
            $team2_wicket=0;
            $team2_overs=0;
            $score_board_notes="";


            $score_board_notes=$api_data_array['status_note'];

                        if(!empty($api_data_array['teama'])){

                if(isset($api_data_array['teama']['scores_full'])){

                    $scores_full=$api_data_array['teama']['scores_full'];

                    if(!empty($scores_full)){
                        $scores_full_array= explode("/",$scores_full);
                        $last_index= end($scores_full_array);
                        $run=str_replace("/".$last_index,"",$scores_full);
                          
                        $last_index_array=explode(" (",$last_index); 
                          
                        $wicket=trim($last_index_array[0]); 
                          
                        $over=explode(" ",trim($last_index_array[1]))[0]; 

                        $team1_run=$run;
                        $team1_wicket=$wicket;
                        $team1_overs=$over;
                    }


                    // $new_score=explode("/", $scores);
              //       $team1_run=$new_score[0];
              //       if(count($new_score)>1)
              //       $team1_wicket=$new_score[1];

                    // $overs=$api_data_array['teama']['overs'];
                    // if(!empty($overs)){
                    //  $team1_overs=$overs;
                    // }
                }

            }

            if(!empty($api_data_array['teamb'])){

                if(isset($api_data_array['teamb']['scores_full'])){

                    $scores_full=$api_data_array['teamb']['scores_full'];

                    if(!empty($scores_full)){
                        $scores_full_array= explode("/",$scores_full);
                        $last_index= end($scores_full_array);
                        $run=str_replace("/".$last_index,"",$scores_full);
                          
                        $last_index_array=explode(" (",$last_index); 
                          
                        $wicket=trim($last_index_array[0]); 
                          
                        $over=explode(" ",trim($last_index_array[1]))[0]; 

                        $team2_run=$run;
                        $team2_wicket=$wicket;
                        $team2_overs=$over;
                    }

                    // $new_score=explode("/", $scores);
              //       $team2_run=$new_score[0];
              //       if(count($new_score)>1)
              //       $team2_wicket=$new_score[1];

                    // $overs=$api_data_array['teamb']['overs'];
                    // if(!empty($overs)){
                    //  $team2_overs=$overs;
                    // }
                }

            }


            $scorecardData=array();
            $scorecardData['team1_run']=$team1_run;
            $scorecardData['team1_wicket']=$team1_wicket;
            $scorecardData['team1_overs']=$team1_overs;
            $scorecardData['team2_run']=$team2_run;
            $scorecardData['team2_wicket']=$team2_wicket;
            $scorecardData['team2_overs']=$team2_overs;
            $scorecardData['score_board_notes']=$score_board_notes;

            if(!isset($game_type_point['Being_Part_Of_Eleven'])){
                    $game_type_point['Being_Part_Of_Eleven']=0;
            }

            if(!isset($game_type_point['Every_Six_Hit'])){
                $game_type_point['Every_Six_Hit']=0;
            }

            if(!isset($game_type_point['Every_Boundary_Hit'])){
                $game_type_point['Every_Boundary_Hit']=0;
            }

            if(!isset($game_type_point['Every_Run_Scored'])){
                $game_type_point['Every_Run_Scored']=0;
            }

            if(!isset($game_type_point['Half_Century'])){
                $game_type_point['Half_Century']=0;
            }

             if(!isset($game_type_point['Century'])){
                  $game_type_point['Century']=0;
             }

             if(!isset($game_type_point['Thirty_Runs'])){
                  $game_type_point['Thirty_Runs']=0;
             }

            if(!isset($game_type_point['Dismiss_For_A_Duck'])){
                $game_type_point['Dismiss_For_A_Duck']=0;
            }

            if(!isset($game_type_point['Wicket'])){
                $game_type_point['Wicket']=0;
            }

            if(!isset($game_type_point['Maiden_Over'])){
                $game_type_point['Maiden_Over']=0;
            }

            if(!isset($game_type_point['Five_Wicket'])){
                $game_type_point['Five_Wicket']=0;
            }

            if(!isset($game_type_point['Four_Wicket'])){
                $game_type_point['Four_Wicket']=0;
            }

              if(!isset($game_type_point['Three_Wicket'])){
                  $game_type_point['Three_Wicket']=0;
              }

              if(!isset($game_type_point['Two_Wicket'])){
                  $game_type_point['Two_Wicket']=0;
              }

            if(!isset($game_type_point['Catch'])){
                $game_type_point['Catch']=0;
            }

            if(!isset($game_type_point['Catch_And_Bowled'])){
                  $game_type_point['Catch_And_Bowled']=0;
            }

            if(!isset($game_type_point['Stumping'])){
                $game_type_point['Stumping']=0;
            }

            if(!isset($game_type_point['Run_Out'])){
                $game_type_point['Run_Out']=0;
            }

            if(!isset($game_type_point['Run_Out_Catcher'])){
                  $game_type_point['Run_Out_Catcher']=0;
            }

            if(!isset($game_type_point['Run_Out_Thrower'])){
                  $game_type_point['Run_Out_Thrower']=0;
            }

            if(!isset($game_type_point['Strike_Rate'])){
                $game_type_point['Strike_Rate']='[]';
            }

            if(!isset($game_type_point['Economy_Rate'])){
                $game_type_point['Economy_Rate']='[]';
            }

            if(!isset($game_type_point['Minimum_Balls_for_Strike_Rate'])){
                $game_type_point['Minimum_Balls_for_Strike_Rate']=0;
            }

            if(!isset($game_type_point['Minimum_Overs_for_Economy_Rate'])){
                $game_type_point['Minimum_Overs_for_Economy_Rate']=0;
            }

            $palyers=array();
            if(!empty($api_data_array['players']) && !empty($game_type_point)){
                   
                foreach($api_data_array['players'] as $playing_players){

                        if($playing_players['role']=="squad"){
                            continue;
                        }

                       $palyers[$playing_players['pid']]['Being_Part_Of_Eleven']=$game_type_point['Being_Part_Of_Eleven'];
                       $palyers[$playing_players['pid']]['Every_Run_Scored']=0;
                       $palyers[$playing_players['pid']]['Dismiss_For_A_Duck']=0;
                       $palyers[$playing_players['pid']]['Every_Boundary_Hit']=0;
                       $palyers[$playing_players['pid']]['Every_Six_Hit']=0;
                       $palyers[$playing_players['pid']]['Half_Century']=0;
                       $palyers[$playing_players['pid']]['Century']=0;
                        $palyers[$playing_players['pid']]['Thirty_Runs']=0;



                       $palyers[$playing_players['pid']]['Wicket']=0;
                       $palyers[$playing_players['pid']]['Maiden_Over']=0;
                       $palyers[$playing_players['pid']]['Four_Wicket']=0;
                       $palyers[$playing_players['pid']]['Five_Wicket']=0;
                       $palyers[$playing_players['pid']]['Three_Wicket']=0;
                       $palyers[$playing_players['pid']]['Two_Wicket']=0;



                       $palyers[$playing_players['pid']]['Catch']=0;
                       $palyers[$playing_players['pid']]['Catch_And_Bowled']=0;
                       $palyers[$playing_players['pid']]['Stumping']=0;
                       $palyers[$playing_players['pid']]['Run_Out']=0;

                       $palyers[$playing_players['pid']]['Run_Out_Catcher']=0;
                       $palyers[$playing_players['pid']]['Run_Out_Thrower']=0;
                       $palyers[$playing_players['pid']]['Strike_Rate']=0;
                       $palyers[$playing_players['pid']]['Economy_Rate']=0;

                       $palyers[$playing_players['pid']]['total_points']= $palyers[$playing_players['pid']]['Being_Part_Of_Eleven']; 
                         $palyers[$playing_players['pid']]['Being_Part_Of_Eleven_Value']=1;
                       $palyers[$playing_players['pid']]['Every_Run_Scored_Value']=0;
                       $palyers[$playing_players['pid']]['Dismiss_For_A_Duck_Value']=0;
                       $palyers[$playing_players['pid']]['Every_Boundary_Hit_Value']=0;
                       $palyers[$playing_players['pid']]['Every_Six_Hit_Value']=0;
                       $palyers[$playing_players['pid']]['Half_Century_Value']=0;
                       $palyers[$playing_players['pid']]['Century_Value']=0;
                       $palyers[$playing_players['pid']]['Thirty_Runs_Value']=0;


                       $palyers[$playing_players['pid']]['Wicket_Value']=0;
                       $palyers[$playing_players['pid']]['Maiden_Over_Value']=0;
                       $palyers[$playing_players['pid']]['Four_Wicket_Value']=0;
                       $palyers[$playing_players['pid']]['Five_Wicket_Value']=0;
                       $palyers[$playing_players['pid']]['Three_Wicket_Value']=0;
                       $palyers[$playing_players['pid']]['Two_Wicket_Value']=0;



                       $palyers[$playing_players['pid']]['Catch_Value']=0;
                       $palyers[$playing_players['pid']]['Catch_And_Bowled_Value']=0;
                       $palyers[$playing_players['pid']]['Stumping_Value']=0;
                       $palyers[$playing_players['pid']]['Run_Out_Value']=0;
                       $palyers[$playing_players['pid']]['Run_Out_Catcher_Value']=0;                                       
                       $palyers[$playing_players['pid']]['Run_Out_Thrower_Value']=0;                                        
                       $palyers[$playing_players['pid']]['Strike_Rate_Value']=0;
                       $palyers[$playing_players['pid']]['Economy_Rate_Value']=0;
                }

                if(!empty($api_data_array['innings'])){
                    $innings = $api_data_array['innings'];
                    foreach($api_data_array['innings'] as $innigsdarta){

                        $inningsNumber=$innigsdarta['number'];

                        if(!empty($innigsdarta['batsmen'])){

                                foreach($innigsdarta['batsmen'] as $batting_scores){

                                            $scores=$batting_scores;
                                            if(!isset($scores['batsman_id'])  || !isset($palyers[$scores['batsman_id']])){

                                                continue;
                                            }



                                            if(empty($scores['sixes'])){
                                                $scores['sixes']=0;
                                            }
                                            $Every_Six_Hit_Points=$game_type_point['Every_Six_Hit']*$scores['sixes'];
                                            if($inningsNumber<=2){
                                                $palyers[$scores['batsman_id']]['Every_Six_Hit']=$game_type_point['Every_Six_Hit']*$scores['sixes'];
                                                $palyers[$scores['batsman_id']]['Every_Six_Hit_Value']=$scores['sixes'];
                                            }else{

                                                $palyers[$scores['batsman_id']]['Every_Six_Hit']+=$game_type_point['Every_Six_Hit']*$scores['sixes'];
                                                $palyers[$scores['batsman_id']]['Every_Six_Hit_Value']+=$scores['sixes'];
                                            }


                                            if(empty($scores['fours'])){
                                                    $scores['fours']=0;
                                            }
                                            $Every_Boundary_Hit_Points=$game_type_point['Every_Boundary_Hit']*$scores['fours'];
                                            if($inningsNumber<=2){

                                                $palyers[$scores['batsman_id']]['Every_Boundary_Hit']=$game_type_point['Every_Boundary_Hit']*$scores['fours'];
                                                $palyers[$scores['batsman_id']]['Every_Boundary_Hit_Value']=$scores['fours'];
                                            }else{

                                                $palyers[$scores['batsman_id']]['Every_Boundary_Hit']+=$game_type_point['Every_Boundary_Hit']*$scores['fours'];
                                                $palyers[$scores['batsman_id']]['Every_Boundary_Hit_Value']+=$scores['fours'];
                                            }


                                            if(empty($scores['runs'])){
                                                    $scores['runs']=0;
                                            }
                                            $Every_Run_Scored_Points=$game_type_point['Every_Run_Scored']*$scores['runs'];
                                            if($inningsNumber<=2){

                                                $palyers[$scores['batsman_id']]['Every_Run_Scored']=$game_type_point['Every_Run_Scored']*$scores['runs'];
                                                $palyers[$scores['batsman_id']]['Every_Run_Scored_Value']=$scores['runs'];
                                            }else{

                                                $palyers[$scores['batsman_id']]['Every_Run_Scored']+=$game_type_point['Every_Run_Scored']*$scores['runs'];
                                                $palyers[$scores['batsman_id']]['Every_Run_Scored_Value']+=$scores['runs'];

                                            }



                                            if(empty($scores['strike_rate'])){
                                                    $scores['strike_rate']=0;
                                            }

                                            if(empty($scores['balls_faced'])){
                                                    $scores['balls_faced']=0;
                                            }


                                            $Strike_Rate_decode=json_decode($game_type_point['Strike_Rate'],true);

                                             $Strike_Rate_Points=0;

                                            if($scores['balls_faced']>=$game_type_point['Minimum_Balls_for_Strike_Rate']){

                                                foreach($Strike_Rate_decode as $Strike_Rate_decode_value){

                                                    if($scores['strike_rate']>=$Strike_Rate_decode_value['min'] && $scores['strike_rate']<=$Strike_Rate_decode_value['max']){
                                                        $Strike_Rate_Points=$Strike_Rate_decode_value['val'];
                                                        break;

                                                    }


                                                }
                                            }



                                            if($inningsNumber<=2){

                                                $palyers[$scores['batsman_id']]['Strike_Rate']=$Strike_Rate_Points;
                                                $palyers[$scores['batsman_id']]['Strike_Rate_Value']=$scores['strike_rate'];
                                            }else{

                                                $palyers[$scores['batsman_id']]['Strike_Rate']+=$Strike_Rate_Points;
                                                $palyers[$scores['batsman_id']]['Strike_Rate_Value'].="/".$scores['strike_rate'];

                                            }












                                            /*$Century_Points=0;
                                            $Half_Century_Points=0;
                                            $Thirty_Runs_Points=0;
                                            if($inningsNumber<=2){
                                                $no_of_century=(int)($scores['runs']/100);
                                                $palyers[$scores['batsman_id']]['Century']=(2*$game_type_point['Half_Century'])*$no_of_century;
                                                $palyers[$scores['batsman_id']]['Century_Value']=$no_of_century;

                                                $remainRuns=$scores['runs']-($no_of_century*100);

                                                $no_of_fifty=(int)($remainRuns/50);
                                                $palyers[$scores['batsman_id']]['Half_Century']=$game_type_point['Half_Century']*$no_of_fifty;
                                                $palyers[$scores['batsman_id']]['Half_Century_Value']=$no_of_fifty;

                                                $Century_Points=(2*$game_type_point['Half_Century'])*$no_of_century;
                                                $Half_Century_Points=$game_type_point['Half_Century']*$no_of_fifty;
                                            }else{
                                                $no_of_century=(int)($scores['runs']/100);
                                                $palyers[$scores['batsman_id']]['Century']+=(2*$game_type_point['Half_Century'])*$no_of_century;
                                                $palyers[$scores['batsman_id']]['Century_Value']+=$no_of_century;

                                                $remainRuns=$scores['runs']-($no_of_century*100);

                                                $no_of_fifty=(int)($remainRuns/50);
                                                $palyers[$scores['batsman_id']]['Half_Century']+=$game_type_point['Half_Century']*$no_of_fifty;
                                                $palyers[$scores['batsman_id']]['Half_Century_Value']+=$no_of_fifty;

                                                $Century_Points=(2*$game_type_point['Half_Century'])*$no_of_century;
                                                $Half_Century_Points=$game_type_point['Half_Century']*$no_of_fifty;
                                            }*/


                                          
                                            $Century_Points=0;
                                            $Half_Century_Points=0;
                                            $Thirty_Runs_Points=0;

                                            
                                            if($inningsNumber<=2){

                                                


                                                $no_of_century=(int)($scores['runs']/100);
                                                
                                                $palyers[$scores['batsman_id']]['Century']=(2*$game_type_point['Half_Century'])*$no_of_century;
                                                $palyers[$scores['batsman_id']]['Century_Value']=$no_of_century;

                                                
                                                $no_of_fifty=(int)($scores['runs']/50);
                                                if( $no_of_century>0){
                                                  $no_of_fifty=0;
                                                }
                                                $palyers[$scores['batsman_id']]['Half_Century']=$game_type_point['Half_Century']*$no_of_fifty;
                                                $palyers[$scores['batsman_id']]['Half_Century_Value']=$no_of_fifty;



                                                $no_of_thirty_runs=(int)($scores['runs']/30);

                                                if($no_of_century>0 || $no_of_fifty>0){
                                                  $no_of_thirty_runs=0;
                                                }

                                                $palyers[$scores['batsman_id']]['Thirty_Runs']=$game_type_point['Thirty_Runs']*$no_of_thirty_runs;
                                                $palyers[$scores['batsman_id']]['Thirty_Runs_Value']=$no_of_thirty_runs;

                                                
                                                

                                              

                                              

                                              $Century_Points=(2*$game_type_point['Half_Century'])*$no_of_century;

                                              $Half_Century_Points=$game_type_point['Half_Century']*$no_of_fifty;

                                              $Thirty_Runs_Points=($game_type_point['Thirty_Runs'])*$no_of_thirty_runs;
                                              
                                            }else{
                                                


                                                $no_of_century=(int)($scores['runs']/100);
                                                
                                                $palyers[$scores['batsman_id']]['Century']+=(2*$game_type_point['Half_Century'])*$no_of_century;
                                                $palyers[$scores['batsman_id']]['Century_Value']+=$no_of_century;

                                                
                                                $no_of_fifty=(int)($scores['runs']/50);
                                                if($no_of_century>0){
                                                  $no_of_fifty=0;
                                                }
                                                $palyers[$scores['batsman_id']]['Half_Century']+=$game_type_point['Half_Century']*$no_of_fifty;
                                                $palyers[$scores['batsman_id']]['Half_Century_Value']+=$no_of_fifty;



                                                $no_of_thirty_runs=(int)($scores['runs']/30);

                                                if($no_of_century>0 || $no_of_fifty>0){
                                                  $no_of_thirty_runs=0;
                                                }

                                                $palyers[$scores['batsman_id']]['Thirty_Runs']+=$game_type_point['Thirty_Runs']*$no_of_thirty_runs;
                                                $palyers[$scores['batsman_id']]['Thirty_Runs_Value']+=$no_of_thirty_runs;

                                                
                                                

                                             

                                             

                                              $Century_Points=(2*$game_type_point['Half_Century'])*$no_of_century;

                                              $Half_Century_Points=$game_type_point['Half_Century']*$no_of_fifty;

                                              $Thirty_Runs_Points=($game_type_point['Thirty_Runs'])*$no_of_thirty_runs;
                                            }

                                            $Dismiss_For_A_Duck_Points=0;
                                            if($inningsNumber<=2){
                                                if($scores['runs']==0 && !empty($scores['dismissal'])){
                                                    $palyers[$scores['batsman_id']]['Dismiss_For_A_Duck']=$game_type_point['Dismiss_For_A_Duck'];
                                                    $palyers[$scores['batsman_id']]['Dismiss_For_A_Duck_Value']=1;
                                                    $Dismiss_For_A_Duck_Points=$game_type_point['Dismiss_For_A_Duck'];
                                                }
                                            }else{
                                                if($scores['runs']==0 && !empty($scores['dismissal'])){
                                                    $palyers[$scores['batsman_id']]['Dismiss_For_A_Duck']+=$game_type_point['Dismiss_For_A_Duck'];
                                                    $palyers[$scores['batsman_id']]['Dismiss_For_A_Duck_Value']=1;
                                                    $Dismiss_For_A_Duck_Points=$game_type_point['Dismiss_For_A_Duck'];
                                                }
                                            }

                                          $palyers[$scores['batsman_id']]['total_points']+= $Every_Six_Hit_Points+$Every_Boundary_Hit_Points+$Every_Run_Scored_Points+$Century_Points+$Half_Century_Points+$Dismiss_For_A_Duck_Points+$Strike_Rate_Points+$Thirty_Runs_Points;

                                        

                                }

                        }


                        if(!empty($innigsdarta['bowlers'])){

                                foreach($innigsdarta['bowlers'] as $bowling_scores){


                                            $scores=$bowling_scores;
                                            if(!isset($scores['bowler_id']) || !isset($palyers[$scores['bowler_id']])){

                                                continue;
                                            }


                                            if(empty($scores['wickets'])){
                                                $scores['wickets']=0;
                                            }
                                            $Wicket_Points=$game_type_point['Wicket']*$scores['wickets'];
                                            if($inningsNumber<=2){
                                                $palyers[$scores['bowler_id']]['Wicket']=$game_type_point['Wicket']*$scores['wickets'];
                                                $palyers[$scores['bowler_id']]['Wicket_Value']=$scores['wickets'];
                                            }else{
                                                $palyers[$scores['bowler_id']]['Wicket']+=$game_type_point['Wicket']*$scores['wickets'];
                                                $palyers[$scores['bowler_id']]['Wicket_Value']+=$scores['wickets'];
                                            }

                                            if(empty($scores['maidens'])){
                                                $scores['maidens']=0;
                                            }
                                            $Maiden_Over_Points=$game_type_point['Maiden_Over']*$scores['maidens'];
                                            if($inningsNumber<=2){
                                                $palyers[$scores['bowler_id']]['Maiden_Over']=$game_type_point['Maiden_Over']*$scores['maidens'];
                                                $palyers[$scores['bowler_id']]['Maiden_Over_Value']=$scores['maidens'];
                                            }else{
                                                $palyers[$scores['bowler_id']]['Maiden_Over']+=$game_type_point['Maiden_Over']*$scores['maidens'];
                                                $palyers[$scores['bowler_id']]['Maiden_Over_Value']+=$scores['maidens'];
                                            }


                                            /*$no_of_5w=(int)($scores['wickets']/5);
                                            $Five_Wicket_Points=$game_type_point['Five_Wicket']*$no_of_5w;
                                            if($inningsNumber<=2){
                                                $palyers[$scores['bowler_id']]['Five_Wicket']=$game_type_point['Five_Wicket']*$no_of_5w;
                                                $palyers[$scores['bowler_id']]['Five_Wicket_Value']=$no_of_5w;
                                            }else{
                                                $palyers[$scores['bowler_id']]['Five_Wicket']+=$game_type_point['Five_Wicket']*$no_of_5w;
                                                $palyers[$scores['bowler_id']]['Five_Wicket_Value']+=$no_of_5w;
                                            }

                                            $no_of_4w=(int)($scores['wickets']/4);
                                            if($no_of_5w>0){
                                                $no_of_4w=0;
                                            }
                                            $Four_Wicket_Points=$game_type_point['Four_Wicket']*$no_of_4w;
                                            if($inningsNumber<=2){
                                                $palyers[$scores['bowler_id']]['Four_Wicket']=$game_type_point['Four_Wicket']*$no_of_4w;
                                                $palyers[$scores['bowler_id']]['Four_Wicket_Value']=$no_of_4w;
                                            }else{
                                                 $palyers[$scores['bowler_id']]['Four_Wicket']+=$game_type_point['Four_Wicket']*$no_of_4w;
                                                $palyers[$scores['bowler_id']]['Four_Wicket_Value']+=$no_of_4w;
                                            }*/



                                            $no_of_5w=(int)($scores['wickets']/5);
                                            
                                            $Five_Wicket_Points=$game_type_point['Five_Wicket']*$no_of_5w;
                                            if($inningsNumber<=2){
                                                $palyers[$scores['bowler_id']]['Five_Wicket']=$game_type_point['Five_Wicket']*$no_of_5w;
                                                $palyers[$scores['bowler_id']]['Five_Wicket_Value']=$no_of_5w;
                                            }else{
                                                $palyers[$scores['bowler_id']]['Five_Wicket']+=$game_type_point['Five_Wicket']*$no_of_5w;
                                                $palyers[$scores['bowler_id']]['Five_Wicket_Value']+=$no_of_5w;
                                            }

                                            $no_of_4w=(int)($scores['wickets']/4);

                                            if($no_of_5w>0){
                                              $no_of_4w=0;
                                            }
                                            
                                            $Four_Wicket_Points=$game_type_point['Four_Wicket']*$no_of_4w;
                                            if($inningsNumber<=2){
                                                $palyers[$scores['bowler_id']]['Four_Wicket']=$game_type_point['Four_Wicket']*$no_of_4w;
                                                $palyers[$scores['bowler_id']]['Four_Wicket_Value']=$no_of_4w;
                                            }else{
                                                 $palyers[$scores['bowler_id']]['Four_Wicket']+=$game_type_point['Four_Wicket']*$no_of_4w;
                                                $palyers[$scores['bowler_id']]['Four_Wicket_Value']+=$no_of_4w;
                                            }



                                            $no_of_3w=(int)($scores['wickets']/3);

                                            if( $no_of_5w>0 || $no_of_4w>0){
                                              $no_of_3w=0;
                                            }
                                            
                                            $Three_Wicket_Points=$game_type_point['Three_Wicket']*$no_of_3w;
                                            if($inningsNumber<=2){
                                                $palyers[$scores['bowler_id']]['Three_Wicket']=$game_type_point['Three_Wicket']*$no_of_3w;
                                                $palyers[$scores['bowler_id']]['Three_Wicket_Value']=$no_of_3w;
                                            }else{
                                                 $palyers[$scores['bowler_id']]['Three_Wicket']+=$game_type_point['Three_Wicket']*$no_of_3w;
                                                $palyers[$scores['bowler_id']]['Three_Wicket_Value']+=$no_of_3w;
                                            }


                                            $no_of_2w=(int)($scores['wickets']/2);

                                            if($no_of_5w>0 || $no_of_4w>0 || $no_of_3w>0){
                                              $no_of_2w=0;
                                            }
                                            
                                            $Two_Wicket_Points=$game_type_point['Two_Wicket']*$no_of_2w;
                                            if($inningsNumber<=2){
                                                $palyers[$scores['bowler_id']]['Two_Wicket']=$game_type_point['Two_Wicket']*$no_of_2w;
                                                $palyers[$scores['bowler_id']]['Two_Wicket_Value']=$no_of_2w;
                                            }else{
                                                 $palyers[$scores['bowler_id']]['Two_Wicket']+=$game_type_point['Two_Wicket']*$no_of_2w;
                                                $palyers[$scores['bowler_id']]['Two_Wicket_Value']+=$no_of_2w;
                                            }


                                            
                                            if(empty($scores['econ'])){
                                                    $scores['econ']=0;
                                            }

                                            if(empty($scores['overs'])){
                                                    $scores['overs']=0;
                                            }


                                            $Economy_Rate_decode=json_decode($game_type_point['Economy_Rate'],true);

                                             $Economy_Rate_Points=0;

                                            if($scores['overs']>=$game_type_point['Minimum_Overs_for_Economy_Rate']){

                                                foreach($Economy_Rate_decode as $Economy_Rate_decode_value){

                                                    if($scores['econ']>=$Economy_Rate_decode_value['min'] && $scores['econ']<=$Economy_Rate_decode_value['max']){
                                                        $Economy_Rate_Points=$Economy_Rate_decode_value['val'];
                                                        break;

                                                    }


                                                }
                                            }



                                            if($inningsNumber<=2){

                                                $palyers[$scores['bowler_id']]['Economy_Rate']=$Economy_Rate_Points;
                                                $palyers[$scores['bowler_id']]['Economy_Rate_Value']=$scores['econ'];
                                            }else{

                                                $palyers[$scores['bowler_id']]['Economy_Rate']+=$Economy_Rate_Points;
                                                $palyers[$scores['bowler_id']]['Economy_Rate_Value'].="/".$scores['econ'];

                                            }


                                             $palyers[$scores['bowler_id']]['total_points']+= $Wicket_Points+$Maiden_Over_Points+$Five_Wicket_Points+$Four_Wicket_Points+$Economy_Rate_Points+$Three_Wicket_Points+$Two_Wicket_Points;


                                } 

                        }   


                        if(!empty($innigsdarta['fielder'])){

                                foreach($innigsdarta['fielder'] as $fielding_scores){



                                            $scores=$fielding_scores;
                                            if(!isset($scores['fielder_id']) || !isset($palyers[$scores['fielder_id']])){

                                                continue;
                                            }

                                            if(empty($scores['catches'])){
                                                $scores['catches']=0;
                                            }
                                            $Catch_Points=$game_type_point['Catch']*$scores['catches'];
                                            if($inningsNumber<=2){
                                                $palyers[$scores['fielder_id']]['Catch']=$game_type_point['Catch']*$scores['catches'];
                                                $palyers[$scores['fielder_id']]['Catch_Value']=$scores['catches'];
                                            }else{
                                                $palyers[$scores['fielder_id']]['Catch']+=$game_type_point['Catch']*$scores['catches'];
                                                $palyers[$scores['fielder_id']]['Catch_Value']+=$scores['catches'];
                                            }

                                            if(empty($scores['stumping'])){
                                                $scores['stumping']=0;
                                            }
                                            $Stumping_Points=$game_type_point['Stumping']*$scores['stumping'];
                                            if($inningsNumber<=2){
                                                $palyers[$scores['fielder_id']]['Stumping']=$game_type_point['Stumping']*$scores['stumping'];
                                                $palyers[$scores['fielder_id']]['Stumping_Value']=$scores['stumping'];
                                            }else{
                                                $palyers[$scores['fielder_id']]['Stumping']+=$game_type_point['Stumping']*$scores['stumping'];
                                                $palyers[$scores['fielder_id']]['Stumping_Value']+=$scores['stumping'];
                                            }

                                            /*if(empty($scores['runout_catcher'])){
                                                $scores['runout_catcher']=0;
                                            }
                                            if(empty($scores['runout_direct_hit'])){
                                                $scores['runout_direct_hit']=0;
                                            }
                                            if(empty($scores['runout_thrower'])){
                                                $scores['runout_thrower']=0;
                                            }
                                            $Run_Out_Points=$game_type_point['Run_Out']*($scores['runout_catcher']+$scores['runout_direct_hit']+$scores['runout_thrower']);
                                            if($inningsNumber<=2){
                                                $palyers[$scores['fielder_id']]['Run_Out']=$game_type_point['Run_Out']*($scores['runout_catcher']+$scores['runout_direct_hit']+$scores['runout_thrower']);
                                                $palyers[$scores['fielder_id']]['Run_Out_Value']=($scores['runout_catcher']+$scores['runout_direct_hit']+$scores['runout_thrower']);
                                            }else{
                                                $palyers[$scores['fielder_id']]['Run_Out']+=$game_type_point['Run_Out']*($scores['runout_catcher']+$scores['runout_direct_hit']+$scores['runout_thrower']);
                                                $palyers[$scores['fielder_id']]['Run_Out_Value']+=($scores['runout_catcher']+$scores['runout_direct_hit']+$scores['runout_thrower']);
                                            }*/


                                            if(empty($scores['runout_direct_hit'])){
                                                $scores['runout_direct_hit']=0;
                                            }
                                           
                                           // $Run_Out_Points=$game_type_point['Run_Out']*($scores['runout_catcher']+$scores['runout_direct_hit']+$scores['runout_thrower']);

                                            $Run_Out_Points=$game_type_point['Run_Out']*($scores['runout_direct_hit']);

                                            if($inningsNumber<=2){
                                                $palyers[$scores['fielder_id']]['Run_Out']=$game_type_point['Run_Out']*($scores['runout_direct_hit']);
                                                $palyers[$scores['fielder_id']]['Run_Out_Value']=($scores['runout_direct_hit']);
                                            }else{
                                                $palyers[$scores['fielder_id']]['Run_Out']+=$game_type_point['Run_Out']*($scores['runout_direct_hit']);
                                                $palyers[$scores['fielder_id']]['Run_Out_Value']+=($scores['runout_direct_hit']);
                                            }


                                            if(empty($scores['runout_catcher'])){
                                                $scores['runout_catcher']=0;
                                            }

                                            $Run_Out_Catcher_Points=$game_type_point['Run_Out_Catcher']*($scores['runout_catcher']);
                                             if($inningsNumber<=2){
                                                $palyers[$scores['fielder_id']]['Run_Out_Catcher']=$game_type_point['Run_Out_Catcher']*($scores['runout_catcher']);
                                                $palyers[$scores['fielder_id']]['Run_Out_Catcher_Value']=($scores['runout_catcher']);
                                            }else{
                                                $palyers[$scores['fielder_id']]['Run_Out_Catcher']+=$game_type_point['Run_Out_Catcher']*($scores['runout_catcher']);
                                                $palyers[$scores['fielder_id']]['Run_Out_Catcher_Value']+=($scores['runout_catcher']);
                                            }


                                             if(empty($scores['runout_thrower'])){
                                                $scores['runout_thrower']=0;
                                            }


                                            $Run_Out_Thrower_Points=$game_type_point['Run_Out_Thrower']*($scores['runout_thrower']);
                                             if($inningsNumber<=2){
                                                $palyers[$scores['fielder_id']]['Run_Out_Thrower']=$game_type_point['Run_Out_Thrower']*($scores['runout_thrower']);
                                                $palyers[$scores['fielder_id']]['Run_Out_Thrower_Value']=($scores['runout_thrower']);
                                            }else{
                                                $palyers[$scores['fielder_id']]['Run_Out_Thrower']+=$game_type_point['Run_Out_Thrower']*($scores['runout_thrower']);
                                                $palyers[$scores['fielder_id']]['Run_Out_Thrower_Value']+=($scores['runout_thrower']);
                                            }


                                            $palyers[$scores['fielder_id']]['total_points']+= $Catch_Points+$Stumping_Points+$Run_Out_Points+$Run_Out_Catcher_Points+$Run_Out_Thrower_Points;


                                      

                                } 

                        } 
                    }
                }        
            }

            $output=array();
            $output['players']=$palyers;
            $output['innings']=$innings;
            $output['scorecard_data']=$scorecardData;

            $man_of_the_match=array();
            if($api_data_array['verified']=="true" && ($api_data_array['status']==2 || $api_data_array['status']==4)){
                $man_of_the_match['pid']="";
                $man_of_the_match['name']="";
            }
            $output['man-of-the-match']=$man_of_the_match;


            return $output;

  }

    public function upcoming_matches_series(){


     $curl = curl_init();

            curl_setopt_array($curl, array(
            CURLOPT_URL => ENTITYSPORT_MATCHE_PLAYER."?status=1&per_page=100&pre_squad=true&token=".ENTITYSPORT_APIKEY,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache"
              ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            $api_data=$response;
            

            $api_data_array=json_decode($api_data,true);

            $output=array();

            if($api_data_array['status']=="ok"){
                $api_data_array=isset($api_data_array['response'])?$api_data_array['response']:array();

                

                if(!empty($api_data_array['items'])){

                    $selectedSeries=array();
                    foreach($api_data_array['items'] as $items){
                        $series=NULL;
                        $competition=$items['competition'];
                        if(!empty($competition)){
                            if(!isset($competition['cid'])  || isset($selectedSeries[$competition['cid']])){
                                continue;
                            }
                            $series['unique_id']=$competition['cid'];
                            $series['title']=$competition['title'];
                            $series['abbr']=$competition['abbr'];
                            $series['type']=$competition['type'];
                            $series['season']=$competition['season'];

                            $selectedSeries[$series['unique_id']]=$series['unique_id'];

                            $output[]=$series;
                        }

                    }


                }
            }


                    $result=array();
                    $result['matches']=$output;
                    print_r($output);
                    return $result;
  }
  
  public function getMatchStatus($match_id){
            
            $curl = curl_init();
            curl_setopt_array($curl, array(
            CURLOPT_URL => ENTITYSPORT_MATCHE_PLAYER.$match_id."/info?token=".ENTITYSPORT_APIKEY,
            //CURLOPT_URL => 'https://rest.entitysport.com/v2/matches/'.$match_id."/scorecard?token=".ENTITYSPORT_APIKEY,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
            CURLOPT_HTTPHEADER => array(
                "Postman-Token: e8439a8d-4242-457f-b187-56adf512fbe6",
                "cache-control: no-cache"
              ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            $api_data=$response;
        
            $api_data_array=json_decode($api_data,true);

            return $api_data_array;
  }
  

  public function upcoming_matches($series_id){

echo ENTITYSPORT_MATCHE_PLAYER."?status=1&pre_squad=true&per_page=50&token=".ENTITYSPORT_APIKEY;
     $curl = curl_init();

            curl_setopt_array($curl, array(
            CURLOPT_URL => ENTITYSPORT_MATCHE_PLAYER."?status=1&pre_squad=true&per_page=50&token=".ENTITYSPORT_APIKEY,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache"
              ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            $api_data=$response;
           

            $api_data_array=json_decode($api_data,true);
            //  echo '<pre>';
            // print_r($api_data_array);die;
            $output=array();

            if($api_data_array['status']=="ok"){
                $api_data_array=isset($api_data_array['response'])?$api_data_array['response']:array();

                if(!empty($api_data_array['items'])){
date_default_timezone_set('Asia/Kolkata');
  
                  foreach($api_data_array['items'] as $items){
                         
                    $series=NULL;
                    $competition=$items['competition'];
                    if(!empty($competition)){
                        $series['unique_id']=$competition['cid'];
                        $series['title']=$competition['title'];
                        $series['abbr']=$competition['abbr'];
                        $series['type']=$competition['type'];
                        $series['season']=$competition['season'];
                    }

                    if($series==NULL || ($series_id!="0" && $series_id!=$series['unique_id'])){
                        continue;
                    }

                    $match['date']=$items['timestamp_start'];
                    $match['enddate']=$items['timestamp_end'];
                    $match['format']=$items['format'];
                    $match['format_str']=$items['format_str'];
                    $match['dateTimeGMT']=$items['date_start'].".000Z";
                    $match['matchStarted']=false;
                    $match['status']=$items['status'];
                    $match['status_str']= $items['status_str'];
                    $match['squad']=true;
                    $match['team-1']=$items['teama']['name'];
                    $match['team-1-short_name']=$items['teama']['short_name'];
                    $match['team-2']=$items['teamb']['name'];
                    $match['team-2-short_name']=$items['teamb']['short_name'];
                    $match['type']=$items['format_str'];
                    $match['unique_id']=$items['match_id'];
                    $match['title']=$items['title'];
                    $match['short_title']=$items['short_title'];
                    $match['subtitle']=$items['subtitle'];
                    $match['series_data']=$series;
                    $match['squad_data']=$this->match_squade($match['unique_id']);
                    $match['teams_data']=array('teama'=>$items['teama'],'teamb'=>$items['teamb']);

                    $output[]=$match;
/*echo '<pre>';
print_r($output);
echo '</pre>';
die;*/
                  }


                }
            }


                    $result=array();
                    $result['matches']=$output;
                    return $result;
  }


  public function upcoming_matches_football($series_id){


     $curl = curl_init();

            curl_setopt_array($curl, array(
            CURLOPT_URL => "https://soccer.entitysport.com/matches?status=1&pre_squad=true&per_page=50&token=fa5fe330735d2249a1323b5772529035",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache"
              ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            $api_data=$response;
           

            $api_data_array=json_decode($api_data,true);
            //   echo '<pre>';
            //  print_r($api_data_array);die;
            $output=array();

            if($api_data_array['status']=="ok"){
                $api_data_array=isset($api_data_array['response'])?$api_data_array['response']:array();

                if(!empty($api_data_array['items'])){
date_default_timezone_set('Asia/Kolkata');
  
                  foreach($api_data_array['items'] as $items){
                         
                    $series=NULL;
                    $competition=$items['competition'];
                    $items['format'] = "Football";
                     $items['format_str'] = "Football";
                   if(!empty($competition)){
                        $series['unique_id']=$competition['cid'];
                        $series['title']=$competition['cname'];
                        $series['abbr']=$competition['cname'];
                        $series['type']="tournament";
                        $series['season']=$competition['year'];
                        $items['format_str'] = $competition['cname'];
                         $items['format'] = $competition['cname'];
                   }
                    if($series==NULL || ($series_id!="0" && $series_id!=$series['unique_id'])){
                        continue;
                    }
                   // print_r($series);
                    $match['date']=$items['timestampstart'];
                    $match['enddate']=$items['timestampend'];
                    $match['format']=$items['format'];
                    $match['format_str']=$items['format_str'];
                    $match['dateTimeGMT']=$items['datestart'].".000Z";
                    $match['matchStarted']=false;
                    $match['status']=$items['status'];
                    $match['status_str']= $items['status_str'];
                    $match['squad']=true;
                    $match['team-1']=$items['teams']['home']['fullname'];
                    $match['team-1-short_name']=$items['teams']['home']['abbr'];
                    $match['team-2']=$items['teams']['away']['fullname'];
                    $match['team-2-short_name']=$items['teams']['away']['abbr'];
                    $match['type']=$items['format_str'];
                    $match['unique_id']=$items['mid'];
                    $match['title']=$items['teams']['home']['fullname'].' VS '.$items['teams']['away']['fullname'];
                    $match['short_title']=$items['teams']['home']['abbr'].' VS '.$items['teams']['away']['abbr'];
                    $match['subtitle']=$items['teams']['home']['abbr'].' VS '.$items['teams']['away']['abbr'];
                    $match['series_data']=$series;
                    $teamplayer = $this->match_squade_football($items['teams']['home']['tid'],$items['teams']['away']['tid'],$items['teams']);

                    $match['squad_data']=$teamplayer;
                    
                    $aryTeam1 = array();
                    $aryTeam1 = $items['teams']['home'];
                    $aryTeam1['name']= $items['teams']['home']['tname'];
                    
                    $aryTeam2 = array();
                    $aryTeam2 = $items['teams']['away'];
                    $aryTeam2['name']= $items['teams']['away']['tname'];
                    $match['teams_data']=array('teama'=>$aryTeam1,'teamb'=>$aryTeam2);

                    $output[]=$match;
/*echo '<pre>';
print_r($output);
echo '</pre>';
die;*/
                  }


                }
            }


                    $result=array();
                    $result['matches']=$output;
                    return $result;
  }


  public function match_squade_football($match_unique_id,$team2_id,$teams) {

            $curl = curl_init();

            curl_setopt_array($curl, array(
            CURLOPT_URL => "https://soccer.entitysport.com/team/$match_unique_id/info?token=fa5fe330735d2249a1323b5772529035",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache"
              ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            $api_data=$response;

            $api_data_array=json_decode($api_data,true);
            $api_data_array=isset($api_data_array['response'])?$api_data_array['response']:array();
            //   echo '<pre>';
            //     print_r($api_data_array['items']);die;

    $teama_palyers=array();
             $teamb_palyers=array();

             $teama_name="";
             $teamb_name="";
             
            $playerDetailData=array();
            if(!empty($api_data_array['items'][0]['player'])){

    // echo '<pre>';
    //              print_r($api_data_array['items'][0]);die;
                foreach($api_data_array['items'][0]['player'] as $player){

                    $singlePlayerData=array();

                    $singlePlayerData['pid']=$player['pid'];
                    $singlePlayerData['name']=$player['fullname'];
                    $singlePlayerData['short_name']=$player['fullname'];
                    $singlePlayerData['battingStyle']=$player['positionname'];
                    $singlePlayerData['bowlingStyle']=$player['positionname'];
                    $singlePlayerData['credits']=$player['fantasy_player_rating'];

                     if(strtolower($player['positionname'])=='defender'){
                      $singlePlayerData['playingRole']="batsman";
                    }else if(strtolower($player['positionname'])=='forward'){
                      $singlePlayerData['playingRole']="bowler";
                    }else if(strtolower($player['positionname'])=='midfielder'){
                      $singlePlayerData['playingRole']="allrounder";
                    }else if(strtolower($player['positionname'])=='goalkeeper' || strtolower($player['positionname'])=='goalkeeper'){
                      $singlePlayerData['playingRole']="wicketkeeper";
                    }else{
                      $singlePlayerData['playingRole']=strtolower($player['positionname']);
                    }
                    ///$singlePlayerData['playingRole']= strtolower($player['positionname']);
                    $realBirthday=$player['birthdate'];
                    if($realBirthday=="0000-00-00"){
                        $realBirthday="";
                    }else{
                        $realBirthday=$realBirthday.",";
                    }
                    $singlePlayerData['born']=$realBirthday;
                    $singlePlayerData['country']=$player['nationality']['name'];
                    $playerDetailData[$player['pid']]=$singlePlayerData;
                    
                    
                     $playerdetail=array();
                      $playerdetail['pid']=$player['pid'];
                      $playerdetail['name']=$player['fullname'];
                         $playerdetail['credits']=$singlePlayerData['credits'];
                         $playerdetail['detail_data']=$singlePlayerData;
                      $teama_palyers[]=$playerdetail;
                      
                      
                }

            }
// echo '<pre>';
//         print_r($playerDetailData);die;




  $curl = curl_init();

            curl_setopt_array($curl, array(
            CURLOPT_URL => "https://soccer.entitysport.com/team/$team2_id/info?token=1ae70ef4de6fbc308cc7699b3ec6aca5",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache"
              ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            $api_data=$response;

            $api_data_array=json_decode($api_data,true);
            $api_data_array=isset($api_data_array['response'])?$api_data_array['response']:array();
            //   echo '<pre>';
            //     print_r($api_data_array['items']);die;

            $playerDetailData2=array();
            if(!empty($api_data_array['items'][0]['player'])){

    // echo '<pre>';
    //              print_r($api_data_array['items'][0]);die;
                foreach($api_data_array['items'][0]['player'] as $player){

                    $singlePlayerData=array();

                    $singlePlayerData['pid']=$player['pid'];
                    $singlePlayerData['name']=$player['fullname'];
                    $singlePlayerData['short_name']=$player['fullname'];
                    $singlePlayerData['battingStyle']=$player['positionname'];
                    $singlePlayerData['bowlingStyle']=$player['positionname'];
                    $singlePlayerData['credits']=$player['fantasy_player_rating'];
                    ///echo $player['positionname'];
                    if(strtolower($player['positionname'])=='defender'){
                      $singlePlayerData['playingRole']="batsman";
                    }else if(strtolower($player['positionname'])=='forward'){
                      $singlePlayerData['playingRole']="bowler";
                    }else if(strtolower($player['positionname'])=='midfielder'){
                      $singlePlayerData['playingRole']="allrounder";
                    }else if(strtolower($player['positionname'])=='goalkeeper' || strtolower($player['positionname'])=='goalkeeper'){
                      $singlePlayerData['playingRole']="wicketkeeper";
                    }else{
                      $singlePlayerData['playingRole']=strtolower($player['positionname']);
                    }
                   /// $singlePlayerData['playingRole']= strtolower($player['positionname']);
                    $realBirthday=$player['birthdate'];
                    if($realBirthday=="0000-00-00"){
                        $realBirthday="";
                    }else{
                        $realBirthday=$realBirthday.",";
                    }
                    $singlePlayerData['born']=$realBirthday;
                    $singlePlayerData['country']=$player['nationality']['name'];
                    $playerDetailData2[$player['pid']]=$singlePlayerData;
                    
                     $playerdetail=array();
                      $playerdetail['pid']=$player['pid'];
                      $playerdetail['name']=$player['fullname'];
                         $playerdetail['credits']=$singlePlayerData['credits'];
                         $playerdetail['detail_data']=$singlePlayerData;
                      $teamb_palyers[]=$playerdetail;
                }

            }
            
            
            // print_r($teams);die;

            $teamsDetail=array();
            if(!empty($teams)){
                $cnt = 0;
                foreach($teams as $team_data){
                  $teamsDetail[$team_data['tid']]['id']=$team_data['tid'];
                  $teamsDetail[$team_data['tid']]['name']=$team_data['tname'];
                    if($cnt==0)
                    {
                               $teama_name=$team_data['tname'];
                    }
                    if($cnt==1)
                    {
                               $teamb_name=$team_data['tname'];
                    }
                    $cnt++;
                }

            }


         

             



            // if(!empty($api_data_array['teama']) && !empty($api_data_array['teama']['squads'])){


            //     foreach($api_data_array['teama']['squads'] as $team){

            //           $playerdetail=array();
            //           $playerdetail['pid']=$team['player_id'];
            //           $playerdetail['name']=$team['name'];
            //           if(isset($playerDetailData[$playerdetail['pid']])){
            //             $playerdetail['credits']=$playerDetailData[$playerdetail['pid']]['credits'];
            //              $playerdetail['detail_data']=$playerDetailData[$playerdetail['pid']];
            //           }else{
            //              $playerdetail['detail_data']=NULL;
            //           }

            //           $teama_palyers[]=$playerdetail;
            //     }                      
            // }

            // if(!empty($api_data_array['teamb']) && !empty($api_data_array['teamb']['squads'])){

            //   $teamb_name=$teamsDetail[$api_data_array['teamb']['team_id']]['name'];

            //     foreach($api_data_array['teamb']['squads'] as $team){
                   
            //           $playerdetail=array();
            //           $playerdetail['pid']=$team['player_id'];
            //           $playerdetail['name']=$team['name'];
            //           if(isset($playerDetailData[$playerdetail['pid']])){
            //             $playerdetail['credits']=$playerDetailData[$playerdetail['pid']]['credits'];
            //              $playerdetail['detail_data']=$playerDetailData[$playerdetail['pid']];
            //           }else{
            //              $playerdetail['detail_data']=NULL;
            //           }

            //           $teamb_palyers[]=$playerdetail;
            //     }                      
            // }

            $squad=array();
            $squad[0]['name']=$teama_name;
            $squad[0]['players']=$teama_palyers;

            $squad[1]['name']=$teamb_name;
            $squad[1]['players']=$teamb_palyers;



             $data=array();
             $data['squad']=$squad;
             return $data;

  }



 public function upcoming_matches_basketball($series_id){


     $curl = curl_init();

            curl_setopt_array($curl, array(
            CURLOPT_URL => "https://basketball.entitysport.com/matches?status=1&pre_squad=true&per_page=50&token=e1f723786f8e06876189839ae14c611e",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache"
              ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            $api_data=$response;
           

            $api_data_array=json_decode($api_data,true);
            //   echo '<pre>';
            //  print_r($api_data_array);die;
            $output=array();

            if($api_data_array['status']=="ok"){
                $api_data_array=isset($api_data_array['response'])?$api_data_array['response']:array();

                if(!empty($api_data_array['items'])){
date_default_timezone_set('Asia/Kolkata');
  
                  foreach($api_data_array['items'] as $items){
                         
                    $series=NULL;
                    $competition=$items['competition'];
                    $items['format'] = "Basketball";
                     $items['format_str'] = "Basketball";
                   if(!empty($competition)){
                        $series['unique_id']=$competition['cid'];
                        $series['title']=$competition['cname'];
                        $series['abbr']=$competition['cname'];
                        $series['type']="tournament";
                        $series['season']=$competition['year'];
                        $items['format_str'] = $competition['cname'];
                         $items['format'] = $competition['cname'];
                   }
                    if($series==NULL || ($series_id!="0" && $series_id!=$series['unique_id'])){
                        continue;
                    }
                   // print_r($series);
                    $match['date']=$items['timestampstart'];
                    $match['enddate']=$items['timestampend'];
                    $match['format']=$items['format'];
                    $match['format_str']=$items['format_str'];
                    $match['dateTimeGMT']=$items['datestart'].".000Z";
                    $match['matchStarted']=false;
                    $match['status']=$items['status'];
                    $match['status_str']= $items['status_str'];
                    $match['squad']=true;
                    $match['team-1']=$items['teams']['home']['fullname'];
                    $match['team-1-short_name']=$items['teams']['home']['abbr'];
                    $match['team-2']=$items['teams']['away']['fullname'];
                    $match['team-2-short_name']=$items['teams']['away']['abbr'];
                    $match['type']=$items['format_str'];
                    $match['unique_id']=$items['mid'];
                    $match['title']=$items['teams']['home']['fullname'].' VS '.$items['teams']['away']['fullname'];
                    $match['short_title']=$items['teams']['home']['abbr'].' VS '.$items['teams']['away']['abbr'];
                    $match['subtitle']=$items['teams']['home']['abbr'].' VS '.$items['teams']['away']['abbr'];
                    $match['series_data']=$series;
                    $teamplayer = $this->match_squade_basketball($items['teams']['home']['tid'],$items['teams']['away']['tid'],$items['teams']);

                    $match['squad_data']=$teamplayer;
                    
                    $aryTeam1 = array();
                    $aryTeam1 = $items['teams']['home'];
                    $aryTeam1['name']= $items['teams']['home']['tname'];
                    
                    $aryTeam2 = array();
                    $aryTeam2 = $items['teams']['away'];
                    $aryTeam2['name']= $items['teams']['away']['tname'];
                    $match['teams_data']=array('teama'=>$aryTeam1,'teamb'=>$aryTeam2);

                    $output[]=$match;
/*echo '<pre>';
print_r($output);
echo '</pre>';
die;*/
                  }


                }
            }


                    $result=array();
                    $result['matches']=$output;
                    return $result;
  }


  public function match_squade_basketball($match_unique_id,$team2_id,$teams) {

           /// echo 'https://basketball.entitysport.com/team/'.$match_unique_id.'/info?token=4089ad0a58c7a4bcc232218ef2f7e240';
            $curl = curl_init();

            curl_setopt_array($curl, array(
            CURLOPT_URL => "https://basketball.entitysport.com/team/$match_unique_id/info?token=e1f723786f8e06876189839ae14c611e",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache"
              ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            $api_data=$response;

            $api_data_array=json_decode($api_data,true);
            $api_data_array=isset($api_data_array['response'])?$api_data_array['response']:array();
            //   echo '<pre>';
            //     print_r($api_data_array['items']);die;

    $teama_palyers=array();
             $teamb_palyers=array();

             $teama_name="";
             $teamb_name="";
             
            $playerDetailData=array();
            if(!empty($api_data_array['items'][0]['squads'])){

    // echo '<pre>';
    //              print_r($api_data_array['items'][0]);die;
                foreach($api_data_array['items'][0]['squads'] as $player){

                    $singlePlayerData=array();

                    $singlePlayerData['pid']=$player['pid'];
                    $singlePlayerData['name']=$player['fullname'];
                    $singlePlayerData['short_name']=$player['fullname'];
                    $singlePlayerData['battingStyle']=$player['primarypositionname'];
                    $singlePlayerData['bowlingStyle']=$player['primarypositionname'];
                    $singlePlayerData['credits']=$player['fantasyplayerrating'];

                     if(strtolower($player['primarypositionname'])=='shooting guard'){
                      $singlePlayerData['playingRole']="batsman";
                    }else if(strtolower($player['primarypositionname'])=='power forward'){
                      $singlePlayerData['playingRole']="bowler";
                    }else if(strtolower($player['primarypositionname'])=='small forward'){
                      $singlePlayerData['playingRole']="allrounder";
                    }else if(strtolower($player['primarypositionname'])=='point guard' || strtolower($player['primarypositionname'])=='point guard'){
                      $singlePlayerData['playingRole']="wicketkeeper";
                    }else{
                      $singlePlayerData['playingRole']=strtolower($player['primarypositionname']);
                    }
                    ///$singlePlayerData['playingRole']= strtolower($player['positionname']);
                    $realBirthday=$player['birthdate'];
                    if($realBirthday=="0000-00-00"){
                        $realBirthday="";
                    }else{
                        $realBirthday=$realBirthday.",";
                    }
                    $singlePlayerData['born']=$realBirthday;
                    $singlePlayerData['country']=$player['nationality']['name'];
                    $playerDetailData[$player['pid']]=$singlePlayerData;
                    
                    
                     $playerdetail=array();
                      $playerdetail['pid']=$player['pid'];
                      $playerdetail['name']=$player['fullname'];
                         $playerdetail['credits']=$singlePlayerData['credits'];
                         $playerdetail['detail_data']=$singlePlayerData;
                      $teama_palyers[]=$playerdetail;
                      
                      
                }

            }





  $curl = curl_init();

            curl_setopt_array($curl, array(
            CURLOPT_URL => "https://basketball.entitysport.com/team/$team2_id/info?token=e1f723786f8e06876189839ae14c611e",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache"
              ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            $api_data=$response;

            $api_data_array=json_decode($api_data,true);
            $api_data_array=isset($api_data_array['response'])?$api_data_array['response']:array();
            //   echo '<pre>';
            //     print_r($api_data_array['items']);die;

            $playerDetailData2=array();
            if(!empty($api_data_array['items'][0]['squads'])){

    // echo '<pre>';
    //              print_r($api_data_array['items'][0]);die;
                foreach($api_data_array['items'][0]['squads'] as $player){

                    $singlePlayerData=array();

                    $singlePlayerData['pid']=$player['pid'];
                    $singlePlayerData['name']=$player['fullname'];
                    $singlePlayerData['short_name']=$player['fullname'];
                    $singlePlayerData['battingStyle']=$player['positionname'];
                    $singlePlayerData['bowlingStyle']=$player['positionname'];
                    $singlePlayerData['credits']=$player['fantasyplayerrating'];
                    ///echo $player['positionname'];
                   if(strtolower($player['primarypositionname'])=='shooting guard'){
                      $singlePlayerData['playingRole']="batsman";
                    }else if(strtolower($player['primarypositionname'])=='power forward'){
                      $singlePlayerData['playingRole']="bowler";
                    }else if(strtolower($player['primarypositionname'])=='small forward'){
                      $singlePlayerData['playingRole']="allrounder";
                    }else if(strtolower($player['primarypositionname'])=='point guard' || strtolower($player['primarypositionname'])=='point guard'){
                      $singlePlayerData['playingRole']="wicketkeeper";
                    }else{
                      $singlePlayerData['playingRole']=strtolower($player['primarypositionname']);
                    }
                   /// $singlePlayerData['playingRole']= strtolower($player['positionname']);
                    $realBirthday=$player['birthdate'];
                    if($realBirthday=="0000-00-00"){
                        $realBirthday="";
                    }else{
                        $realBirthday=$realBirthday.",";
                    }
                    $singlePlayerData['born']=$realBirthday;
                    $singlePlayerData['country']=$player['nationality']['name'];
                    $playerDetailData2[$player['pid']]=$singlePlayerData;
                    
                     $playerdetail=array();
                      $playerdetail['pid']=$player['pid'];
                      $playerdetail['name']=$player['fullname'];
                         $playerdetail['credits']=$singlePlayerData['credits'];
                         $playerdetail['detail_data']=$singlePlayerData;
                      $teamb_palyers[]=$playerdetail;
                }

            }
            
            
            // print_r($teams);die;

            $teamsDetail=array();
            if(!empty($teams)){
                $cnt = 0;
                foreach($teams as $team_data){
                  $teamsDetail[$team_data['tid']]['id']=$team_data['tid'];
                  $teamsDetail[$team_data['tid']]['name']=$team_data['tname'];
                    if($cnt==0)
                    {
                               $teama_name=$team_data['tname'];
                    }
                    if($cnt==1)
                    {
                               $teamb_name=$team_data['tname'];
                    }
                    $cnt++;
                }

            }


         

             



            // if(!empty($api_data_array['teama']) && !empty($api_data_array['teama']['squads'])){


            //     foreach($api_data_array['teama']['squads'] as $team){

            //           $playerdetail=array();
            //           $playerdetail['pid']=$team['player_id'];
            //           $playerdetail['name']=$team['name'];
            //           if(isset($playerDetailData[$playerdetail['pid']])){
            //             $playerdetail['credits']=$playerDetailData[$playerdetail['pid']]['credits'];
            //              $playerdetail['detail_data']=$playerDetailData[$playerdetail['pid']];
            //           }else{
            //              $playerdetail['detail_data']=NULL;
            //           }

            //           $teama_palyers[]=$playerdetail;
            //     }                      
            // }

            // if(!empty($api_data_array['teamb']) && !empty($api_data_array['teamb']['squads'])){

            //   $teamb_name=$teamsDetail[$api_data_array['teamb']['team_id']]['name'];

            //     foreach($api_data_array['teamb']['squads'] as $team){
                   
            //           $playerdetail=array();
            //           $playerdetail['pid']=$team['player_id'];
            //           $playerdetail['name']=$team['name'];
            //           if(isset($playerDetailData[$playerdetail['pid']])){
            //             $playerdetail['credits']=$playerDetailData[$playerdetail['pid']]['credits'];
            //              $playerdetail['detail_data']=$playerDetailData[$playerdetail['pid']];
            //           }else{
            //              $playerdetail['detail_data']=NULL;
            //           }

            //           $teamb_palyers[]=$playerdetail;
            //     }                      
            // }

            $squad=array();
            $squad[0]['name']=$teama_name;
            $squad[0]['players']=$teama_palyers;

            $squad[1]['name']=$teamb_name;
            $squad[1]['players']=$teamb_palyers;



             $data=array();
             $data['squad']=$squad;
             return $data;

  }




  public function match_squade($match_unique_id) {

            $curl = curl_init();

            curl_setopt_array($curl, array(
            CURLOPT_URL => ENTITYSPORT_MATCHE_PLAYER.$match_unique_id."/squads?token=".ENTITYSPORT_APIKEY,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
            CURLOPT_HTTPHEADER => array(
                "Postman-Token: e8439a8d-4242-457f-b187-56adf512fbe6",
                "cache-control: no-cache"
              ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            $api_data=$response;
            

            $api_data_array=json_decode($api_data,true);
            $api_data_array=isset($api_data_array['response'])?$api_data_array['response']:array();


            $playerDetailData=array();
            if(!empty($api_data_array['players'])){

                foreach($api_data_array['players'] as $player){

                    $singlePlayerData=array();

                    $singlePlayerData['pid']=$player['pid'];
                    $singlePlayerData['name']=$player['title'];
                    $singlePlayerData['short_name']=$player['short_name'];
                    $singlePlayerData['battingStyle']=$player['batting_style'];
                    $singlePlayerData['bowlingStyle']=$player['bowling_style'];
                    $singlePlayerData['credits']=$player['fantasy_player_rating'];

                    if($player['playing_role']=='bat'){
                      $singlePlayerData['playingRole']="batsman";
                    }else if($player['playing_role']=='bowl'){
                      $singlePlayerData['playingRole']="bowler";
                    }else if($player['playing_role']=='all'){
                      $singlePlayerData['playingRole']="allrounder";
                    }else if($player['playing_role']=='wk' || $player['playing_role']=='wkbat'){
                      $singlePlayerData['playingRole']="wicketkeeper";
                    }else{
                      $singlePlayerData['playingRole']=strtolower($player['playing_role']);
                    }
                    
                    $realBirthday=$player['birthdate'];
                    if($realBirthday=="0000-00-00"){
                        $realBirthday="";
                    }else{
                        $realBirthday=$realBirthday.",";
                    }
                    $singlePlayerData['born']=$realBirthday;
                    $singlePlayerData['country']=$player['nationality'];


                    $playerDetailData[$player['pid']]=$singlePlayerData;
                }

            }

            //print_r($playerDetailData);die;




            $teamsDetail=array();
            if(!empty($api_data_array['teams'])){

                foreach($api_data_array['teams'] as $team_data){
                  $teamsDetail[$team_data['tid']]['id']=$team_data['tid'];
                  $teamsDetail[$team_data['tid']]['name']=$team_data['title'];
                }

            }


             $teama_palyers=array();
             $teamb_palyers=array();

             $teama_name="";
             $teamb_name="";

             



            if(!empty($api_data_array['teama']) && !empty($api_data_array['teama']['squads'])){

                $teama_name=$teamsDetail[$api_data_array['teama']['team_id']]['name'];

                foreach($api_data_array['teama']['squads'] as $team){

                      $playerdetail=array();
                      $playerdetail['pid']=$team['player_id'];
                      $playerdetail['name']=$team['name'];
                      if(isset($playerDetailData[$playerdetail['pid']])){
                        $playerdetail['credits']=$playerDetailData[$playerdetail['pid']]['credits'];
                         $playerdetail['detail_data']=$playerDetailData[$playerdetail['pid']];
                      }else{
                         $playerdetail['detail_data']=NULL;
                      }

                      $teama_palyers[]=$playerdetail;
                }                      
            }

            if(!empty($api_data_array['teamb']) && !empty($api_data_array['teamb']['squads'])){

              $teamb_name=$teamsDetail[$api_data_array['teamb']['team_id']]['name'];

                foreach($api_data_array['teamb']['squads'] as $team){
                   
                      $playerdetail=array();
                      $playerdetail['pid']=$team['player_id'];
                      $playerdetail['name']=$team['name'];
                      if(isset($playerDetailData[$playerdetail['pid']])){
                        $playerdetail['credits']=$playerDetailData[$playerdetail['pid']]['credits'];
                         $playerdetail['detail_data']=$playerDetailData[$playerdetail['pid']];
                      }else{
                         $playerdetail['detail_data']=NULL;
                      }

                      $teamb_palyers[]=$playerdetail;
                }                      
            }

            $squad=array();
            $squad[0]['name']=$teama_name;
            $squad[0]['players']=$teama_palyers;

            $squad[1]['name']=$teamb_name;
            $squad[1]['players']=$teamb_palyers;



             $data=array();
             $data['squad']=$squad;
             return $data;

  }


    public function player_finder($player_name) {

            $player_name=rawurlencode($player_name);

            $curl = curl_init();

            curl_setopt_array($curl, array(
            CURLOPT_URL => ENTITYSPORT_PLAYER."?per_page=500&search=".$player_name."&token=".ENTITYSPORT_APIKEY,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
            CURLOPT_HTTPHEADER => array(
                "Postman-Token: e8439a8d-4242-457f-b187-56adf512fbe6",
                "cache-control: no-cache"
              ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            $api_data=$response;
            

            $api_data_array=json_decode($api_data,true);
            $output=array();

            if($api_data_array['status']=="ok"){
                $api_data_array=isset($api_data_array['response'])?$api_data_array['response']:array();

                $items=isset($api_data_array['items'])?$api_data_array['items']:array();

                foreach ($items as $key => $player) {

                    $data=array();

                    $data['pid']=$player['pid'];
                    $data['fullName']=$player['title'];
                    $data['name']=$player['title'];

                    $data['battingStyle']=$player['batting_style'];
                    $data['bowlingStyle']=$player['bowling_style'];

                    if($player['playing_role']=='bat'){
                      $data['playingRole']="batsman";
                    }else if($player['playing_role']=='bowl'){
                      $data['playingRole']="bowler";
                    }else if($player['playing_role']=='all'){
                      $data['playingRole']="allrounder";
                    }else if($player['playing_role']=='wk' || $player['playing_role']=='wkbat'){
                      $data['playingRole']="wicketkeeper";
                    }else{
                      $data['playingRole']=strtolower($player['playing_role']);
                    }
                    
                    $realBirthday=$player['birthdate'];
                    if($realBirthday=="0000-00-00"){
                        $realBirthday="";
                    }else{
                        $realBirthday=$realBirthday.",";
                    }
                    $data['born']=$realBirthday;
                    $data['country']=$player['nationality'];

                    $output[]=$data;
                   
                }
            }

             $find_data=array();
             $find_data['data']=$output;
             return $find_data;
    }

  public function player_detail($player_id) {

            $curl = curl_init();

            curl_setopt_array($curl, array(
            CURLOPT_URL => ENTITYSPORT_PLAYER.$player_id."?token=".ENTITYSPORT_APIKEY,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
            CURLOPT_HTTPHEADER => array(
                "Postman-Token: e8439a8d-4242-457f-b187-56adf512fbe6",
                "cache-control: no-cache"
              ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            $api_data=$response;
            

            $api_data_array=json_decode($api_data,true);
            $api_data_array=isset($api_data_array['response'])?$api_data_array['response']:array();

            $player=isset($api_data_array['player'])?$api_data_array['player']:array();


            $data=array();

            $data['pid']=$player['pid'];
            $data['battingStyle']=$player['batting_style'];
            $data['bowlingStyle']=$player['bowling_style'];

            if($player['playing_role']=='bat'){
              $data['playingRole']="batsman";
            }else if($player['playing_role']=='bowl'){
              $data['playingRole']="bowler";
            }else if($player['playing_role']=='all'){
              $data['playingRole']="allrounder";
            }else if($player['playing_role']=='wk' || $player['playing_role']=='wkbat'){
              $data['playingRole']="wicketkeeper";
            }else{
              $data['playingRole']=strtolower($player['playing_role']);
            }
            
            $realBirthday=$player['birthdate'];
            if($realBirthday=="0000-00-00"){
                $realBirthday="";
            }else{
                $realBirthday=$realBirthday.",";
            }
            $data['born']=$realBirthday;
            $data['country']=$player['nationality'];
            




            
             return $data;

  }

  public function match_squade_roster($series_unique_id, $match_unique_id) {

            $curl = curl_init();
            $url=str_replace("{SERIES_ID}", $series_unique_id, ENTITYSPORT_MATCHE_PLAYER_SQUAD);
            $url=str_replace("{MATCH_ID}", $match_unique_id, $url);

            curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
            CURLOPT_HTTPHEADER => array(
                "Postman-Token: e8439a8d-4242-457f-b187-56adf512fbe6",
                "cache-control: no-cache"
              ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            $api_data=$response;
            

            $api_data_array=json_decode($api_data,true);
            if($api_data_array['status']!="ok"){
              return "UNABLE_TO_PROCEED";
            }
            
            

            $teamsDetail=array();

            $teama_palyers=array();
            $teamb_palyers=array();

            $teama_name="";
            $teamb_name="";

            $api_data_array=isset($api_data_array['response'])?$api_data_array['response']:array();

            $matchSquadData=isset($api_data_array['squads'])?$api_data_array['squads']:array();

            $teamNumber=1;
            foreach($matchSquadData as $squad){

              $teamData=$squad['team'];

              $teamsDetail[$teamData['tid']]['id']=$teamData['tid'];
              $teamsDetail[$teamData['tid']]['name']=$teamData['title'];

              if($teamNumber==1){
                $teama_name=$teamData['title'];
              }else {
                $teamb_name=$teamData['title'];
              }


              $playerSquadData=isset($squad['players'])?$squad['players']:array();

              foreach($playerSquadData as $player){


                    $singlePlayerData['pid']=$player['pid'];
                    $singlePlayerData['name']=$player['title'];
                    $singlePlayerData['short_name']=$player['short_name'];
                    $singlePlayerData['battingStyle']=$player['batting_style'];
                    $singlePlayerData['bowlingStyle']=$player['bowling_style'];
                    $singlePlayerData['credits']=$player['fantasy_player_rating'];

                    if($player['playing_role']=='bat'){
                      $singlePlayerData['playingRole']="batsman";
                    }else if($player['playing_role']=='bowl'){
                      $singlePlayerData['playingRole']="bowler";
                    }else if($player['playing_role']=='all'){
                      $singlePlayerData['playingRole']="allrounder";
                    }else if($player['playing_role']=='wk' || $player['playing_role']=='wkbat'){
                      $singlePlayerData['playingRole']="wicketkeeper";
                    }else{
                                            $singlePlayerData['playingRole']=strtolower($player['playing_role']);

                    }
                    
                    $realBirthday=$player['birthdate'];
                    if($realBirthday=="0000-00-00"){
                        $realBirthday="";
                    }else{
                        $realBirthday=$realBirthday.",";
                    }
                    $singlePlayerData['born']=$realBirthday;
                    $singlePlayerData['country']=$player['nationality'];



                    $playerdetail=array();
                    $playerdetail['pid']=$singlePlayerData['pid'];
                    $playerdetail['name']=$singlePlayerData['name'];
                    $playerdetail['credits']=$singlePlayerData['credits'];
                    $playerdetail['playingRole']=$singlePlayerData['playingRole'];
                    $playerdetail['detail_data']=$singlePlayerData;


                    if($teamNumber==1){
                       $teama_palyers[]=$playerdetail;
                    }else {
                      $teamb_palyers[]=$playerdetail;
                    }
              }

              $teamNumber++;
            }

            function sortByPlayerId($a, $b) {
              if($b['pid'] < $a['pid']){
                return 1;
              }else if($b['pid'] > $a['pid']){
                return -1;
              }
              else{
                return 0;
              }
            }

            usort($teamb_palyers, 'sortByPlayerId');
            usort($teama_palyers, 'sortByPlayerId');


            $squad=array();
          
            $squad[0]['name']=$teamb_name;
            $squad[0]['players']=$teamb_palyers;

            $squad[1]['name']=$teama_name;
            $squad[1]['players']=$teama_palyers;



             $data=array();
             $data['squad']=$squad;
             return $data;

  }


}
