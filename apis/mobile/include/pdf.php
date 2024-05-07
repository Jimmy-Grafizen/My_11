<?php
class contest_pdf {
	
	
	
	public function genrate_pdf($data){



		$message='<table width="100%" cellspacing="0" border="0">
	<colgroup span="2" width="147"></colgroup>
	<colgroup width="150"></colgroup>
	<colgroup width="147"></colgroup>
	<colgroup width="150"></colgroup>
	<colgroup width="147"></colgroup>
	<colgroup width="150"></colgroup>
	<colgroup width="147"></colgroup>
	<colgroup width="150"></colgroup>
	<colgroup span="2" width="147"></colgroup>
	<colgroup width="150"></colgroup>
	<tr>
	<td colspan="7">	RISING11</td>
	</tr>
	<tr>
	     <td  colspan=2 height="32" align="left" valign=middle bgcolor="#0e2141">
		</td>

		<td style="border-bottom: 1px solid #000000; text-align: center" colspan=2 height="32" align="left" valign=middle bgcolor="#0e2141">
		<b><font color="#ffffff" face="Arial" size=1>'.$data['match_name'].'</font></b>
		</td>

		<td style="border-bottom: 1px solid #000000; text-align: center" colspan=2 height="32" align="left" valign=middle bgcolor="#0e2141">
		<b><font color="#ffffff" face="Arial" size=1>Contest: Win Rs. '.$data['total_price'].'</font></b>
		</td>

		<td style="border-bottom: 1px solid #000000;  text-align: center" colspan=2 height="32" align="left" valign=middle bgcolor="#0e2141">
		<b><font color="#ffffff" face="Arial" size=1>Entry Fee Rs. '.$data['entry_fees'].'</font></b>
		</td>

		<td style="border-bottom: 1px solid #000000;  text-align: center" colspan=2 height="32" align="left" valign=middle bgcolor="#0e2141">
		<b><font color="#ffffff" face="Arial" size=1>Members: '.count($data['teams']).'</font></b>
		</td>
		<td style="border-bottom: 1px solid #000000;  text-align: center" colspan=2 height="32" align="left" valign=middle bgcolor="#0e2141">
		<b><font color="#ffffff" face="Arial" size=1>Invite code: '.$data['slug'].'</font></b>
		</td>
		</tr>
	<tr style="background-color: #f2f2f2;">
		<th style="border-top: 1px solid #000000; border-bottom: 1px solid #000000 font-size: 14px;font-weight: 600; border-left: 1px solid #000000; border-right: 1px solid #000000" height="10" align="left" valign=middle ><b><font face="Arial" size=1>User (Team)</font></b></td>
		<th style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" align="left" valign=middle ><b><font face="Arial" size=1>Player-1 (Captain)</font></b></td>
		<th style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" align="left" valign=middle ><b><font face="Arial" size=1>Player-2 (Vice Captain)</font></b></td>
		<th style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" align="left" valign=middle ><b><font face="Arial" size=1>Player-3 </font></b></td>
		<th style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" align="left" valign=middle ><b><font face="Arial" size=1>Player-4 </font></b></td>
		<th style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" align="left" valign=middle ><b><font face="Arial" size=1>Player-5 </font></b></td>
		<th style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" align="left" valign=middle ><b><font face="Arial" size=1>Player-6 </font></b></td>
		<th style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" align="left" valign=middle ><b><font face="Arial" size=1>Player-7 </font></b></td>
		<th style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" align="left" valign=middle ><b><font face="Arial" size=1>Player-8 </font></b></td>
		<th style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" align="left" valign=middle ><b><font face="Arial" size=1>Player-9 </font></b></td>
		<th style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" align="left" valign=middle ><b><font face="Arial" size=1>Player-10 </font></b></td>
		<th style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" align="left" valign=middle ><b><font face="Arial" size=1>Player-11 </font></b></td>
	</tr>';


	$i=1;
	foreach($data['teams'] as $value){
		if($i%2==0){
			$message.='<tr>';
		}else{
			$message.='<tr style="background-color: #bfbfbf;">';
		}
     	

     	$message.='<td style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" height="10" align="left" valign=middle ><b><font face="Arial" size=1>'.$value['team_name'].' (T'.$value['name'].')</font></b></td>';

     	 foreach($value['players'] as $p_value){

     	 	$message.='<td style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" align="left" valign=middle ><font face="Arial" size=1>'.$p_value['name'].'</font></td>';

     	 }

     	$message.='</tr>';
     	$i++;

    }
	
	$message.='</table>';

			
	return $message;
	}
	
		
	
	
}
?>
