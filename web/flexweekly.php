#!/usr/bin/php
<?php
include_once('flex.inc');

$db = getDB();

extract($_GET);
$currentDate=time();
$startDate=strtotime(SEASON_START);
$week=ceil(($currentDate-$startDate)/604800);
$week=$week<1?1:$week;
$week=$week>21?21:$week;
echo " Week = $week !!!!!!!!!!!";
//Reload Teams
loadTeams();

//Reload Players
loadPlayers();

//reload Schedule
$schedule = getData('nflSchedule', ['L' => L_ID, 'W' => $week]);
loadSchedule($week, $schedule);

// Load season rosters and scores
$db->query("TRUNCATE TABLE scores");
$db->query("TRUNCATE TABLE rosters");
/*$url="http://football.myfantasyleague.com/{$year}/export?TYPE=weeklyResults&L={$lid}&W=YTD";
echo $url."\n";
if (!($allweeklyresults=simplexml_load_file($url))) echo "import error\n";*/
//var_dump($allweeklyresults);

$allweeklyresults = getData('weeklyResults', ['L'=> L_ID, 'W' => 'YTD']);
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
	$processed_franchises = [];
	foreach ($matchups as $matchup){
 		$franchises =$matchup->franchise;
		foreach ($franchises as $franchise){
		    var_dump($processed_franchises);
		    echo "Processing {$franchise['id']}";
		    if (in_array($franchise['id'], $processed_franchises)) {
		        echo "Skipping a second run for {$franchise['id']}";
		        continue;
            }
            $processed_franchises[] = (string) $franchise['id'];
		//	echo "in franchises\n";
		//	echo "   Entering data for team {$franchise['id']}\n";
			$team_id=$franchise['id'];
			$query="SELECT player_id FROM flex WHERE team_id='$team_id' && week='$week'";
			echo "      $query\n";
			$result=$db->query($query);

			if (!$row=$result->fetch_assoc()){
	//			 echo "No Flex Found";

				$player_id=NULL;			
			} else extract($row);
			$players=$franchise->player;
			foreach ($players as $player){
				if ($player['status']=='starter'){
					$pos=$player['id']==$player_id?'FLEX':getPos($player['id']);
					$id=$player['id'];
					$score=$player['score'];
					echo "  entering rosters, team, player, week $team_id, $id, $week\n";
					$query="INSERT INTO rosters (team_id, player_id, week) VALUES ('$team_id', '$id', '$week')";
					$db->query($query);
            //      echo "  entering scores, team, player, pos, week, score $team_id, $id, $pos, $week, $score\n";
					$query="INSERT INTO scores (team_id, player_id, position, week, score)
									VALUES ('$team_id', '$id', '$pos', '$week', '$score')";
					$db->query($query);
		//			echo "Executing '$query'\n";
		//			echo "     added player $id as $pos\n";
				}
			}

				foreach($FLEX_POSITIONS as $pos){
					$query= "SELECT COUNT(DISTINCT(player_id)) num FROM scores WHERE team_id='$team_id' AND week=$week and position='$pos'";
					$row=$db->query($query)->fetch_object();//mysql_fetch_object(mysql_query($query));
					echo "Found {$row->num} $pos\n";
					if ($row->num>$max_valid[$pos]){
						$db->query("DELETE FROM scores WHERE team_id='$team_id' AND week=$week and position='$pos'");
			//			echo "      Removed $pos from scores due to invalid flex \n";
					}
				}			
	
		}
	}
	$franchises=$weeklyResult->franchise;
		foreach ($franchises as $franchise){
          if (in_array($franchise['id'], $processed_franchises)) {
            continue;
          }
          $processed_franchises[] = (string) $franchise['id'];
//			echo "in franchises\n";
			echo "   Entering data for team {$franchise['id']}\n";
			$team_id=$franchise['id'];
			$query="SELECT player_id FROM flex WHERE team_id='$team_id' && week='$week'";
			echo "      $query\n";
			$result=$db->query($query);
		
			if (!$row=$result->fetch_assoc()){
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
					$db->query($query);
					$query="INSERT INTO scores (team_id, player_id, position, week, score)
									VALUES ('$team_id', '$id', '$pos', '$week', '$score')";
					$db->query($query);
					echo "     added player $id as $pos\n"; 
				}
			}
		
				foreach($FLEX_POSITIONS as $pos){
					$query= "SELECT COUNT(DISTINCT(player_id)) num FROM scores WHERE team_id='$team_id' AND week=$week and position='$pos'";
					echo $query;
                                        $row=$db->query($query)->fetch_object();
					echo "Found {$row->num} $pos\n";
					if ($row->num>$max_valid[$pos]){
						$db->query("DELETE FROM scores WHERE team_id='$team_id' AND week=$week and position='$pos'");
						echo "      Removed $pos from scores due to invalid flex \n";				
					}
				}			
			
		}
	


}

				




