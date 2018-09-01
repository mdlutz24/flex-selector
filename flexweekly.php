#!/usr/bin/php
<?
$FLEX_POSITIONS=array('RB', 'WR', 'TE');
$NUM_STARTERS=array('RB'=> 3, 'WR'=> 3, 'TE'=> 2);
$max_valid= array('RB' => 2, 'WR' => 2, 'TE' => 1);
$year=2013;
$lid=14936;
$host='localhost';
$user='mfl';
$pass='AyOkUPmRwM3yvoZs';
$connect = mysql_connect($host, $user, $pass) or die("SQL error");
mysql_select_db("mfl") or die (mysql_error());
extract($_GET);
$dbStartDate="2013-09-03 03:59:59";
$currentDate=time();
$startDate=strtotime($dbStartDate);
$week=ceil(($currentDate-$startDate)/604800);
$week=$week<1?1:$week;
$week=$week>21?21:$week;
echo " Week = $week !!!!!!!!!!!";
//Reload Teams
mysql_query("TRUNCATE TABLE teams");
$league=simplexml_load_file("http://football2.myfantasyleague.com/$year/export?TYPE=league&L=$lid");
$franchises=$league->franchises[0]->franchise;
foreach ($franchises as $franchise) {
	$id=$franchise['id'];
	$name=mysql_real_escape_string($franchise['name']);
	$query="INSERT INTO teams (id, name) VALUES ('$id', '$name')";
	mysql_query($query);
}

//Reload Players
mysql_query("TRUNCATE TABLE players");
$players=simplexml_load_file("http://football.myfantasyleague.com/$year/export?TYPE=players");
$players=$players->player;
foreach($players as $player){
	foreach ($player->attributes() as $key => $value)
		$arrays[$key]=mysql_real_escape_string($value);
	extract($arrays);
	$query="INSERT INTO players (id, name, position, team) 
	VALUES ('$id', '$name', '$position', '$team')";
//	echo "Adding $id, $name, $position, $team<br />";
	mysql_query($query) or die(mysql_error());
}

//reload Schedule
mysql_query("TRUNCATE TABLE schedule");
$schedule=simplexml_load_file("http://football.myfantasyleague.com/$year/export?TYPE=nflSchedule&L=$lid&W=$week");
$matchups=$schedule->matchup;
foreach($matchups as $matchup) {		
	$date=date('Y-m-d H:i:s', (int)$matchup['kickoff']);
	$teams=$matchup->team;
	foreach($teams as $team){
		$id=$team['id'];
		$query="INSERT INTO schedule (team_id, date) VALUES ('$id', '$date')";
  	mysql_query($query) or die(mysql_error());
		}	
	}

// Load season rosters and scores
mysql_query("TRUNCATE TABLE scores");
mysql_query("TRUNCATE TABLE rosters");
$url="http://football.myfantasyleague.com/{$year}/export?TYPE=weeklyResults&L={$lid}&W=YTD";
echo $url."\n";
if (!($allweeklyresults=simplexml_load_file($url))) echo "import error\n";
//var_dump($allweeklyresults);
if ($week == 1){
$weeklyResults = array($allweeklyresults);
} else {
$weeklyResults=$allweeklyresults->weeklyResults;
}

foreach ($weeklyResults as $weeklyResult){
	echo "in results\n";
	$week=$weeklyResult['week'];
	echo "Starting Week $week\n\n";
	$matchups=$weeklyResult->matchup;
	$franchises=array();
	foreach ($matchups as $matchup){
 		$franchises =$matchup->franchise;
		foreach ($franchises as $franchise){
			echo "in franchises\n";
			echo "   Entering data for team {$franchise['id']}\n";
			$team_id=$franchise['id'];
			$query="SELECT player_id FROM flex WHERE team_id='$team_id' && week='$week'";
			echo "      $query\n";
			$result=mysql_query($query) or die(mysql_error());

			if (!$row=mysql_fetch_array($result)){
				 echo "No Flex Found";

				$player_id=NULL;			
			} else extract($row);
			$players=$franchise->player;
			foreach ($players as $player){
				if ($player['status']=='starter'){
					$pos=$player['id']==$player_id?'FLEX':getPos($player['id']);
					$id=$player['id'];
					$score=$player['score'];
					$query="INSERT INTO rosters (team_id, player_id, week) VALUES ('$team_id', '$id', '$week')";
					mysql_query($query) or die(mysql_error());				
					$query="INSERT INTO scores (team_id, player_id, position, week, score)
									VALUES ('$team_id', '$id', '$pos', '$week', '$score')";
					mysql_query($query) or die(mysql_error());
					echo "     added player $id as $pos\n"; 
				}
			}

				foreach($FLEX_POSITIONS as $pos){
					$query= "SELECT COUNT(DISTINCT(player_id)) num FROM scores WHERE team_id='$team_id' AND week=$week and position='$pos'";
					$row=mysql_fetch_object(mysql_query($query));
					echo "Found {$row->num} $pos\n";
					if ($row->num>$max_valid[$pos]){
						mysql_query("DELETE FROM scores WHERE team_id='$team_id' AND week=$week and position='$pos'");
						echo "      Removed $pos from scores due to invalid flex \n";				
					}
				}			
	
		}
	}
	$franchises=$weeklyResult->franchise;
		foreach ($franchises as $franchise){
//			echo "in franchises\n";
			echo "   Entering data for team {$franchise['id']}\n";
			$team_id=$franchise['id'];
			$query="SELECT player_id FROM flex WHERE team_id='$team_id' && week='$week'";
			echo "      $query\n";
			$result=mysql_query($query) or die(mysql_error());
		
			if (!$row=mysql_fetch_array($result)){
				 echo "No Flex Found";
			
				$player_id=NULL;			
			} else extract($row);
			$players=$franchise->player;
			foreach ($players as $player){
				if ($player['status']=='starter'){
					$pos=$player['id']==$player_id?'FLEX':getPos($player['id']);
					$id=$player['id'];
					$score=$player['score'];
					$query="INSERT INTO rosters (team_id, player_id, week) VALUES ('$team_id', '$id', '$week')";
					mysql_query($query) or die(mysql_error());				
					$query="INSERT INTO scores (team_id, player_id, position, week, score)
									VALUES ('$team_id', '$id', '$pos', '$week', '$score')";
					mysql_query($query) or die(mysql_error());
					echo "     added player $id as $pos\n"; 
				}
			}
		
				foreach($FLEX_POSITIONS as $pos){
					$query= "SELECT COUNT(DISTINCT(player_id)) num FROM scores WHERE team_id='$team_id' AND week=$week and position='$pos'";
					echo $query;
                                        $row=mysql_fetch_object(mysql_query($query));
					echo "Found {$row->num} $pos\n";
					if ($row->num>$max_valid[$pos]){
						mysql_query("DELETE FROM scores WHERE team_id='$team_id' AND week=$week and position='$pos'");
						echo "      Removed $pos from scores due to invalid flex \n";				
					}
				}			
			
		}
	


}

				



function getPos($id){
	$query="SELECT position FROM players WHERE id='$id'";
	$result=mysql_query($query) or die(mysql_error());
	$row=mysql_fetch_array($result);
	return $row['position'];
}

function checkValid($team_id, $player_id){
	global $FLEX_POSITIONS;
	global $NUM_STARTERS;
	global $week;	
	$pos=getPos($player_id);
	$query="SELECT count(DISTINCT(player_id)) as total FROM rosters, players WHERE team_id='$team_id' AND week='$week' AND players.id=player_id AND position='$pos'";
	$result=mysql_query($query) or die(mysql_error());
	$row=mysql_fetch_array($result);
	
	return ($NUM_STARTERS[$pos]==$row['total']);
	
		
}


?>


