<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!--Copyright 2007 Fossit Solutions. All rights reserved-->
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
	<title>
		My Football Picks
	</title>
<link rel="stylesheet" href="http://www9.myfantasyleague.com/fflnetdynamic2013/14936.css" type="text/css"  />
<link rel="stylesheet" href="http://www9.myfantasyleague.com/skins/style_4/4d.css" type="text/css"  />
	<style type="text/css">
		@import "iframe.css";
		.sort{cursor:pointer;}
		.sort:hover{cursor:pointer;text-decoration:underline;}
		body{background:transparent;}
                
                table{width:100%;}
	</style>

</head>
<body><?php
if ($_SERVER['HTTP_HOST']=='dev.myfootballpicks.org') {
	$host='localhost';
	$user='root';
	$pass='kahless';
} else {
	$host='localhost';
	$user='mfl';
	$pass='AyOkUPmRwM3yvoZs';
}

ini_set('display_errors', 1);
error_reporting(E_ALL);

$connect = mysql_connect($host, $user, $pass) or die("SQL error");
mysql_select_db("mfl") or die (mysql_error());
$sort='total';
extract($_GET);
if (!isset($week) or $week==''){
	$dbStartDate="2013-09-03 04:00:00";
	$currentDate=time();
	$startDate=strtotime($dbStartDate);
	$week=ceil(($currentDate-$startDate)/604800);
	$week=$week<1?1:$week;
	$week=$week>21?21:$week;
}
echo "<form name='sortform' id='sortform' method='get' action='pos_totals.php'>";
echo "<input type='hidden' name='sort' value='' id='sort' />";
echo "</form>";
$positions=array('QB','RB','WR','TE','FLEX','PK','Def');
echo "<table class='homepagemodule report' align='center'><span><caption>Positional Scores adjusted for FLEX</caption></span><tbody>";
echo "<tr><th class='sort' onclick=\"document.getElementById('sort').value='team';document.getElementById('sortform').submit()\" >Team</th>";
foreach ($positions as $position)
	echo "<th class='sort' onclick=\"document.getElementById('sort').value='$position';document.getElementById('sortform').submit()\" >$position</th>";
echo "<th class='sort' onclick=\"document.getElementById('sort').value='total';document.getElementById('sortform').submit()\" >Total</th></tr>";
if ($sort=='total')
	$query="SELECT name, id as tid FROM teams ORDER BY (SELECT SUM(score) FROM scores WHERE scores.team_id=tid) DESC";
else if ($sort=='team')
	$query="SELECT name, id as tid FROM teams ORDER BY name ASC";
else
	$query="SELECT name, id as tid FROM teams ORDER BY (SELECT SUM(score) FROM scores WHERE scores.team_id=tid && position='$sort') DESC";
//echo $query;
$result=mysql_query($query) or die(mysql_error());
$trclass='oddtablerow';

while ($row=mysql_fetch_array($result)){
	extract($row);	
	echo "<tr class='$trclass'><td>$name</td>";
	foreach($positions as $position){
		$query="SELECT SUM(score) as pos_score FROM scores WHERE team_id='$tid' AND position='$position'";
		$scores=mysql_query($query) or die(mysql_error());
		$score=mysql_fetch_array($scores);
		extract($score);
		echo "<td>$pos_score</td>";
	}
	$query="SELECT SUM(score) as pos_score FROM scores WHERE team_id='$tid'";
	$scores=mysql_query($query) or die(mysql_error());
	$score=mysql_fetch_array($scores);
	extract($score);
	echo "<td>$pos_score</td></tr>";
	$trclass=$trclass=='oddtablerow'?'eventablerow':'oddtablerow';

}
echo "</tbody></table>";
$query="SELECT name, id as tid FROM teams ORDER BY name ASC";
$col=1;
echo "<table width=100%>";
$teams=mysql_query($query) or die(mysql_error());
while ($team=mysql_fetch_array($teams)){
	extract($team);
	if ($col==1) echo "<tr>";
	echo "<td width=50%>";
	echo "<table class='homepagemodule report' align='center'><span><caption>$name</caption></span><tbody>";
	echo "<tr><th></th>";
	for ($week=1;$week<17;$week++)
			echo "<th>$week</th>";
	echo "<th>TOT</th></tr>";
	$trclass='oddtablerow';
	foreach($positions as $position){
		echo "<tr class='$trclass'><td>$position</td>";
		for ($week=1;$week<17;$week++){
			$query="SELECT SUM(score) as pos_score FROM scores WHERE team_id='$tid' AND position='$position' AND week='$week'";
			$result=mysql_query($query) or die(mysql_error());
			$row=mysql_fetch_array($result);
			extract($row);
			//if ($pos_score==0) echo "<td> - </td>";
			//else 
				echo "<td>$pos_score</td>";
		}
		$query="SELECT SUM(score) as pos_score FROM scores WHERE team_id='$tid' AND position='$position'";
		$result=mysql_query($query) or die(mysql_error());
		$row=mysql_fetch_array($result);
		extract($row);
		echo "<td>$pos_score</td></tr>";
		$trclass=$trclass=='oddtablerow'?'eventablerow':'oddtablerow';
	}
	echo "<tr class='$trclass'><td>TOT</td>";
	for ($week=1;$week<17;$week++){
		$query="SELECT SUM(score) as pos_score FROM scores WHERE team_id='$tid' AND week='$week'";
		$result=mysql_query($query) or die(mysql_error());
		$row=mysql_fetch_array($result);
		extract($row);
		//if ($pos_score==0) echo "<td> - </td>";
		//else 
		echo "<td>$pos_score</td>";
	}
	$query="SELECT SUM(score) as pos_score FROM scores WHERE team_id='$tid'";
	$result=mysql_query($query) or die(mysql_error());
	$row=mysql_fetch_array($result);
	extract($row);
	echo "<td>$pos_score</td></tr></table>";
	echo "</td>";
	if ($col==2) echo "</tr>";
	$col=$col==1?2:1;
}		
echo "</table>";





echo "</body></html>";

