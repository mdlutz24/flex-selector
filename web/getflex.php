<?php

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

if (!isset($_GET['week']) || !isset($_GET['franchise'])) {
  die();
}

$week = (int) $_GET['week'];
$franchise = str_pad((string) (int) $_GET['franchise'], 4, '0', STR_PAD_LEFT);
$player = getDB()->query("SELECT `player_id` from `flex` where week=$week AND team_id = '$franchise'")->fetch_row()[0];
echo str_pad($player, 5, '0', STR_PAD_LEFT);
