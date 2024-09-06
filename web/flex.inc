<?php
/**
 * @file
 * ${fileDescription}
 */

$FLEX_POSITIONS=array('QB', 'RB', 'WR', 'TE', 'PK');
$NUM_STARTERS=array('QB' => 2, 'RB'=> 3, 'WR'=> 3, 'TE'=> 2, 'PK' => 2);
$max_valid= array('QB' => 1, 'RB' => 2, 'WR' => 2, 'TE' => 1, 'PK' => 1);
$ipick='3b0a06';
$vpick='377336';
$icarry='822822';
$vcarry='084007';

define('YEAR', 2024);
define('L_ID', 46324);
define('SEASON_START', '2024-09-03 04:00:00');
define('HOST', 'www43.myfantasyleague.com/');
define('GENERAL_HOST', 'api.myfantasyleague.com');
define('PROTOCOL', 'https://');
define('SEASON_LENGTH', 18);

function getData($type, $options = [], $command = 'export') {
  $args = '';
  foreach($options as $key => $value) {
    $value = urlencode($value);
    $args .= "&$key=$value";
  }

  $url = PROTOCOL . (isset($options['L']) ? HOST : GENERAL_HOST) . '/' . YEAR . '/' . $command . "?TYPE=" . $type . $args;
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



function getPos($id){
  $db = getDB();
  $query="SELECT position FROM players WHERE id='$id'";
  $result=$db->query($query);
  $row=$result->fetch_assoc();
  return $row['position'];
}

function checkValid($team_id, $player_id, $flexweek=NULL){
  global $NUM_STARTERS;
  global $week;
  $flexweek = $flexweek?$flexweek:$week;
  $db = getDB();
  $pos=getPos($player_id);
  $query="SELECT count(DISTINCT(player_id)) as total FROM rosters, players WHERE team_id='$team_id' AND week='$flexweek' AND players.id=player_id AND position='$pos'";
  $result=$db->query($query);
  $row=$result->fetch_assoc();

  return ($NUM_STARTERS[$pos]==$row['total']);

}


function loadRosters($week, $weeklyResults){
  global $currentDate;
  getDB()->query("DELETE FROM rosters WHERE week='$week'");
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
          $result=getDB()->query($query) ;
          if($result->num_rows == 0){
            $query="INSERT INTO rosters (team_id, player_id, week) VALUES ('$id', '$starter', '$week')";
            getDB()->query($query) ;
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
        $result=getDB()->query($query) ;
        if($result->num_rows==0){
          $query="INSERT INTO rosters (team_id, player_id, week) VALUES ('$id', '$starter', '$week')";
          getDB()->query($query) ;
        }
      }
    }
  }

}
