<?php


class Entitysport {

     

    function __construct() {
                require_once  '../../../global_constants.php';
                
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

                   
                    if($team['playing11']=="true") {
                        $palyers[]=$team['player_id'];
                    }
                   

                }                      
            }

            if(!empty($api_data_array['teamb']) && !empty($api_data_array['teamb']['squads'])){

                foreach($api_data_array['teamb']['squads'] as $team){

                   
                    if($team['playing11']=="true") {
                        $palyers[]=$team['player_id'];
                    }
                   

                }                      
            }


            return $palyers;

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
            

            $api_data_array=json_decode($api_data,true);
            if(empty($api_data_array) || $api_data_array['status']!='ok'){
              $output=array();
              $output['players']=array();
              $output['scorecard_data']=NULL;
              $output['api_response']=$api_data_array;
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
                    return $result;
  }

  public function upcoming_matches($series_id){


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

                if(!empty($api_data_array['items'])){

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




                    $match['date']=$items['date_start'];
                    $match['dateTimeGMT']=$items['date_start'].".000Z";
                    $match['matchStarted']=false;
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

                    $output[]=$match;

                  }


                }
            }


                    $result=array();
                    $result['matches']=$output;
                    return $result;
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
                      $singlePlayerData['playingRole']="";
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
                      $data['playingRole']="";
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
              $data['playingRole']="";
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
                      $singlePlayerData['playingRole']="";
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
