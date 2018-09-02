<?php
session_start();
define('YEAR', 2018);
define('L_ID', 46324);
define('SEASON_START', '2018-09-04 04:00:00');

//data varibles
$FLEX_POSITIONS=array('RB', 'WR', 'TE');
$NUM_STARTERS=array('RB'=> 3, 'WR'=> 3, 'TE'=> 2);
$ipick='FF5555';
$vpick='FFFFFF';
$icarry='FF5555';
$vcarry='55ff55';


$host='localhost';
$user='mfl';
$pass='AyOkUPmRwM3yvoZs';
$connect = mysql_connect($host, $user, $pass) or die("SQL error");
mysql_select_db("mfl") or die (mysql_error());

function loadSchedule($week, $schedule){
    mysql_query("DELETE FROM schedule WHERE week='$week'");
    global $currentDate;
   // $slog=fopen("log/".date("Y-m-d_H:i:s", $currentDate)."/schedule", "a");
  //  print_r($schedule);
    $matchups=$schedule->matchup;
  //  print_r($matchups);
    foreach($matchups as $matchup) {
    //    print_r($matchup);
        $date=date('Y-m-d H:i:s', (int)$matchup['kickoff']);
        $teams=$matchup->team;
        foreach($teams as $team){
            $id=$team['id'];
            $query="INSERT INTO schedule (team_id, date, week) VALUES ('$id', '$date', '$week')";
            fwrite($slog,"$query\n");
 //           echo $query."<br />";
            mysql_query($query) or die(mysql_error());
        }
    }
 //   fclose($slog);
}


function loadPlayers(){
    mysql_query("TRUNCATE TABLE players");
    global $currentDate;
   // $plog=fopen("log/".date("Y-m-d_H:i:s", $currentDate)."/players", "a");

    $players=simplexml_load_file("http://football.myfantasyleague.com/".YEAR."/export?TYPE=players");
    $players=$players->player;
    foreach($players as $player){
        foreach ($player->attributes() as $key => $value)
            $arrays[$key]=mysql_real_escape_string($value);

        extract($arrays);
        $query="INSERT INTO players (id, name, position, team) VALUES ('$id', '$name', '$position', '$team')";
   //     echo "Adding $id, $name, $position, $team<br />";
        mysql_query($query) or die(mysql_error());
    }
   // fclose($plog);
}

function loadRosters($week, $weeklyResults){
    global $currentDate;
    mysql_query("DELETE FROM rosters WHERE week='$week'");
    /*$matchups=$weeklyResults->matchup;
    ?><pre><? print_r($weeklyResults->matchup[11]); ?></pre><?// die(); //continue; die();
*/
    foreach($weeklyResults->matchup as $delta => $matchup) {
      //  print_r($matchup); continue;

   foreach($matchup->franchise as $franchise){
            $starters=explode(',',$franchise['starters']);
            foreach($starters as $starter){
                if($starter!=''){
                    $id=$franchise['id'];
                    $query="SELECT id FROM rosters WHERE team_id='$id' AND player_id='$starter' AND week='$week'";
                    $result=mysql_query($query) or die(mysql_error());
                    if(mysql_num_rows($result)==0){
                        $query="INSERT INTO rosters (team_id, player_id, week) VALUES ('$id', '$starter', '$week')";
                        mysql_query($query) or die(mysql_error());
                    }
                }
            }
        }
    } //die();
    $franchises=$weeklyResults->franchise;
    foreach($franchises as $franchise){
        $starters=explode(',',$franchise['starters']);
        foreach($starters as $starter){
            if($starter!=''){
                $id=$franchise['id'];
                $query="SELECT id FROM rosters WHERE team_id='$id' AND player_id='$starter' AND week='$week'";
                $result=mysql_query($query) or die(mysql_error());
                if(mysql_num_rows($result)==0){
                    $query="INSERT INTO rosters (team_id, player_id, week) VALUES ('$id', '$starter', '$week')";
                    mysql_query($query) or die(mysql_error());
                }
            }
        }
    }

}

function loadTeams(){
    mysql_query("TRUNCATE TABLE teams");
    global $currentDate;
//	$tlog=fopen("log/".date("Y-m-d_H:i:s", $currentDate)."/teams", "a");
    $league=simplexml_load_file("http://football2.myfantasyleague.com/".YEAR."/export?TYPE=league&L=".L_ID);
    $franchises=$league->franchises[0]->franchise;
    foreach ($franchises as $franchise) {
        $id=$franchise['id'];
        $name=mysql_real_escape_string($franchise['name']);
        $query="INSERT INTO teams (id, name) VALUES ('$id', '$name')";
//		fwrite($tlog,"$query\n");
        mysql_query($query);
    }
//	fclose($tlog);
}

function checkValid($team_id, $player_id){
    global $FLEX_POSITIONS;
    global $NUM_STARTERS;
    global $week;
    $pos=getPos($player_id);
    $query="SELECT count(*) as total FROM rosters, players WHERE team_id='$team_id' AND week='$week' AND players.id=player_id AND position='$pos'";
    $result=mysql_query($query) or die(mysql_error());
    $row=mysql_fetch_array($result);
    $query="SELECT * FROM rosters WHERE team_id='$team_id' AND week='$week' AND player_id='$player_id'";
    $inroster=mysql_query($query) or die(mysql_error());
    return ($NUM_STARTERS[$pos]==$row['total'] && mysql_num_rows($inroster)==1);


}

function getPos($player_id){
    $query="SELECT position FROM players WHERE id='$player_id'";
    $result=mysql_query($query) or die(mysql_error());
    $row=mysql_fetch_array($result);
    return $row['position'];
}




extract($_GET);
if (!isset($id)) $id = '';
if (!isset($week) or $week==''){
    $dbStartDate=SEASON_START;
    $currentDate=time();
    $startDate=strtotime($dbStartDate);
    $week=ceil(($currentDate-$startDate)/604800);
    $week=$week<1?1:$week;
    $week=$week>21?21:$week;
}
//mkdir("log/".date("Y-m-d_H:i:s", $currentDate));
$stamp=date("H:i:s", $currentDate);
if ($true_id!=0 || $id=='') $team_id=$id=$true_id;
else $team_id=$id;
//$log=fopen("log/flex.log","a");
//fwrite($log,"\n\n\n$stamp - New entry - true_id=$true_id && id=$id\n");
//fwrite($log,"$stamp - Date_time=".date('Y-m-d_H:i:s', $currentDate)."\n");

if (isset($flex) && $flex!='' && $userchange!="TRUE") {
    $query="SELECT * FROM flex WHERE team_id='$id' AND week='$week'";
    $result=mysql_query($query) or die(mysql_error());
    if (mysql_num_rows($result) >0) $query="UPDATE flex SET player_id='$flex', carry_over='0' WHERE team_id='$id' AND week='$week'";
    else $query="INSERT INTO flex (team_id, player_id, week) VALUES ('$id', '$flex', '$week')";
    mysql_query($query) or die(mysql_error());
} else {
    $result_url = "http://football.myfantasyleague.com/".YEAR."/export?TYPE=weeklyResults&L=".L_ID."&W=$week";
  //  print($result_url);
    $weeklyResults=simplexml_load_file($result_url);
 /*   ?><pre><? print_r($weeklyResults); ?></pre><? die();*/
    $schedule=simplexml_load_file("http://football.myfantasyleague.com/".YEAR."/export?TYPE=nflSchedule&L=".L_ID."&W=$week");

    $query="SELECT * FROM roster_lock WHERE team_id='$id'";
    $result=mysql_query($query) or die(mysql_error());
    if (mysql_num_rows($result)==0){
        mysql_query("INSERT INTO roster_lock (team_id) VALUES ('$id')");
        loadRosters($week, $weeklyResults);
        loadSchedule($week, $schedule);
        loadTeams();
 	    loadPlayers();
    }


}






?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!--Copyright 2007 Fossit Solutions. All rights reserved-->
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
	<title>
		Flex Select
	</title>
<link rel="stylesheet" href="http://www9.myfantasyleague.com/fflnetdynamic2013/14936.css" type="text/css"  />
<link rel="stylesheet" href="http://www9.myfantasyleague.com/skins/style_4/4d.css" type="text/css"  />
<link rel="shortcut icon" href="http://www9.myfantasyleague.com/favicon.ico"  />
<style type='text/css'>
	body{
		background-color:transparent;
		background-image:none;
	}
</style>
    <script type="text/javascript" src="http://www9.myfantasyleague.com/mootools-core-1.3.1-full-compat-yc.js"></script>
    <script type="text/javascript" src="http://www9.myfantasyleague.com/mootools-more-1.3.1.1.js"></script>
    <script type="text/javascript" src="http://www9.myfantasyleague.com/mfl_common.js"></script>

<script type="text/javascript">
	window.onload=function(){
	//	alert('iframewrap'+document.getElementById('wrapper').clientHeight);
	//	alert('parent'+window.top.document);//=document.body.clientHeight+'px');
	}
</script>

</head>
<body><!--<div id='wrapper' style='position:absolute;top:0px;width:100%;padding:0px;margin:0px;'>--><?php

echo "<form name='flexform' method='get' action=''>";
echo "<input type='hidden' name='true_id' value='$true_id' />";
echo "<input type='hidden' name='week' value='$week' />";
echo "<div class='pagebody homepagecolumn'><table class='homepagemodule report' align='center' style='position:absolute;top:0px;width:100%;'><span><caption>Select Your Flex Player</caption></span><tbody><tr><th colspan='4'>";
echo "Current Flex Player: ";
$query="SELECT player_id, players.name, schedule.date curr_date FROM flex, players, schedule WHERE flex.week='$week' AND flex.team_id='$id' AND players.id=flex.player_id AND schedule.team_id=players.team AND schedule.week='$week' LIMIT 1";
$result=mysql_query($query) or die(mysql_error());
if(mysql_num_rows($result)==0) echo "No Flex Selected";
else {
	$row=mysql_fetch_array($result);
	extract($row);
	echo "$name";
}
echo "</th></tr>";
if ($true_id!=-1){
$trclass='oddtablerow';
echo "<input type='hidden' id='userchange' name='userchange' value='FALSE' />";
if ($true_id==0) {
	echo "<tr class='oddtablerow'><td colspan='4'><center><select name='id' ";
	echo "onchange=\"document.getElementById('userchange').value='TRUE';document.flexform.submit();\">";
	$query="SELECT * FROM teams";
	$result=mysql_query($query) or die(mysql_error());
	echo "<option value='0'>--Select a team--</option>";
	while ($row=mysql_fetch_array($result)){
		echo "<option value='".$row['id']."' ";
		if ($row['id']==$id) echo "selected='selected'";
		echo ">".$row['name']."</option>";
	}
	echo "</select></center>";
	$trclass='eventablerow';
}	

$pos='';
$query="SELECT * FROM rosters, players WHERE team_id='$id' AND rosters.player_id=players.id AND  position='RB' AND week='$week'";
$result=mysql_query($query) or die(mysql_error());
if (mysql_num_rows($result)==3) $pos='RB';
$query="SELECT * FROM rosters, players WHERE team_id='$id' AND rosters.player_id=players.id AND position='WR' AND week='$week'";
$result=mysql_query($query) or die(mysql_error());
if (mysql_num_rows($result)==3) $pos='WR';
$query="SELECT * FROM rosters, players WHERE team_id='$id' AND rosters.player_id=players.id AND position='TE' AND week='$week'";
$result=mysql_query($query) or die(mysql_error());
if (mysql_num_rows($result)==2) $pos='TE';
if ($pos=='') {
	echo "<tr class='$trclass'><td colspan=4>You have no eligible flex players. Please set your lineup properly, dumbass</td></tr>";
}
$query="SELECT rosters.team_id, players.*, schedule.date 
				FROM rosters, players, schedule
				WHERE players.id=rosters.player_id
				AND position='$pos' 
				AND rosters.team_id='$id'
				AND rosters.week='$week'
				AND schedule.team_id=players.team
				AND schedule.week='$week'";
fwrite($log, "$stamp - $query\n");
$result=mysql_query($query) or die(mysql_error());
while ($row=mysql_fetch_array($result)) {
	extract($row);
	echo "<tr class='$trclass'><td>";
//	echo strtotime($date)."  -  ".time()."  -  ".strtotime($curr_date);
	echo "<input type='radio' name='flex' value='$id' ";
	if ((strtotime($date)<time() || ($curr_date!='' && strtotime($curr_date)<time())) && $true_id!=0) echo "disabled='disabled' ";
	if ($player_id==$id) echo "checked='checked'";
	echo "/></td><td colspan='3'>$name</td></tr>";
	fwrite($log,"$stamp - Option given=$name\n");
	$trclass=$trclass=='oddtablerow'?'eventablerow':'oddtablerow';
}
echo "<tr class='$trclass'><td colspan=4><center><input type='submit' value='submit' /></center></td></tr>";
}echo "<tr><th colspan='4'>Current Flex picks</th></tr>";
$trclass='oddtablerow';
	echo "<tr class='oddtablerow'><td colspan='4'><center><select name='week' ";
	echo "onchange=\"document.getElementById('userchange').value='TRUE';document.flexform.submit();\">";
	for($i=1;$i<18;$i++){
		echo "<option value='$i'";
		if ($i==$week) echo " selected='selected'";
		echo ">Week $i</option>";
	}
echo "</select></center></td></tr>";
$trclass='eventablerow';

$query="SELECT * FROM teams";
$teams=mysql_query($query) or die(mysql_error());
while ($team=mysql_fetch_array($teams)) {
	echo "<tr class='$trclass'><td colspan='2'><a href='http://football2.myfantasyleague.com/".YEAR."/options?L=".L_ID."&F=".str_pad($team['id'], 4, "0", STR_PAD_LEFT)."&O=01' target='_top'>".$team['name']."</a></td>";
	$query="SELECT players.name, player_id, carry_over
					FROM flex, players
					WHERE week='$week' AND player_id=players.id AND team_id='";
	$query.=$team['id']."'";
	echo "<td colspan=2>";
	$result=mysql_query($query) or die(mysql_error());
	if (mysql_num_rows($result)==0){
		$query="SELECT players.name, player_id
						FROM flex, players
						WHERE week='".max($week-1,1)."'
							AND player_id=players.id
							AND team_id='".$team['id']."' LIMIT 1";
		$lresult=mysql_query($query) or die(mysql_error());
		if (mysql_num_rows($lresult)==1){
			$lrow=mysql_fetch_array($lresult);
			if (checkValid($team['id'], $lrow['player_id'])){
				$query="INSERT INTO flex (team_id, player_id, week, carry_over)
								VALUES ('".$team['id']."', '".$lrow['player_id']."', '$week', '1')";
				mysql_query($query) or die(mysql_error());
				echo "<span style='color:#$vcarry;font-weight:bold;'>".$lrow['name']."</span>";
			} else echo "<span style='color:#$icarry;font-weight:bold;'>".$lrow['name']."</span>";
		}	else echo "<span style='color:#$ipick;font-weight:bold;'>NO FLEX SELECTED</span>";
	} else {
		$row=mysql_fetch_array($result);
		if (checkValid($team['id'],$row['player_id'])){
			if ($row['carry_over'])
				echo "<span style='color:#$vcarry;font-weight:bold;'>".$row['name']."</span>";
			else 
				echo "<span style='color:#$vpick;font-weight:bold;'>".$row['name']."</span>";
		 } else {
			if ($row['carry_over'])
				echo "<span style='color:#$icarry;font-weight:bold;'>".$row['name']."</span>";
			else 
				echo "<span style='color:#$ipick;font-weight:bold;'>".$row['name']."</span>";
		}	
	}	echo "</td></tr>";
		$trclass=$trclass=='oddtablerow'?'eventablerow':'oddtablerow';
		
}
echo "<tr class='$trclass'><td colspan='4'><span style='color:#$ipick;font-weight:bold;'>Invalid FLEX pick!!</span><br />";
echo "<span style='color:#$vcarry;font-weight:bold;'>Carry over pick from last week - Valid</span><br />";
echo "<span style='color:#$icarry;font-weight:bold;'>Carry over from last week but invalid</span><br />";
echo "<span style='color:#$vpick;font-weight:bold;'>Valid Pick</span>";
echo "</table></div>";
echo "</form>";
//fclose($log);
$query="DELETE FROM roster_lock WHERE team_id='$team_id'";
mysql_query($query) or die(mysql_error());

mysql_close();
//echo "</div>";
echo "</body></html>";
