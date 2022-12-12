<?php
include('flex.inc');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!--Copyright 2007 Fossit Solutions. All rights reserved-->
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
	<title>
		My Football Picks
	</title>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,400i,700|Roboto+Condensed:400,700|Roboto:400,400i,700" rel="stylesheet"><link rel="stylesheet" id="default" href="https://www71.myfantasyleague.com/skins17/MFLBaseCSS.css" type="text/css">
    <link rel="stylesheet" id="skin" href="https://www71.myfantasyleague.com/skins17/Cheeseheads/Cheeseheads.css" type="text/css">
    <link rel="stylesheet" id="responsive" href="https://www71.myfantasyleague.com/skins17/Cheeseheads/responsive.css" type="text/css">
    <link rel="stylesheet" id="custom" href="https://mfl.hazelknot.com/css/style.css" type="text/css">
	<style type="text/css">
		@import "iframe.css";
		.sort{cursor:pointer;}
		.sort:hover{cursor:pointer;text-decoration:underline;}
		body{background:transparent;}
                
                table{width:100%;}
	</style>

</head>
<body><?php

function posClass($pos) {
    return str_replace(" ", '', strtolower($pos));
}
$db = getDB();

$sort='total';
extract($_GET);
if (!isset($week) or $week==''){
	$currentDate=time();
	$startDate=strtotime(SEASON_START);
	$week=ceil(($currentDate-$startDate)/604800);
	$week=$week<1?1:$week;
	$week=$week>22?22:$week;
}
echo "<form name='sortform' id='sortform' method='get' action='pos_totals.php'>";
echo "<input type='hidden' name='sort' value='' id='sort' />";
echo "</form>";
$positions=array('QB','RB','WR','TE','FLEX','PK','Def', 'Lost Points');
echo "<table class='homepagemodule report' align='center'><span><caption>Positional Scores adjusted for FLEX</caption></span><tbody>";
echo "<tr><th class='sort' onclick=\"document.getElementById('sort').value='team';document.getElementById('sortform').submit()\" >Team</th>";
foreach ($positions as $position)
	echo "<th class='sort " . posClass($position) . "' onclick=\"document.getElementById('sort').value='$position';document.getElementById('sortform').submit()\" >$position</th>";
echo "<th class='sort' onclick=\"document.getElementById('sort').value='total';document.getElementById('sortform').submit()\" >Total</th></tr>";
if ($sort=='total')
	$query="SELECT name, id as tid FROM teams ORDER BY (SELECT SUM(score) FROM scores WHERE scores.team_id=tid) DESC";
else if ($sort=='team')
	$query="SELECT name, id as tid FROM teams ORDER BY name ASC";
else
	$query="SELECT name, id as tid FROM teams ORDER BY (SELECT SUM(score) FROM scores WHERE scores.team_id=tid && position='$sort') DESC";
//echo $query;
$result=$db->query($query);
$trclass='oddtablerow';

while ($row=$result->fetch_assoc()){
	extract($row);	
	echo "<tr class='$trclass'><td>$name</td>";
	foreach($positions as $position){
		$query="SELECT SUM(score) as pos_score FROM scores WHERE team_id='$tid' AND position='$position'";
		$scores=$db->query($query);
		$score=$scores->fetch_assoc();
		extract($score);
		echo "<td class='" . posClass($position) . "'>$pos_score</td>";
	}
	$query="SELECT SUM(score) as pos_score FROM scores WHERE team_id='$tid'";
    $scores=$db->query($query);
    $score=$scores->fetch_assoc();
	extract($score);
	echo "<td class='total'>$pos_score</td></tr>";
	$trclass=$trclass=='oddtablerow'?'eventablerow':'oddtablerow';

}
echo "</tbody></table>";
$query="SELECT name, id as tid FROM teams ORDER BY name ASC";
$col=1;
echo "<table width=100%>";
$teams=$db->query($query);
while ($team=$teams->fetch_assoc()){
	extract($team);
	if ($col==1) echo "<tr>";
	echo "<td>";
	echo "<table class='homepagemodule report' align='center'><span><caption>$name</caption></span><tbody>";
	echo "<tr><th></th>";
	for ($week=1;$week<19;$week++)
			echo "<th>$week</th>";
	echo "<th>TOT</th></tr>";
	$trclass='oddtablerow';
	foreach($positions as $position){
		echo "<tr class='$trclass " . posClass($position) . "'><td>$position</td>";
		for ($week=1;$week<19;$week++){
			$query="SELECT SUM(score) as pos_score FROM scores WHERE team_id='$tid' AND position='$position' AND week='$week'";
			$result=$db->query($query);
			$row=$result->fetch_assoc();
			extract($row);
			//if ($pos_score==0) echo "<td> - </td>";
			//else 
				echo "<td>$pos_score</td>";
		}
		$query="SELECT SUM(score) as pos_score FROM scores WHERE team_id='$tid' AND position='$position'";
        $result=$db->query($query);
        $row=$result->fetch_assoc();
		extract($row);
		echo "<td>$pos_score</td></tr>";
		$trclass=$trclass=='oddtablerow'?'eventablerow':'oddtablerow';
	}
	echo "<tr class='$trclass postotal'><td>TOT</td>";
	for ($week=1;$week<19;$week++){
		$query="SELECT SUM(score) as pos_score FROM scores WHERE team_id='$tid' AND week='$week'";
        $result=$db->query($query);
        $row=$result->fetch_assoc();
		extract($row);
		//if ($pos_score==0) echo "<td> - </td>";
		//else 
		echo "<td>$pos_score</td>";
	}
	$query="SELECT SUM(score) as pos_score FROM scores WHERE team_id='$tid'";
    $result=$db->query($query);
    $row=$result->fetch_assoc();
	extract($row);
	echo "<td>$pos_score</td></tr></table>";
	echo "</td>";
	if ($col==1) echo "</tr>";
	//$col=$col==1?2:1;
}		
echo "</table>";





echo "</body></html>";

