#!/usr/bin/php
<?php
$FLEX_POSITIONS=array('RB', 'WR', 'TE');
$NUM_STARTERS=array('RB'=> 3, 'WR'=> 3, 'TE'=> 2);
$max_valid= array('RB' => 2, 'WR' => 2, 'TE' => 1);
define('YEAR', 2019);
define('L_ID', 46324);
define('SEASON_START', '2019-09-03 04:00:00');
define('HOST', 'www71.myfantasyleague.com/');
define('PROTOCOL', 'https://');

function getData($type, $options = [], $command = 'export') {
  $args = '';
  foreach($options as $key => $value) {
    $value = urlencode($value);
    $args .= "&$key=$value";
  }

  $url = PROTOCOL . HOST . '/' . YEAR . '/' . $command . "?TYPE=" . $type . $args;
  return simplexml_load_file($url);
}

function getDB() {
  static $db = FALSE;
  if (!$db) {
    $host='localhost';
    $user='mfl';
    $pass='AyOkUPmRwM3yvoZs';
    $db = new mysqli($host, $user, $pass, 'mfl');
  }
  return $db;
}

function loadTeams(){
  $db = getDB();
  $db->query("TRUNCATE table teams");
  $league=getData('league', ['L' => L_ID]);
  $franchises=$league->franchises[0]->franchise;
  foreach ($franchises as $franchise) {
    $id=$franchise['id'];
    $name=strip_tags(getDB()->escape_string($franchise['name']));

    if ($db->query("SELECT * FROM teams WHERE id=$id")->num_rows) {
      $db->query("UPDATE teams SET name='$name' WHERE id=$id");
    } else {
      $db->query("INSERT INTO teams (id, name) VALUES ('$id', '$name')");
    }
  }
}

function loadPlayers(){
  getDB()->query("TRUNCATE TABLE players");
  global $currentDate;
  // $plog=fopen("log/".date("Y-m-d_H:i:s", $currentDate)."/players", "a");

  $players=getData('players');
  $players=$players->player;
  $arrays = [];
  foreach($players as $player){
    foreach ($player->attributes() as $key => $value)
      $arrays[$key]=getDB()->escape_string($value);

    extract($arrays);
    $query="INSERT INTO players (id, name, position, team) VALUES ('$id', '$name', '$position', '$team')";
    //     echo "Adding $id, $name, $position, $team<br />";
    getDB()->query($query);
  }
  // fclose($plog);
}


function loadSchedule($week, $schedule){
  getDB()->query("DELETE FROM schedule WHERE week='$week'");
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
      //fwrite($slog,"$query\n");
      //           echo $query."<br />";
      getDB()->query($query);
    }
  }
  //   fclose($slog);
}

/*
$year=2013;
$lid=14936;
$host='localhost';
$user='mfl';
$pass='AyOkUPmRwM3yvoZs';
$connect = mysql_connect($host, $user, $pass) or die("SQL error");
mysql_select_db("mfl") or die (mysql_error());
*/
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

				



function getPos($id){
    $db = getDB();
	$query="SELECT position FROM players WHERE id='$id'";
	$result=$db->query($query);
	$row=$result->fetch_assoc();
	return $row['position'];
}

function checkValid($team_id, $player_id){
	global $FLEX_POSITIONS;
	global $NUM_STARTERS;
	global $week;
    $db = getDB();
	$pos=getPos($player_id);
	$query="SELECT count(DISTINCT(player_id)) as total FROM rosters, players WHERE team_id='$team_id' AND week='$week' AND players.id=player_id AND position='$pos'";
  $result=$db->query($query);
  $row=$result->fetch_assoc();
	
	return ($NUM_STARTERS[$pos]==$row['total']);
	
		
}



