<?php

/**
 * statstrac - open NFL statistics platform
 *
 */

define('DS', DIRECTORY_SEPARATOR);
define('PATH', dirname(__FILE__) . DS);
define('CACHE', PATH . 'cache' . DS);
define('ASSETS', PATH . 'assets' . DS);
define('EXT', '.php');

require PATH . 'config' . EXT;

if ($_GET["week"]) { $week = $_GET["week"]; }
if ($_GET["year"]) { $year = $_GET["year"]; }
if ($_GET["type"]) { $season_type = $_GET["type"]; }
if ($_GET["sort"]) { $sort = $_GET["sort"]; }

if (!isset($week) && !isset($year) && !isset($season_type) && !isset($sort)) {
	$meta = CACHE . 'meta.json';
	if (file_exists($meta) && filemtime($meta) + 86400 > time()) {
		$metadata = file_get_contents($meta);
		$objmeta = json_decode($metadata, true);
	} else {
		$msg = file_get_contents($postgrest . 'meta');
		$f = fopen($meta, "w+");
		fwrite($f, $msg);
		fclose($f);
		$objmeta = json_decode($msg, true);
	}
	$week = $objmeta[0]["week"];
	$year = $objmeta[0]["season_year"];
	$season_type = $objmeta[0]["season_type"];
	$last_update = $objmeta[0]["last_roster_download"];
	$date_u = strtotime($last_update);
	$date_u = "Last updated ".date('D\, M j Y', $date_u);
	$sort = '&order=finished.asc,start_time.asc,home_score.desc,away_score.desc';
	$sortdropdown = '<li><a href="?sort=date&type='.$season_type.'&year='.$year.'&week='.$week.'">Start Date</a></li>
		<li class="active"><a href="?sort=completed&type='.$season_type.'&year='.$year.'&week='.$week.'">Completed</a></li>
		<li><a href="?sort=score&type='.$season_type.'&year='.$year.'&week='.$week.'">Score</a></li>';
} else {
	$meta = CACHE . 'meta.json';
	if (file_exists($meta) && filemtime($meta) + 86400 > time()) {
		$metadata = file_get_contents($meta);
		$objmeta = json_decode($metadata, true);
	} else {
		$msg = file_get_contents($postgrest . 'meta');
		$f = fopen($meta, "w+");
		fwrite($f, $msg);
		fclose($f);
		$objmeta = json_decode($msg, true);
	}
	$last_update = $objmeta[0]["last_roster_download"];
	$date_u = strtotime($last_update);
	$date_u = "Last updated ".date('D\, M j Y', $date_u);
	if (!isset($week)) {
		$week = $objmeta[0]["week"];
	}
	if (!isset($year)) {
		$year = $objmeta[0]["season_year"];
	}
	if (!isset($season_type)) {
		$season_type = $objmeta[0]["season_type"];
	}
	if ($sort === "date") {
		$sort = '&order=start_time.asc';
		$sortdropdown = '<li class="active"><a href="?sort=date&type='.$season_type.'&year='.$year.'&week='.$week.'">Start Date</a></li>
		<li><a href="?sort=completed&type='.$season_type.'&year='.$year.'&week='.$week.'">Completed</a></li>
		<li><a href="?sort=score&type='.$season_type.'&year='.$year.'&week='.$week.'">Score</a></li>';
	} else if ($sort === "completed") {
		$sort = '&order=finished.asc,start_time.asc,home_score.desc,away_score.desc';
		$sortdropdown = '<li><a href="?sort=date&type='.$season_type.'&year='.$year.'&week='.$week.'">Start Date</a></li>
		<li class="active"><a href="?sort=completed&type='.$season_type.'&year='.$year.'&week='.$week.'">Completed</a></li>
		<li><a href="?sort=score&type='.$season_type.'&year='.$year.'&week='.$week.'">Most Score</a></li>';
	} else if ($sort === "score") {
		$sort = '&order=home_score.desc,away_score.desc';
		$sortdropdown = '<li><a href="?sort=date&type='.$season_type.'&year='.$year.'&week='.$week.'">Start Date</a></li>
		<li><a href="?sort=completed&type='.$season_type.'&year='.$year.'&week='.$week.'">Completed Games</a></li>
		<li class="active"><a href="?sort=score&type='.$season_type.'&year='.$year.'&week='.$week.'">Score</a></li>';
	}
	if (!isset($sort)) {
		$sort = '&order=finished.asc,start_time.asc,home_score.desc,away_score.desc';
		$sortdropdown = '<li><a href="?sort=date&type='.$season_type.'&year='.$year.'&week='.$week.'">Start Date</a></li>
		<li class="active"><a href="?sort=completed&type='.$season_type.'&year='.$year.'&week='.$week.'">Completed</a></li>
		<li><a href="?sort=score&type='.$season_type.'&year='.$year.'&week='.$week.'">Score</a></li>';
	}
}

// NAV FUNCTIONS - WEEKS
$grab_weeks = CACHE . 'game/game?season_type=eq.'.$season_type.'&season_year=eq.'.$year.'&select=week&order=week.asc.json';
if (file_exists($grab_weeks)) {
	$grabweeksdata = file_get_contents($grab_weeks);
	$grabweeksobj = json_decode($grabweeksdata, true);
} else {
	$msg = file_get_contents($postgrest . 'game?season_type=eq.'.$season_type.'&season_year=eq.'.$year.'&select=week&order=week.asc');
	$f = fopen($grab_weeks, "w+");
	fwrite($f, $msg);
	fclose($f);
	$grabweeksobj = json_decode($msg, true);
}
foreach ($grabweeksobj as $key => $val) {
	$totalweeks = $val["week"];
}
for ($i = 1; $i < $totalweeks+1; $i++) {
	if ($i == $week) {
		$weeksdropdown .= '<li class="active"><a href="/?type='.$season_type.'&year='.$year.'&week='.$i.'">Week '.$i.'</a></li>';
	} else {
		$weeksdropdown .= '<li><a href="/?type='.$season_type.'&year='.$year.'&week='.$i.'">Week '.$i.'</a></li>';
	}
}

// NAV FUNCTIONS - YEARS
$startyear = 1970; # this specific install has data from 1970
for ($i = $startyear; $i < $currentyear + 1; $i++) {
	if ($i == $year) {
		$yearsdropdown .= '<li class="active"><a href="/?type='.$season_type.'&year='.$i.'">'.$i.'</a></li>';
	} else {
		$yearsdropdown .= '<li><a href="/?type='.$season_type.'&year='.$i.'">'.$i.'</a></li>';
	}
}

// NAV FUNCTIONS - SEASON TYPES
$grab_season_types = CACHE . 'game/game?season_year=eq.'.$year.'&select=season_type&order=season_type.asc.json';
if (file_exists($grab_season_types) && filemtime($grab_season_types) + 604800 > time()) {
	$grabseasontypesdata = file_get_contents($grab_season_types);
	$grabseasontypeobj = json_decode($grabseasontypesdata, true);
} else {
	$msg = file_get_contents($postgrest . 'game?season_year=eq.'.$year.'&select=season_type&order=season_type.asc');
	$f = fopen($grab_season_types, "w+");
	fwrite($f, $msg);
	fclose($f);
	$grabseasontypeobj = json_decode($msg, true);
}
$i = 0;
foreach ($grabseasontypeobj as $key => $val) {
	$totalseasontypes[$i] = $val["season_type"];
	$i++;
}
$totalseasontypes = array_unique($totalseasontypes);
foreach ($totalseasontypes as $key => $val) {
	if ($val == $season_type) {
		$seasontypedropdown .= '<li class="active"><a href="/?type='.$val.'&year='.$year.'&week='.$week.'">'.$val.'</a></li>';
	} else {
		$seasontypedropdown .= '<li><a href="/?type='.$val.'&year='.$year.'&week='.$week.'">'.$val.'</a></li>';
	}
}
$i = 0;

// NAV FUNCTIONS - TEAMS
$grab_teams = CACHE . 'team/team?order=city.asc.json';
if (file_exists($grab_teams)) {
	$grabteamsdata = file_get_contents($grab_teams);
	$grabteamsobj = json_decode($grabteamsdata, true);
} else {
	$msg = file_get_contents($postgrest . 'team?order=city.asc');
	$f = fopen($grab_teams, "w+");
	fwrite($f, $msg);
	fclose($f);
	$grabteamsobj = json_decode($msg, true);
}
foreach ($grabteamsobj as $key => $val) {
	if ($val["team_id"] !== "UNK" && $val["name"] !== "UNK" && $val["city"] !== "UNK" && $val["team_id"] !== "BOS" && $val["team_id"] !== "LA" && $val["team_id"] !== "PHO" && $val["team_id"] !== "RAI" && $val["team_id"] !== "RAM") {
		if ($val["team_id"] === "NE" || $val["team_id"] === "NYJ" || $val["team_id"] === "MIA" || $val["team_id"] === "BUF") {
			$AFCE[$t] = $val["team_id"];
		} else if ($val["team_id"] === "CIN" || $val["team_id"] === "CLE" || $val["team_id"] === "PIT" || $val["team_id"] === "BAL") {
			$AFCN[$t] = $val["team_id"];
		} else if ($val["team_id"] === "JAC" || $val["team_id"] === "TEN" || $val["team_id"] === "IND" || $val["team_id"] === "HOU") {
			$AFCS[$t] = $val["team_id"];
		} else if ($val["team_id"] === "DEN" || $val["team_id"] === "OAK" || $val["team_id"] === "SD" || $val["team_id"] === "KC") {
			$AFCW[$t] = $val["team_id"];
		} else if ($val["team_id"] === "DAL" || $val["team_id"] === "WAS" || $val["team_id"] === "NYG" || $val["team_id"] === "PHI") {
			$NFCE[$t] = $val["team_id"];
		} else if ($val["team_id"] === "GB" || $val["team_id"] === "MIN" || $val["team_id"] === "DET" || $val["team_id"] === "CHI") {
			$NFCN[$t] = $val["team_id"];
		} else if ($val["team_id"] === "ATL" || $val["team_id"] === "CAR" || $val["team_id"] === "TB" || $val["team_id"] === "NO") {
			$NFCS[$t] = $val["team_id"];
		} else if ($val["team_id"] === "ARI" || $val["team_id"] === "STL" || $val["team_id"] === "SF" || $val["team_id"] === "SEA") {
			$NFCW[$t] = $val["team_id"];
		}
		if ($val["team_id"] === "NE") {
			$teamcolor["".$val["team_id"].""] = '#9A9A9A';
		} if ($val["team_id"] === "NYJ") {
			$teamcolor["".$val["team_id"].""] = '#2A433A';
		} if ($val["team_id"] === "MIA") {
			$teamcolor["".$val["team_id"].""] = '#10B6B9';
		} if ($val["team_id"] === "BUF") {
			$teamcolor["".$val["team_id"].""] = '#005496';
		} if ($val["team_id"] === "CIN") { 
			$teamcolor["".$val["team_id"].""] = '#F04E23';
		} if ($val["team_id"] === "CLE") { 
			$teamcolor["".$val["team_id"].""] = '#F26522';
		} if ($val["team_id"] === "PIT") { 
			$teamcolor["".$val["team_id"].""] = '#f1c817';
		} if ($val["team_id"] === "BAL") {
			$teamcolor["".$val["team_id"].""] = '#5C58DA';
		} if ($val["team_id"] === "JAC") { 
			$teamcolor["".$val["team_id"].""] = '#008DA5';
		} if ($val["team_id"] === "TEN") { 
			$teamcolor["".$val["team_id"].""] = '#4495D1';
		} if ($val["team_id"] === "IND") { 
			$teamcolor["".$val["team_id"].""] = '#00427E';
		} if ($val["team_id"] === "HOU") {
			$teamcolor["".$val["team_id"].""] = '#BF1616';
		} if ($val["team_id"] === "DEN") { 
			$teamcolor["".$val["team_id"].""] = '#2C7DDE';
		} if ($val["team_id"] === "OAK") { 
			$teamcolor["".$val["team_id"].""] = '#eee';
		} if ($val["team_id"] === "SD") { 
			$teamcolor["".$val["team_id"].""] = '#FFC500';
		} if ($val["team_id"] === "KC") {
			$teamcolor["".$val["team_id"].""] = '#C9243F';
		} if ($val["team_id"] === "DAL") { 
			$teamcolor["".$val["team_id"].""] = '#255A98';
		} if ($val["team_id"] === "WAS") { 
			$teamcolor["".$val["team_id"].""] = '#96113C';
		} if ($val["team_id"] === "NYG") {
			$teamcolor["".$val["team_id"].""] = '#0053C5';
		} if ($val["team_id"] === "PHI") {
			$teamcolor["".$val["team_id"].""] = '#00727B';
		} if ($val["team_id"] === "GB") { 
			$teamcolor["".$val["team_id"].""] = '#22926A';
		} if ($val["team_id"] === "MIN") { 
			$teamcolor["".$val["team_id"].""] = '#5E49A9';
		} if ($val["team_id"] === "DET") { 
			$teamcolor["".$val["team_id"].""] = '#006DB0';
		} if ($val["team_id"] === "CHI") {
			$teamcolor["".$val["team_id"].""] = '#FF7300';
		} if ($val["team_id"] === "ATL") { 
			$teamcolor["".$val["team_id"].""] = '#a71931';
		} if ($val["team_id"] === "CAR") { 
			$teamcolor["".$val["team_id"].""] = '#0099D9';
		} if ($val["team_id"] === "TB") { 
			$teamcolor["".$val["team_id"].""] = '#d50a0a';
		} if ($val["team_id"] === "NO") {
			$teamcolor["".$val["team_id"].""] = 'rgb(162,138,102)';
		} if ($val["team_id"] === "ARI") { 
			$teamcolor["".$val["team_id"].""] = '#B1063A';
		} if ($val["team_id"] === "STL") { 
			$teamcolor["".$val["team_id"].""] = '#0E5EBD';
		} if ($val["team_id"] === "SF") { 
			$teamcolor["".$val["team_id"].""] = '#A30D2D';
		} if ($val["team_id"] === "SEA") {
			$teamcolor["".$val["team_id"].""] = '#2D74B3';
		}
		$total_teamname[$t] = $val["name"];
		$total_teamcity[$t] = $val["city"];
		$total_teamid[$t] = $val["team_id"];
		$teamname["".$val["team_id"].""] = $val["name"];
		$teamcity["".$val["team_id"].""] = $val["city"];
		$t++;
	}
}

/*	TEAM SEASON RANK CALCULATIONS
	- If stats year is not equal to the current year, grab from cache
	- Else if stats year is equal to current year and was last updated over an hour ago, write cache file
	- Else write cache file
*/
$grabseasonscore = CACHE . 'game/game?season_type=eq.'.$season_type.'&season_year=eq.'.$year.'&finished=eq.true.json';
if (file_exists($grabseasonscore) && $year != $currentyear) {
	$jsonseasonscore = file_get_contents($grabseasonscore);
	$objseasonscore = json_decode($jsonseasonscore, true);
} else if (file_exists($grabseasonscore) && filemtime($grabseasonscore) + 3600 > time()) {
	$jsonseasonscore = file_get_contents($grabseasonscore);
	$objseasonscore = json_decode($jsonseasonscore, true);
} else {
	$msg = file_get_contents($postgrest . 'game?season_type=eq.'.$season_type.'&season_year=eq.'.$year.'&finished=eq.true');
	$f = fopen($grabseasonscore, "w+");
	fwrite($f, $msg);
	fclose($f);
	$objseasonscore = json_decode($msg, true);
}
foreach ($objseasonscore as $key => $val) {
	$finishedgames[$g] = $val["gsis_id"];
	$ht = $val["home_team"];
	$at = $val["away_team"];
	$hts = $val["home_score"];
	$ats = $val["away_score"];
	if ($hts > $ats) {
		$wins["".$ht.""] = $wins["".$ht.""] + 1;
		$losses["".$ht.""] = $losses["".$ht.""] + 0;
		$losses["".$at.""] = $losses["".$at.""] + 1;
		$wins["".$at.""] = $wins["".$at.""] + 0;
		$ties["".$at.""] = $ties["".$at.""] + 0;
		$ties["".$ht.""] = $ties["".$ht.""] + 0;
	} else if ($ats > $hts) {
		$wins["".$at.""] = $wins["".$at.""] + 1;
		$losses["".$at.""] = $losses["".$at.""] + 0;
		$losses["".$ht.""] = $losses["".$ht.""] + 1;
		$wins["".$ht.""] = $wins["".$ht.""] + 0;
		$ties["".$at.""] = $ties["".$at.""] + 0;
		$ties["".$ht.""] = $ties["".$ht.""] + 0;
	} else if ($hts === $ats) {
		$ties["".$at.""] = $ties["".$at.""] + 1;
		$ties["".$ht.""] = $ties["".$ht.""] + 1;
		$wins["".$ht.""] = $wins["".$ht.""] + 0;
		$wins["".$at.""] = $wins["".$at.""] + 0;
		$losses["".$ht.""] = $losses["".$ht.""] + 0;
		$losses["".$at.""] = $losses["".$at.""] + 0;
	}
$g++;
}

for ($z = 0; $z < $t; $z++) {
	$teamnav .= '<li>
					<a href="team.php?team_id='.$total_teamid[$z].'">
					<span class="badge pull-right '.$total_teamid[$z].'colors">'.$wins["".$total_teamid[$z].""].'-'.$losses["".$total_teamid[$z].""].'-'.$ties["".$total_teamid[$z].""].'</span>
					<span>'.$total_teamid[$z].' '.$total_teamname[$z].'</span>
					</a>
				</li>';
}

/*	CURRENT WEEK GAME OBJECTS
	- If stats year is not equal to the current year, grab from cache
	- Else if stats year is equal to current year and was last updated over an hour ago, write cache file
	- Else write cache file
*/
$json_string = CACHE . 'game/game?season_type=eq.'.$season_type.'&season_year=eq.'.$year.'&week=eq.'.$week.$sort.'.json';
if (file_exists($json_string) && $year != $currentyear) {
	$jsondata = file_get_contents($json_string);
	$obj = json_decode($jsondata, true);
} else if (file_exists($json_string) && filemtime($json_string) + 3600 > time()) {
	$jsondata = file_get_contents($json_string);
	$obj = json_decode($jsondata, true);
} else {
	$msg = file_get_contents($postgrest . 'game?season_type=eq.'.$season_type.'&season_year=eq.'.$year.'&week=eq.'.$week.$sort);
	$f = fopen($json_string, "w+");
	fwrite($f, $msg);
	fclose($f);
	$obj = json_decode($msg, true);
}
$i = 0;
foreach ($obj as $key => $val) {
	$day = $val["day_of_week"];
	$date = $val["gsis_id"];
	$date_f = $date."0000";
	$date_f = strtotime($date_f);
	$date_f = date('D\, M j', $date_f);
	$gsis = $val["gamekey"];
	// SCORE TOTAL
	$homescore = $val["home_score"];
	$awayscore = $val["away_score"];
	// HOME SCORE QUARTER
	$homescore_q1 = $val["home_score_q1"];
	$homescore_q2 = $val["home_score_q2"];
	$homescore_q3 = $val["home_score_q3"];
	$homescore_q4 = $val["home_score_q4"];
	$homescore_q5 = $val["home_score_q5"];
	// Away SCORE QUARTER
	$awayscore_q1 = $val["away_score_q1"];
	$awayscore_q2 = $val["away_score_q2"];
	$awayscore_q3 = $val["away_score_q3"];
	$awayscore_q4 = $val["away_score_q4"];
	$awayscore_q5 = $val["away_score_q5"];

	$homecode = $val["home_team"];
	$awaycode = $val["away_team"];
	$finished = $val["finished"];

	if ($finished) {
		$quarter = "F";
		$progress = "100%";
	}

	$hometeam_string = CACHE . 'team/team?team_id=eq.'.$homecode.'.json';
	if (file_exists($hometeam_string)) {
		$jsonhometeam = file_get_contents($hometeam_string);
		$objhometeam = json_decode($jsonhometeam, true);
	} else {
		$msg = file_get_contents($postgrest . 'team?team_id=eq.'.$homecode);
		$f = fopen($hometeam_string, "w+");
		fwrite($f, $msg);
		fclose($f);
		$objhometeam = json_decode($msg, true);
	}

	$awayteam_string = CACHE . 'team/team?team_id=eq.'.$awaycode.'.json';
	if (file_exists($awayteam_string)) {
		$jsonawayteam = file_get_contents($awayteam_string);
		$objawayteam = json_decode($jsonawayteam, true);
	} else {
		$msg = file_get_contents($postgrest . 'team?team_id=eq.'.$awaycode);
		$f = fopen($awayteam_string, "w+");
		fwrite($f, $msg);
		fclose($f);
		$objawayteam = json_decode($msg, true);
	}

	$hometeam = $objhometeam[0]["name"];
	$homecity = $objhometeam[0]["city"];
	$awayteam = $objawayteam[0]["name"];
	$awaycity = $objawayteam[0]["city"];

	// Don't calculate detailed game stats for games prior to 2009
	if ($year >= 2009) {

		/*	INDIVIDUAL GAME DRIVE OBJECT
			- If stats year is not equal to the current year, grab from cache
			- Else if stats year is equal to current year and was last updated over an hour ago, write cache file
			- Else write cache file
		*/
		$drive_string = CACHE . 'drive/drive?gsis_id=eq.'.$date.'&order=time_inserted.desc.json';
		if (file_exists($drive_string) && $year != $currentyear) {
			$jsondrive = file_get_contents($drive_string);
			$objdrive = json_decode($jsondrive, true);
		} else if (file_exists($drive_string) && filemtime($drive_string) + 3600 > time()) {
			$jsondrive = file_get_contents($drive_string);
			$objdrive = json_decode($jsondrive, true);
		} else {
			$msg = file_get_contents($postgrest . 'drive?gsis_id=eq.'.$date.'&order=time_inserted.desc');
			$f = fopen($drive_string, "w+");
			fwrite($f, $msg);
			fclose($f);
			$objdrive = json_decode($msg, true);
		}

		// Game not started yet
		if (empty($objdrive)) {
			$quarter = 'P';
			$progress = '0%';
			$upcoming_time = date("g:i A", strtotime($val["start_time"]));
			if (strtotime($val["start_time"]) <= time() + 43200) {
				$upcoming_countdown = '<span style="font-weight:normal;">(<span data-countdown="'.date("Y/m/d g:i:s A", strtotime($val["start_time"])).'"></span>)</span>';
			} else {
				$upcoming_countdown = '';
			}
		} else {
			$quarter = $objdrive[0]["end_time"]["phase"];
			$elapsed = $objdrive[0]["end_time"]["elapsed"];
			$posteam = $objdrive[0]["pos_team"];
			$elapsed = 900 - $elapsed;
			$clock = gmdate("i:s", $elapsed);

			if ($posteam == $homecode) {
				$hometeam_f = $hometeam." <img src='/assets/img/posteam.png' />";
				$awayteam_f = $awayteam; 
			} else if ($posteam == $awaycode) {
				$awayteam_f = $awayteam." <img src='/assets/img/posteam.png' />";
				$hometeam_f = $hometeam; 
			} else {
				$awayteam_f = $awayteam;
				$hometeam_f = $hometeam;
			}

			if ($finished) {
				$quarter = "F";
				$progress = "100%";
			} else if ($quarter == "Q1") {
				$progress = "25%";
			} else if ($quarter == "Q2") {
				$progress = "50%";
			} else if ($quarter == "Q3") {
				$progress = "75%";
			} else if ($quarter == "Q4" && $finished) {
				$progress = "100%";
				$quarter = "F";
			} else if ($quarter == "Q4") {
				$progress = "85%";
			} else if ($quarter == "Q5" && $finished) {
				$progress = "100%";
				$quarter = "F";
			} else if ($quarter == "OT" && $finished) {
				$progress = "100%";
				$quarter = "F";
			} 
		}

		$hashtag = $awaycode."vs".$homecode;
		$globalgameref[$date] = $awaycode." vs. ".$homecode;

		// Game is in progress or finished
		if ($quarter !== "P") {

			$highlight = "bg-black text-white";

			// Game in progress
			if ($quarter !== "F") {

				if ($objdrive[0]["result"]) {
					$result = ' - '.$objdrive[0]["result"];
				} else {
					$result = '';
				}
				$yards_gained = $objdrive[0]["yards_gained"];
				$drive_line = "(".$posteam.") ".$yards_gained." YD gain".$result;

				$progress_label = "IN-PROGRESS";
				$progress_button = '<span class="label label-danger m-r-10 pull-right"><strong>IN-PROGRESS</strong></span>';
				$bottom_line = 	'<div class="alert fade in" style="background-color: rgba(255, 0, 0, 0.45); margin-bottom: 0px;border-radius: 0px;">
									<ul class="list-inline">
										<li><strong style="color:#222;">'.$quarter.' <span style="font-weight:normal;">('.$clock.')</span> Drive: </strong><span style="color:#222;">'.$drive_line.'</span></strong></li>
									</ul>
								</div>';

				$progress_updater = '<meta http-equiv="refresh" content="60" />';

				if ($homescore > $awayscore) {
					$home_tr = '<tr class="success">';
					$away_tr = '<tr>';
				} else if ($awayscore > $homescore) {
					$away_tr = '<tr class="success">';
					$home_tr = '<tr>';
				} else {
					$home_tr = '<tr>';
					$away_tr = '<tr>';
				}

				$drives_stats = '';

				foreach ($objdrive as $key => $drives) {
					$drives_stats .= 	'<tr>
											<td>'.$drives["drive_id"].'</td>
											<td>'.$drives["end_time"]["phase"].'</td>
											<td>'.$drives["pos_team"].'</td>
											<td>'.$drives["yards_gained"].'</td>
											<td>'.$drives["result"].'</td>
										</tr>';
				}

				$drives_table = 	'<div class="table-responsive">
										<table id="data-table" class="table" style="margin-bottom:0px;">
											<thead>
												<tr class="active">
													<th>Drive</th>
													<th>QTR</th>
													<th>POS</th>
													<th>YDS</th>
													<th>Result</th>
												</tr>
											</thead>
											<tbody>
												'.$drives_stats.'
											</tbody>
										</table>
									</div>';

				$tabs = 	'<li class="active"><a href="#'.$hashtag.'-tab-1" data-toggle="tab" aria-expanded="true">Live Score</a></li>
							<li><a href="#'.$hashtag.'-tab-2" data-toggle="tab" aria-expanded="false">Drives</a></li>
							<li><a href="game.php?game_id='.$date.'&type='.$season_type.'&year='.$year.'&week='.$week.'" aria-expanded="false"><i class="fa fa-angle-double-right"></i></a></li>';
				$tabbody = 	'<div class="tab-pane fade" id="'.$hashtag.'-tab-2">
								<div data-scrollbar="true" data-height="215px">
	                            	'.$drives_table.'
								</div>
							</div>';

			// Game finished
			} else {

				$progress_updater = '';

				// STAT COLLECTION
				$master_passing_yds_away = array();
				$master_passing_yds_home = array();
				$master_rushing_yds_away = array();
				$master_rushing_yds_home = array();
				$master_receiving_yds_away = array();
				$master_receiving_yds_home = array();

				$master_passing_yds_away_summary = array();
				$master_passing_yds_home_summary = array();
				$master_rushing_yds_away_summary = array();
				$master_rushing_yds_home_summary = array();
				$master_receiving_yds_away_summary = array();
				$master_receiving_yds_home_summary = array();

				$awayteam_f = $awayteam;
				$hometeam_f = $hometeam;
				$progress_label = "FINAL";
				$progress_button = '<span class="label label-default m-r-10 pull-right"><strong>FINAL</strong></span>';
				$bottom_line = '<div class="alert fade in" style="margin-bottom: 0px;	background: #d2d2d2;border-radius: 0px;">
								<ul class="list-inline">
									<li><strong style="color:#151515;"><a href="game.php?game_id='.$date.'&type='.$season_type.'&year='.$year.'&week='.$week.'"><span class="label label-primary">Game Analysis</span></a></strong></li>
								</ul>
							</div>';

				if ($homescore > $awayscore) {
					$home_tr = '<tr class="success">';
					$away_tr = '<tr>';
				} else if ($awayscore > $homescore) {
					$away_tr = '<tr class="success">';
					$home_tr = '<tr>';
				}

				// PLAY BY PLAY
				$playbyplay_string = CACHE . 'play/play?gsis_id=eq.'.$date.'&order=drive_id.asc,play_id.asc.json';
				if (file_exists($playbyplay_string)) {
					$jsonplaybyplay = file_get_contents($playbyplay_string);
					$objplaybyplay = json_decode($jsonplaybyplay, true);
				} else {
					$msg = file_get_contents($postgrest . 'play?gsis_id=eq.'.$date.'&order=drive_id.asc,play_id.asc');
					$f = fopen($playbyplay_string, "w+");
					fwrite($f, $msg);
					fclose($f);
					$objplaybyplay = json_decode($msg, true);
				}

				$playbyplay_stats = '';

				foreach ($objplaybyplay as $play => $stat) {
					$playbyplay_stats .= 	"<tr>
												<td>".$stat["yards_to_go"]."</td>
												<td>".$stat["pos_team"]."</td>
												<td>".$stat["description"]."</td>
											</tr>";
				}

				$playbyplay_table = '<div class="table-responsive">
										<table id="data-table" class="table" style="margin-bottom:0px;">
											<thead>
												<tr class="active">
													<th>Y2G</th>
													<th>POS</th>
													<th>Description</th>
												</tr>
											</thead>
											<tbody>
												'.$playbyplay_stats.'
											</tbody>
										</table>
									</div>';

				
				// STATS
				$passing_stats_away = '';
				$passing_stats_home = '';

				$rushing_stats_away = '';
				$rushing_stats_home = '';

				$receiving_stats_away = '';
				$receiving_stats_home = '';

				$summary_passing_stats_away = '';
				$summary_passing_stats_home = '';

				$summary_rushing_stats_away = '';
				$summary_rushing_stats_home = '';

				$summary_receiving_stats_away = '';
				$summary_receiving_stats_home = '';

				// PASSING AWAY
				$passing_string_away = CACHE . 'play_player/play_player?gsis_id=eq.'.$date.'&team=eq.'.$awaycode.'&select=team,player_id,passing_att,passing_cmp,passing_cmp_air_yds,passing_incmp,passing_incmp_air_yds,passing_int,passing_sk,passing_sk_yds,passing_tds,passing_yds&passing_att=not.eq.0.json';
				if (file_exists($passing_string_away)) {
					$jsonpassingaway = file_get_contents($passing_string_away);
					$objpassingaway = json_decode($jsonpassingaway, true);
				} else {
					$msg = file_get_contents($postgrest . 'play_player?gsis_id=eq.'.$date.'&team=eq.'.$awaycode.'&select=team,player_id,passing_att,passing_cmp,passing_cmp_air_yds,passing_incmp,passing_incmp_air_yds,passing_int,passing_sk,passing_sk_yds,passing_tds,passing_yds&passing_att=not.eq.0');
					$f = fopen($passing_string_away, "w+");
					fwrite($f, $msg);
					fclose($f);
					$objpassingaway = json_decode($msg, true);
				}

				$passing_att_away = 0;
				$passing_cmp_away = 0;
				$passing_yds_away = 0;
				$passing_tds_away = 0;
				$passing_int_away = 0;
				$passing_id_away = 0;

				foreach ($objpassingaway as $pass => $stat) {
				    $passing_id_away = $stat["player_id"];
					$passing_att_away = $passing_att_away + $stat["passing_att"];
					$passing_cmp_away = $passing_cmp_away + $stat["passing_cmp"];
					$passing_yds_away = $passing_yds_away + $stat["passing_yds"];
					$passing_tds_away = $passing_tds_away + $stat["passing_tds"];
					$passing_int_away = $passing_int_away + $stat["passing_int"];
				}

				$player_string_away = CACHE . 'player/player?player_id=eq.'.$passing_id_away.'.json';
				if (file_exists($player_string_away)) {
					$jsonplayeraway = file_get_contents($player_string_away);
					$objplayeraway = json_decode($jsonplayeraway, true);
				} else {
					$msg = file_get_contents($postgrest . 'player?player_id=eq.'.$passing_id_away);
					$f = fopen($player_string_away, "w+");
					fwrite($f, $msg);
					fclose($f);
					$objplayeraway = json_decode($msg, true);
				}

				$passing_stats_away .= 	"<tr>
											<td><a href='player.php?player_id=".$passing_id_away."'>".$objplayeraway[0]["first_name"][0].".".$objplayeraway[0]["last_name"]."</a></td>
								 			<td>".$passing_cmp_away."/".$passing_att_away."</td>
								 			<td>".$passing_yds_away."</td>
								 			<td>".$passing_tds_away."</td>
								 			<td>".$passing_int_away."</td>
								 		</tr>";

				$passing_stats_away_sum = 	"<tr>
												<td><a href='player.php?player_id=".$passing_id_away."'>".$objplayeraway[0]["first_name"][0].".".$objplayeraway[0]["last_name"]."</a></td>
								 				<td>".$passing_cmp_away."/".$passing_att_away."</td>
								 				<td>".$passing_yds_away."</td>
								 				<td>".$passing_tds_away."</td>
								 				<td>".$passing_int_away."</td>
								 			</tr>";

				$master_passing_yds_away["".$objplayeraway[0]["first_name"][0].".".$objplayeraway[0]["last_name"]." ".$date." ".$passing_id_away.""] = $passing_yds_away;
				$master_passing_yds_away_summary["".$passing_id_away.""] = $passing_stats_away_sum;

				// PASSING HOME
				$passing_string_home = CACHE . 'play_player/play_player?gsis_id=eq.'.$date.'&team=eq.'.$homecode.'&select=team,player_id,passing_att,passing_cmp,passing_cmp_air_yds,passing_incmp,passing_incmp_air_yds,passing_int,passing_sk,passing_sk_yds,passing_tds,passing_yds&passing_att=not.eq.0.json';
				if (file_exists($passing_string_home)) {
					$jsonpassinghome = file_get_contents($passing_string_home);
					$objpassinghome = json_decode($jsonpassinghome, true);
				} else {
					$msg = file_get_contents($postgrest . 'play_player?gsis_id=eq.'.$date.'&team=eq.'.$homecode.'&select=team,player_id,passing_att,passing_cmp,passing_cmp_air_yds,passing_incmp,passing_incmp_air_yds,passing_int,passing_sk,passing_sk_yds,passing_tds,passing_yds&passing_att=not.eq.0');
					$f = fopen($passing_string_home, "w+");
					fwrite($f, $msg);
					fclose($f);
					$objpassinghome = json_decode($msg, true);
				}

				$passing_att_home = 0;
				$passing_cmp_home = 0;
				$passing_yds_home = 0;
				$passing_tds_home = 0;
				$passing_int_home = 0;
				$passing_id_home = 0;

				foreach ($objpassinghome as $pass => $passstat) {
				    $passing_id_home = $passstat["player_id"];
					$passing_att_home = $passing_att_home + $passstat["passing_att"];
					$passing_cmp_home = $passing_cmp_home + $passstat["passing_cmp"];
					$passing_yds_home = $passing_yds_home + $passstat["passing_yds"];
					$passing_tds_home = $passing_tds_home + $passstat["passing_tds"];
					$passing_int_home = $passing_int_home + $passstat["passing_int"];
				}

				$player_string_home = CACHE . 'player/player?player_id=eq.'.$passing_id_home.'.json';
				if (file_exists($player_string_home)) {
					$jsonplayerhome = file_get_contents($player_string_home);
					$objplayerhome = json_decode($jsonplayerhome, true);
				} else {
					$msg = file_get_contents($postgrest . 'player?player_id=eq.'.$passing_id_home);
					$f = fopen($player_string_home, "w+");
					fwrite($f, $msg);
					fclose($f);
					$objplayerhome = json_decode($msg, true);
				}

				$passing_stats_home .= 	"<tr>
											<td><a href='player.php?player_id=".$passing_id_home."'>".$objplayerhome[0]["first_name"][0].".".$objplayerhome[0]["last_name"]."</a></td>
								 			<td>".$passing_cmp_home."/".$passing_att_home."</td>
								 			<td>".$passing_yds_home."</td>
								 			<td>".$passing_tds_home."</td>
								 			<td>".$passing_int_home."</td>
								 		</tr>";

				$passing_stats_home_sum = 	"<tr>
												<td><a href='player.php?player_id=".$passing_id_home."'>".$objplayerhome[0]["gsis_name"]."</a></td>
								 				<td>".$passing_cmp_home."/".$passing_att_home."</td>
								 				<td>".$passing_yds_home."</td>
								 				<td>".$passing_tds_home."</td>
								 				<td>".$passing_int_home."</td>
								 			</tr>";

				$master_passing_yds_home["".$objplayerhome[0]["gsis_name"]." ".$date." ".$passing_id_home.""] = $passing_yds_home;
				$master_passing_yds_home_summary["".$passing_id_home.""] = $passing_stats_home_sum;

				// RUSHING AWAY
				$rushing_string_away = CACHE . 'play_player/play_player?gsis_id=eq.'.$date.'&team=eq.'.$awaycode.'&select=team,player_id,rushing_att,rushing_loss,rushing_loss_yds,rushing_tds,rushing_yds&rushing_att=not.eq.0&order=team.asc,player_id.desc.json';
				if (file_exists($rushing_string_away)) {
					$jsonrushingaway = file_get_contents($rushing_string_away);
					$objrushingaway = json_decode($jsonrushingaway, true);
				} else {
					$msg = file_get_contents($postgrest . 'play_player?gsis_id=eq.'.$date.'&team=eq.'.$awaycode.'&select=team,player_id,rushing_att,rushing_loss,rushing_loss_yds,rushing_tds,rushing_yds&rushing_att=not.eq.0&order=team.asc,player_id.desc');
					$f = fopen($rushing_string_away, "w+");
					fwrite($f, $msg);
					fclose($f);
					$objrushingaway = json_decode($msg, true);
				}

				$rushing_id_away = array();
				$r = 0;

				foreach ($objrushingaway as $rush => $rushstat) {
					$rushing_id_away[$r] = $rushstat["player_id"];
					$r++;
				}

				$r = 0;

				$rushing_id_away = array_unique($rushing_id_away);

				$rushing_att_away = 0;
				$rushing_yds_away = 0;
				$rushing_tds_away = 0;
				$rushing_loss_away = 0;

				foreach ($rushing_id_away as $rush => $rushstat) {
					$player_save_id = $rushstat;
					$player_string_away = CACHE . 'player/player?player_id=eq.'.$rushstat.'.json';
					if (file_exists($player_string_away)) {
						$jsonplayeraway = file_get_contents($player_string_away);
						$objplayeraway = json_decode($jsonplayeraway, true);
					} else {
						$msg = file_get_contents($postgrest . 'player?player_id=eq.'.$rushstat);
						$f = fopen($player_string_away, "w+");
						fwrite($f, $msg);
						fclose($f);
						$objplayeraway = json_decode($msg, true);
					}
					$name = $objplayeraway[0]["first_name"][0].".".$objplayeraway[0]["last_name"];
				    $rushing_string_away = CACHE . 'play_player/play_player?gsis_id=eq.'.$date.'&team=eq.'.$awaycode.'&player_id=eq.'.$rushstat.'&select=team,player_id,rushing_att,rushing_loss,rushing_loss_yds,rushing_tds,rushing_yds&rushing_att=not.eq.0.json';
				    if (file_exists($rushing_string_away)) {
				    	$jsonrushingaway = file_get_contents($rushing_string_away);
				    	$objrushingaway = json_decode($jsonrushingaway, true);
				    } else {
				    	$msg = file_get_contents($postgrest . 'play_player?gsis_id=eq.'.$date.'&team=eq.'.$awaycode.'&player_id=eq.'.$rushstat.'&select=team,player_id,rushing_att,rushing_loss,rushing_loss_yds,rushing_tds,rushing_yds&rushing_att=not.eq.0');
				    	$f = fopen($rushing_string_away, "w+");
				    	fwrite($f, $msg);
				    	fclose($f);
				    	$objrushingaway = json_decode($msg, true);
				    }
				    $rushing_att_away = 0;
					$rushing_yds_away = 0;
					$rushing_tds_away = 0;
					$rushing_loss_away = 0;
				    foreach ($objrushingaway as $rush => $rushstat) {
						$rushing_att_away = $rushing_att_away + $rushstat["rushing_att"];
						$rushing_yds_away = $rushing_yds_away + $rushstat["rushing_yds"];
						$rushing_tds_away = $rushing_tds_away + $rushstat["rushing_tds"];
						$rushing_loss_away = $rushing_loss_away + $rushstat["rushing_loss"];
					}
					$rushing_stats_away .= 	"<tr>
												<td><a href='player.php?player_id=".$player_save_id."'>".$name."</a></td>
									 			<td>".$rushing_att_away."</td>
									 			<td>".$rushing_yds_away."</td>
									 			<td>".$rushing_tds_away."</td>
									 			<td>".$rushing_loss_away."</td>
									 		</tr>";
					$rushing_stats_away_sum = 	"<tr>
													<td><a href='player.php?player_id=".$player_save_id."'>".$name."</a></td>
									 				<td>".$rushing_att_away."</td>
									 				<td>".$rushing_yds_away."</td>
									 				<td>".$rushing_tds_away."</td>
									 				<td>".$rushing_loss_away."</td>
									 			</tr>";

					$master_rushing_yds_away["".$name." ".$date." ".$player_save_id.""] = $rushing_yds_away;
					$master_rushing_yds_away_summary["".$player_save_id.""] = $rushing_stats_away_sum;
				}

				// RUSHING HOME
				$rushing_string_home = CACHE . 'play_player/play_player?gsis_id=eq.'.$date.'&team=eq.'.$homecode.'&select=team,player_id,rushing_att,rushing_loss,rushing_loss_yds,rushing_tds,rushing_yds&rushing_att=not.eq.0.json';
				if (file_exists($rushing_string_home)) {
					$jsonrushinghome = file_get_contents($rushing_string_home);
					$objrushinghome = json_decode($jsonrushinghome, true);
				} else {
					$msg = file_get_contents($postgrest . 'play_player?gsis_id=eq.'.$date.'&team=eq.'.$homecode.'&select=team,player_id,rushing_att,rushing_loss,rushing_loss_yds,rushing_tds,rushing_yds&rushing_att=not.eq.0');
					$f = fopen($rushing_string_home, "w+");
					fwrite($f, $msg);
					fclose($f);
					$objrushinghome = json_decode($msg, true);
				}

				$rushing_id_home = array();
				$r = 0;

				foreach ($objrushinghome as $rush => $rushstat) {
					$rushing_id_home[$r] = $rushstat["player_id"];
					$r++;
				}

				$r = 0;

				$rushing_id_home = array_unique($rushing_id_home);

				$rushing_att_home = 0;
				$rushing_yds_home = 0;
				$rushing_tds_home = 0;
				$rushing_loss_home = 0;

				foreach ($rushing_id_home as $rush => $rushstat) {
					$player_save_id = $rushstat;
					$player_string_home = CACHE . 'player/player?player_id=eq.'.$rushstat.'.json';
					if (file_exists($player_string_home)) {
						$jsonplayerhome = file_get_contents($player_string_home);
						$objplayerhome = json_decode($jsonplayerhome, true);
					} else {
						$msg = file_get_contents($postgrest . 'player?player_id=eq.'.$rushstat);
						$f = fopen($player_string_home, "w+");
						fwrite($f, $msg);
						fclose($f);
						$objplayerhome = json_decode($msg, true);
					}
					$name = $objplayerhome[0]["first_name"][0].".".$objplayerhome[0]["last_name"];
				    $rushing_string_home = CACHE . 'play_player/play_player?gsis_id=eq.'.$date.'&team=eq.'.$homecode.'&player_id=eq.'.$rushstat.'&select=team,player_id,rushing_att,rushing_loss,rushing_loss_yds,rushing_tds,rushing_yds&rushing_att=not.eq.0.json';
				    if (file_exists($rushing_string_home)) {
				    	$jsonrushinghome = file_get_contents($rushing_string_home);
				    	$objrushinghome = json_decode($jsonrushinghome, true);
				    } else {
				    	$msg = file_get_contents($postgrest . 'play_player?gsis_id=eq.'.$date.'&team=eq.'.$homecode.'&player_id=eq.'.$rushstat.'&select=team,player_id,rushing_att,rushing_loss,rushing_loss_yds,rushing_tds,rushing_yds&rushing_att=not.eq.0');
				    	$f = fopen($rushing_string_home, "w+");
				    	fwrite($f, $msg);
				    	fclose($f);
				    	$objrushinghome = json_decode($msg, true);
				    }
				    $rushing_att_home = 0;
					$rushing_yds_home = 0;
					$rushing_tds_home = 0;
					$rushing_loss_home = 0;
				    foreach ($objrushinghome as $rush => $rushstat) {
						$rushing_att_home = $rushing_att_home + $rushstat["rushing_att"];
						$rushing_yds_home = $rushing_yds_home + $rushstat["rushing_yds"];
						$rushing_tds_home = $rushing_tds_home + $rushstat["rushing_tds"];
						$rushing_loss_home = $rushing_loss_home + $rushstat["rushing_loss"];
					}
					$rushing_stats_home .= 	"<tr>
												<td><a href='player.php?player_id=".$player_save_id."'>".$name."</a></td>
									 			<td>".$rushing_att_home."</td>
									 			<td>".$rushing_yds_home."</td>
									 			<td>".$rushing_tds_home."</td>
									 			<td>".$rushing_loss_home."</td>
									 		</tr>";
					$rushing_stats_home_sum = 	"<tr>
													<td><a href='player.php?player_id=".$player_save_id."'>".$name."</a></td>
									 				<td>".$rushing_att_home."</td>
									 				<td>".$rushing_yds_home."</td>
									 				<td>".$rushing_tds_home."</td>
									 				<td>".$rushing_loss_home."</td>
									 			</tr>";

					$master_rushing_yds_home["".$name." ".$date." ".$player_save_id.""] = $rushing_yds_home;
					$master_rushing_yds_home_summary["".$player_save_id.""] = $rushing_stats_home_sum;
				}

				// RECEIVING AWAY
				$receiving_string_away = CACHE . 'play_player/play_player?gsis_id=eq.'.$date.'&team=eq.'.$awaycode.'&select=team,player_id,receiving_rec,receiving_tar,receiving_tds,receiving_twopta,receiving_twoptm,receiving_twoptmissed,receiving_yac_yds,receiving_yds&receiving_rec=not.eq.0.json';
				if (file_exists($receiving_string_away)) {
					$jsonreceivingaway = file_get_contents($receiving_string_away);
					$objreceivingaway = json_decode($jsonreceivingaway, true);
				} else {
					$msg = file_get_contents($postgrest . 'play_player?gsis_id=eq.'.$date.'&team=eq.'.$awaycode.'&select=team,player_id,receiving_rec,receiving_tar,receiving_tds,receiving_twopta,receiving_twoptm,receiving_twoptmissed,receiving_yac_yds,receiving_yds&receiving_rec=not.eq.0');
					$f = fopen($receiving_string_away, "w+");
					fwrite($f, $msg);
					fclose($f);
					$objreceivingaway = json_decode($msg, true);
				}

				$receiving_id_away = array();
				$r = 0;

				foreach ($objreceivingaway as $receive => $receivestat) {
					$receiving_id_away[$r] = $receivestat["player_id"];
					$r++;
				}

				$r = 0;

				$receiving_id_away = array_unique($receiving_id_away);

				$receiving_rec_away = 0;
				$receiving_yds_away = 0;
				$receiving_tds_away = 0;
				$receiving_yac_away = 0;

				foreach ($receiving_id_away as $receive => $receivestat) {
					$player_save_id = $receivestat;
					$player_string_away = CACHE . 'player/player?player_id=eq.'.$receivestat.'.json';
					if (file_exists($player_string_away)) {
						$jsonplayeraway = file_get_contents($player_string_away);
						$objplayeraway = json_decode($jsonplayeraway, true);
					} else {
						$msg = file_get_contents($postgrest . 'player?player_id=eq.'.$receivestat);
						$f = fopen($player_string_away, "w+");
						fwrite($f, $msg);
						fclose($f);
						$objplayeraway = json_decode($msg, true);
					}
					$name = $objplayeraway[0]["first_name"][0].".".$objplayeraway[0]["last_name"];
				    $receiving_string_away = CACHE . 'play_player/play_player?gsis_id=eq.'.$date.'&team=eq.'.$awaycode.'&player_id=eq.'.$receivestat.'&select=team,player_id,receiving_rec,receiving_tar,receiving_tds,receiving_twopta,receiving_twoptm,receiving_twoptmissed,receiving_yac_yds,receiving_yds.json';
				    if (file_exists($receiving_string_away)) {
				    	$jsonreceivingaway = file_get_contents($receiving_string_away);
				    	$objreceivingaway = json_decode($jsonreceivingaway, true);
				    } else {
				    	$msg = file_get_contents($postgrest . 'play_player?gsis_id=eq.'.$date.'&team=eq.'.$awaycode.'&player_id=eq.'.$receivestat.'&select=team,player_id,receiving_rec,receiving_tar,receiving_tds,receiving_twopta,receiving_twoptm,receiving_twoptmissed,receiving_yac_yds,receiving_yds');
				    	$f = fopen($receiving_string_away, "w+");
				    	fwrite($f, $msg);
				    	fclose($f);
				    	$objreceivingaway = json_decode($msg, true);
				    }
				    $receiving_rec_away = 0;
					$receiving_yds_away = 0;
					$receiving_tds_away = 0;
					$receiving_yac_away = 0;
				    foreach ($objreceivingaway as $receive => $receivestat) {
						$receiving_rec_away = $receiving_rec_away + $receivestat["receiving_rec"];
						$receiving_yds_away = $receiving_yds_away + $receivestat["receiving_yds"];
						$receiving_tds_away = $receiving_tds_away + $receivestat["receiving_tds"];
						$receiving_yac_away = $receiving_yac_away + $receivestat["receiving_yac_yds"];
					}
					$receiving_stats_away .= 	"<tr>
													<td><a href='player.php?player_id=".$player_save_id."'>".$name."</a></td>
									 				<td>".$receiving_rec_away."</td>
									 				<td>".$receiving_yds_away."</td>
									 				<td>".$receiving_tds_away."</td>
									 				<td>".$receiving_yac_away."</td>
									 			</tr>";
					$receiving_stats_away_sum = "<tr>
													<td><a href='player.php?player_id=".$player_save_id."'>".$name."</a></td>
									 				<td>".$receiving_rec_away."</td>
									 				<td>".$receiving_yds_away."</td>
									 				<td>".$receiving_tds_away."</td>
									 				<td>".$receiving_yac_away."</td>
									 			</tr>";

					$master_receiving_yds_away["".$name." ".$date." ".$player_save_id.""] = $receiving_yds_away;
					$master_receiving_yds_away_summary["".$player_save_id.""] = $receiving_stats_away_sum;
				}

				// RECEIVING HOME
				$receiving_string_home = CACHE . 'play_player/play_player?gsis_id=eq.'.$date.'&team=eq.'.$homecode.'&select=team,player_id,receiving_rec,receiving_tar,receiving_tds,receiving_twopta,receiving_twoptm,receiving_twoptmissed,receiving_yac_yds,receiving_yds&receiving_rec=not.eq.0&order=team.asc,player_id.desc.json';
				if (file_exists($receiving_string_home)) {
					$jsonreceivinghome = file_get_contents($receiving_string_home);
					$objreceivinghome = json_decode($jsonreceivinghome, true);
				} else {
					$msg = file_get_contents($postgrest . 'play_player?gsis_id=eq.'.$date.'&team=eq.'.$homecode.'&select=team,player_id,receiving_rec,receiving_tar,receiving_tds,receiving_twopta,receiving_twoptm,receiving_twoptmissed,receiving_yac_yds,receiving_yds&receiving_rec=not.eq.0&order=team.asc,player_id.desc');
					$f = fopen($receiving_string_home, "w+");
					fwrite($f, $msg);
					fclose($f);
					$objreceivinghome = json_decode($msg, true);
				}

				$receiving_id_home = array();
				$r = 0;

				foreach ($objreceivinghome as $receive => $receivestat) {
					$receiving_id_home[$r] = $receivestat["player_id"];
					$r++;
				}

				$r = 0;

				$receiving_id_home = array_unique($receiving_id_home);

				$receiving_rec_home = 0;
				$receiving_yds_home = 0;
				$receiving_tds_home = 0;
				$receiving_yac_home = 0;

				foreach ($receiving_id_home as $receive => $receivestat) {
					$player_save_id = $receivestat;
					$player_string_home = CACHE . 'player/player?player_id=eq.'.$receivestat.'.json';
					if (file_exists($player_string_home)) {
						$jsonplayerhome = file_get_contents($player_string_home);
						$objplayerhome = json_decode($jsonplayerhome, true);
					} else {
						$msg = file_get_contents($postgrest . 'player?player_id=eq.'.$receivestat);
						$f = fopen($player_string_home, "w+");
						fwrite($f, $msg);
						fclose($f);
						$objplayerhome = json_decode($msg, true);
					}
					$name = $objplayerhome[0]["first_name"][0].".".$objplayerhome[0]["last_name"];
				    $receiving_string_home = CACHE . 'play_player/play_player?gsis_id=eq.'.$date.'&team=eq.'.$homecode.'&player_id=eq.'.$receivestat.'&select=team,player_id,receiving_rec,receiving_tar,receiving_tds,receiving_twopta,receiving_twoptm,receiving_twoptmissed,receiving_yac_yds,receiving_yds.json';
				    if (file_exists($receiving_string_home)) {
				    	$jsonreceivinghome = file_get_contents($receiving_string_home);
				    	$objreceivinghome = json_decode($jsonreceivinghome, true);
				    } else {
				    	$msg = file_get_contents($postgrest . 'play_player?gsis_id=eq.'.$date.'&team=eq.'.$homecode.'&player_id=eq.'.$receivestat.'&select=team,player_id,receiving_rec,receiving_tar,receiving_tds,receiving_twopta,receiving_twoptm,receiving_twoptmissed,receiving_yac_yds,receiving_yds');
				    	$f = fopen($receiving_string_home, "w+");
				    	fwrite($f, $msg);
				    	fclose($f);
				    	$objreceivinghome = json_decode($msg, true);
				    }
				    $receiving_rec_home = 0;
					$receiving_yds_home = 0;
					$receiving_tds_home = 0;
					$receiving_yac_home = 0;
				    foreach ($objreceivinghome as $receive => $receivestat) {
						$receiving_rec_home = $receiving_rec_home + $receivestat["receiving_rec"];
						$receiving_yds_home = $receiving_yds_home + $receivestat["receiving_yds"];
						$receiving_tds_home = $receiving_tds_home + $receivestat["receiving_tds"];
						$receiving_yac_home = $receiving_yac_home + $receivestat["receiving_yac_yds"];
					}
					$receiving_stats_home .= 	"<tr>
													<td><a href='player.php?player_id=".$player_save_id."'>".$name."</a></td>
									 				<td>".$receiving_rec_home."</td>
									 				<td>".$receiving_yds_home."</td>
									 				<td>".$receiving_tds_home."</td>
									 				<td>".$receiving_yac_home."</td>
									 			</tr>";
					$receiving_stats_home_sum = "<tr>
													<td><a href='player.php?player_id=".$player_save_id."'>".$name."</a></td>
									 				<td>".$receiving_rec_home."</td>
									 				<td>".$receiving_yds_home."</td>
									 				<td>".$receiving_tds_home."</td>
									 				<td>".$receiving_yac_home."</td>
									 			</tr>";

					$master_receiving_yds_home["".$name." ".$date." ".$player_save_id.""] = $receiving_yds_home;
					$master_receiving_yds_home_summary["".$player_save_id.""] = $receiving_stats_home_sum;
				}

				// CALCULATE INDIVIDUAL GAME HIGHS
				foreach ($master_passing_yds_away_summary as $val) {
					$summary_passing_stats_away = $val;
				}

				foreach ($master_passing_yds_home_summary as $val) {
					$summary_passing_stats_home = $val;
				}

				$summary_table_away = '<table class="table" style="margin-bottom:0px;">
									<thead>
										<tr class="'.$awaycode.'colors">
											<th>Team Leaders: <a href="team.php?team='.$awaycode.'">'.$awayteam.'</a> ('.$wins["".$awaycode.""].'-'.$losses["".$awaycode.""].'-'.$ties["".$awaycode.""].')</th>
											<th></th>
											<th></th>
											<th></th>
											<th></th>
										</tr>
										<tr class="active">
											<th>Passing</th>
											<th>CP/AT</th>
											<th>YDS</th>
											<th>TD</th>
											<th>INT</th>
										<tr>
									</thead>
									<tbody>
										'.$summary_passing_stats_away.'
									<tbody>
								</table>';

				$summary_table_home = '<table class="table" style="margin-bottom:0px;">
									<thead>
										<tr class="'.$homecode.'colors">
											<th>Team Leaders: <a href="team.php?team='.$homecode.'">'.$hometeam.'</a> ('.$wins["".$homecode.""].'-'.$losses["".$homecode.""].'-'.$ties["".$homecode.""].')</th>
											<th></th>
											<th></th>
											<th></th>
											<th></th>
										</tr>
										<tr class="active">
											<th>Passing</th>
											<th>CP/AT</th>
											<th>YDS</th>
											<th>TD</th>
											<th>INT</th>
										<tr>
									</thead>
									<tbody>
										'.$summary_passing_stats_home.'
									<tbody>
								</table>';

				$summary_table_away .= '<table class="table">
									<thead>
										<tr class="active">
											<th>Rushing</th>
											<th>ATT</th>
											<th>YDS</th>
											<th>TD</th>
											<th>LOSS</th>
										<tr>
									</thead>
									<tbody>
										'.$summary_rushing_stats_away.'
									<tbody>
								</table>';

				$summary_table_home .= '<table class="table">
									<thead>
										<tr class="active">
											<th>Rushing</th>
											<th>ATT</th>
											<th>YDS</th>
											<th>TD</th>
											<th>LOSS</th>
										</tr>
									</thead>
									<tbody>
										'.$summary_rushing_stats_home.'
									<tbody>
								</table>';

				$summary_table_away .= '<table class="table">
									<thead>
										<tr class="active">
											<th>Receiving</th>
											<th>REC</th>
											<th>YDS</th>
											<th>TD</th>
											<th>YAC</th>
										<tr>
									</thead>
									<tbody>
										'.$summary_receiving_stats_away.'
									<tbody>
								</table>';

				$summary_table_home .= '<table class="table">
									<thead>
										<tr class="active">
											<th>Receiving</th>
											<th>REC</th>
											<th>YDS</th>
											<th>TD</th>
											<th>YAC</th>
										</tr>
									</thead>
									<tbody>
										'.$summary_receiving_stats_home.'
									<tbody>
								</table>';

				$stats_table_away = '<table class="table" style="margin-bottom:0px;">
									<thead>
										<tr class="'.$awaycode.'colors">
											<th><a href="team.php?team='.$awaycode.'" class="'.$awaycode.'colors">'.$awayteam.'</a> <span class="'.$awaycode.'colors" style="font-weight:normal;">('.$wins["".$awaycode.""].'-'.$losses["".$awaycode.""].'-'.$ties["".$awaycode.""].')</span></th>
											<th></th>
											<th></th>
											<th></th>
											<th></th>
										</tr>
										<tr class="active">
											<th>Passing</th>
											<th>CP/AT</th>
											<th>YDS</th>
											<th>TD</th>
											<th>INT</th>
										<tr>
									</thead>
									<tbody>
										'.$passing_stats_away.'
									<tbody>
								</table>';

				$stats_table_home = '<table class="table" style="margin-bottom:0px;">
									<thead>
										<tr class="'.$homecode.'colors">
											<th><a href="team.php?team='.$homecode.'" class="'.$homecode.'colors">'.$hometeam.'</a> <span class="'.$homecode.'colors" style="font-weight:normal;">('.$wins["".$homecode.""].'-'.$losses["".$homecode.""].'-'.$ties["".$homecode.""].')</span></th>
											<th></th>
											<th></th>
											<th></th>
											<th></th>
										</tr>
										<tr class="active">
											<th>Passing</th>
											<th>CP/AT</th>
											<th>YDS</th>
											<th>TD</th>
											<th>INT</th>
										</tr>
									</thead>
									<tbody>
										'.$passing_stats_home.'
									<tbody>
								</table>';

				$stats_table_away .= '<table class="table">
									<thead>
										<tr class="active">
											<th>Rushing</th>
											<th>ATT</th>
											<th>YDS</th>
											<th>TD</th>
											<th>LOSS</th>
										<tr>
									</thead>
									<tbody>
										'.$rushing_stats_away.'
									<tbody>
								</table>';

				$stats_table_home .= '<table class="table">
									<thead>
										<tr class="active">
											<th>Rushing</th>
											<th>ATT</th>
											<th>YDS</th>
											<th>TD</th>
											<th>LOSS</th>
										</tr>
									</thead>
									<tbody>
										'.$rushing_stats_home.'
									<tbody>
								</table>';

				$stats_table_away .= '<table class="table">
									<thead>
										<tr class="active">
											<th>Receiving</th>
											<th>REC</th>
											<th>YDS</th>
											<th>TD</th>
											<th>YAC</th>
										<tr>
									</thead>
									<tbody>
										'.$receiving_stats_away.'
									<tbody>
								</table>';

				$stats_table_home .= '<table class="table">
									<thead>
										<tr class="active">
											<th>Receiving</th>
											<th>REC</th>
											<th>YDS</th>
											<th>TD</th>
											<th>YAC</th>
										</tr>
									</thead>
									<tbody>
										'.$receiving_stats_home.'
									<tbody>
								</table>';

				$tabs = '<li class="active"><a href="#'.$hashtag.'-tab-1" data-toggle="tab" aria-expanded="true">Final</a></li>
						<li class=""><a href="#'.$hashtag.'-tab-2" data-toggle="tab" aria-expanded="false">Box Score</a></li>
						<li class=""><a href="#'.$hashtag.'-tab-3" data-toggle="tab" aria-expanded="false">Play by Play</a></li>
						<li><a href="game.php?game_id='.$date.'&type='.$season_type.'&year='.$year.'&week='.$week.'" aria-expanded="false"><i class="fa fa-angle-double-right"></i></a></li>';
				$tabbody = '<div class="tab-pane fade" id="'.$hashtag.'-tab-2">
								<div data-scrollbar="true" data-height="215px">
	                            		'.$stats_table_away.'
										'.$stats_table_home.'
										</div>
									</div>
									<div class="tab-pane fade" id="'.$hashtag.'-tab-3">
										<div data-scrollbar="true" data-height="215px">
											'.$playbyplay_table.'
										</div>
									</div>';
			}

		// Game not started yet (upcoming)
		} else if ($quarter === "P") {

			$highlight = "";

			$stats_table_away = '';
			$stats_table_home = '';
			$playbyplay_table = '';

			$awayteam_f = $awayteam;
			$hometeam_f = $hometeam;

			$progress_label = "UPCOMING";
			$progress_button = '<span class="label label-info m-r-10 pull-right"><strong>UPCOMING</strong></span>';

			$away_tr = '<tr>';
			$home_tr = '<tr>';

			$homescore_q1 = '-';
			$homescore_q2 = '-';
			$homescore_q3 = '-';
			$homescore_q4 = '-';
			$homescore_q5 = '-';
			$homescore = '-';

			$awayscore_q1 = '-';
			$awayscore_q2 = '-';
			$awayscore_q3 = '-';
			$awayscore_q4 = '-';
			$awayscore_q5 = '-';
			$awayscore = '-';
			$progress_updater = '';

			$posteam = '';
			$clock = '';

			$tabs = '<li class="active"><a href="#'.$hashtag.'-tab-1" data-toggle="tab" aria-expanded="true">Upcoming</a></li>
					<li><a href="game.php?game_id='.$date.'&type='.$season_type.'&year='.$year.'&week='.$week.'" aria-expanded="false"><i class="fa fa-angle-double-right"></i></a></li>';
			$tabbody = '';

			$homeslug = strtolower($homecity)."-".strtolower($hometeam);
			$homeslug = preg_replace("/[^a-z0-9_\s-]/", "", $homeslug);
			$homeslug = preg_replace("/[\s-]+/", " ", $homeslug);
			$homeslug = preg_replace("/[\s_]/", "-", $homeslug);
			$awayslug = strtolower($awaycity)."-".strtolower($awayteam);
			$awayslug = preg_replace("/[^a-z0-9_\s-]/", "", $awayslug);
			$awayslug = preg_replace("/[\s-]+/", " ", $awayslug);
			$awayslug = preg_replace("/[\s_]/", "-", $awayslug);
			$tickets = 'http://api.seatgeek.com/2/events?taxonomies.name=sports&performers.slug='.$homeslug.'&performers.slug='.$awayslug.'&aid=11592';
			$ticketurl = file_get_contents($tickets);
			$objticket = json_decode($ticketurl,true);
			$ticketbutton = '<a href="'.$objticket["events"][0]["url"].'" target="_blank">FIND TICKETS</a>';

			$bottom_line = '<div class="alert fade in" style="background: #92DDFF;margin-bottom: 0px;border-radius: 0px;">
								<ul class="list-inline">
									<li><strong style="color:#151515;">'.$date_f.' @ '.$upcoming_time.' ET '.$upcoming_countdown.'</strong></li>
									<li style="font-weight:bold;">'.$ticketbutton.'</li>
								</ul>
							</div>';

		}

	// Year prior to 2009, score data only
	} else {

		$awayteam_f = $awayteam;
		$hometeam_f = $hometeam;
		$progress_label = "FINAL";
		$progress_button = '<span class="label label-default m-r-10 pull-right"><strong>FINAL</strong></span>';
		$bottom_line = '<div class="alert fade in" style="margin-bottom: 0px;	background: #d2d2d2;border-radius: 0px;">
						<ul class="list-inline">
							<li><strong style="color:#151515;"><a href="game.php?game_id='.$date.'&type='.$season_type.'&year='.$year.'&week='.$week.'"><span class="label label-primary">Game Analysis</span></a></strong></li>
						</ul>
					</div>';

		if ($homescore > $awayscore) {
			$home_tr = '<tr class="success">';
			$away_tr = '<tr>';
		} else if ($awayscore > $homescore) {
			$away_tr = '<tr class="success">';
			$home_tr = '<tr>';
		}
		
	}

	// Number of score widgets per row
	if ($i == 0) {
		$rowinsert = '<div class="row">';
		$rowinsertend = '';
	} else if ($i % 3 == 0) {
		$rowinsert = '</div><div class="row">';
		$rowinsertend = '';
	} else {
		$rowinsert = '';
	}

	$shareurl = 'http://statstrac.com/game.php?game_id='.$date.'&type='.$season_type.'&year='.$year.'&week='.$week;
	$shareurl = urlencode($shareurl);

	$sortid = $i + 1;

	// Score widget template
	$widget[$i] = $rowinsert.'
				<div class="col-md-4">
					<div class="panel panel-inverse game-'.$date.'" data-sortable-id="ui-widget-'.$sortid.'">
                        <div class="panel-heading">
                            <div class="panel-heading-btn pull-right">
                                <a href="http://www.facebook.com/share.php?u='.$shareurl.'" target="_blank" class="btn btn-primary btn-icon btn-circle btn-xs"><i class="fa fa-facebook"></i></a>
                                <a href="http://twitter.com/intent/tweet?status='.$shareurl.'" target="_blank" class="btn btn-info btn-icon btn-circle btn-xs"><i class="fa fa-twitter"></i></a>
                            </div>
                            <a href="game.php?game_id='.$date.'&type='.$season_type.'&year='.$year.'&week='.$week.'">'.$progress_button.'</a>
                            <h4 class="panel-title"><a href="game.php?game_id='.$date.'&type='.$season_type.'&year='.$year.'&week='.$week.'">'.$date_f.' '.$year.' - '.$awaycode.' vs. '.$homecode.'</a></h4>
                        </div>
                        <div class="panel-body">
                        	<ul class="nav nav-tabs">
								'.$tabs.'
							</ul>
							<div class="tab-content" style="margin-bottom:0px;">
								<div class="tab-pane fade active in" id="'.$hashtag.'-tab-1">
									<div class="table-responsive">
	                            		<table class="table score" style="margin-bottom:0px;">
											<thead>
												<tr>
													<th>Team</th>
													<th style="text-align: right;">1</th>
													<th style="text-align: right;">2</th>
													<th style="text-align: right;">3</th>
													<th style="text-align: right;">4</th>
													<th style="text-align: right;">Total</th>
												</tr>
											</thead>
											<tbody>
												'.$away_tr.'
													<td style="padding-left: 10px;"><a href="team.php?team='.$awaycode.'"><img src="/assets/img/teams/'.$awaycode.'.png" style="width: 65px;float:left;" /><span style="padding: 5px;vertical-align: middle;font-weight: bold;display: inline-block;"><span style="font-weight:normal;color: #000;">('.$wins["".$awaycode.""].'-'.$losses["".$awaycode.""].'-'.$ties["".$awaycode.""].')</span><br />'.$awayteam_f.'</span></a></td>
													<td style="text-align: right;">'.$awayscore_q1.'</td>
													<td style="text-align: right;">'.$awayscore_q2.'</td>
													<td style="text-align: right;">'.$awayscore_q3.'</td>
													<td style="text-align: right;">'.$awayscore_q4.'</td>
													<td style="text-align: right;"><h4><strong>'.$awayscore.'</h4></strong></td>
												</tr>
												'.$home_tr.'
													<td style="padding-left: 10px;"><a href="team.php?team='.$homecode.'"><img src="/assets/img/teams/'.$homecode.'.png" style="width: 65px;float:left;" /><span style="padding: 5px;vertical-align: middle;font-weight: bold;display: inline-block;"><span style="font-weight:normal;color: #000;">('.$wins["".$homecode.""].'-'.$losses["".$homecode.""].'-'.$ties["".$homecode.""].')</span><br />'.$hometeam_f.'</span></a></td>
													<td style="text-align: right;">'.$homescore_q1.'</td>
													<td style="text-align: right;">'.$homescore_q2.'</td>
													<td style="text-align: right;">'.$homescore_q3.'</td>
													<td style="text-align: right;">'.$homescore_q4.'</td>
													<td style="text-align: right;"><h4><strong>'.$homescore.'</h4></strong></td>
												</tr>
											</tbody>
										</table>
									</div>
									'.$bottom_line.'
									<div class="progress progress-striped active" style="display:none;">
                                		<div class="progress-bar progress-bar-success" style="width: '.$progress.'">'.$progress.'</div>
                            		</div>
                            	</div>
                            	'.$tabbody.'
							</div>
						</div>
                    </div>
                </div>';
    $i++;
}

// Conference menu rank order & builder

// AFC
foreach ($AFCE as $key => $tid) {
	$AFCEs["".$tid.""] = $wins["".$tid.""] - $losses["".$tid.""];
}

foreach ($AFCN as $key => $tid) {
	$AFCNs["".$tid.""] = $wins["".$tid.""] - $losses["".$tid.""];
}

foreach ($AFCS as $key => $tid) {
	$AFCSs["".$tid.""] = $wins["".$tid.""] - $losses["".$tid.""];
} 

foreach ($AFCW as $key => $tid) {
	$AFCWs["".$tid.""] = $wins["".$tid.""] - $losses["".$tid.""];
} 

// NFC
foreach ($NFCE as $key => $tid) {
	$NFCEs["".$tid.""] = $wins["".$tid.""] - $losses["".$tid.""];
}

foreach ($NFCN as $key => $tid) {
	$NFCNs["".$tid.""] = $wins["".$tid.""] - $losses["".$tid.""];
}

foreach ($NFCS as $key => $tid) {
	$NFCSs["".$tid.""] = $wins["".$tid.""] - $losses["".$tid.""];
} 

foreach ($NFCW as $key => $tid) {
	$NFCWs["".$tid.""] = $wins["".$tid.""] - $losses["".$tid.""];
} 

arsort($AFCEs);
arsort($AFCNs);
arsort($AFCSs);
arsort($AFCWs);
arsort($NFCEs);
arsort($NFCNs);
arsort($NFCSs);
arsort($NFCWs);

// AFC
foreach ($AFCEs as $key => $tid) {

	if (!$AFCEflag) { $label = "<div class='info rushing' style='width: 100%;padding-top: 10px;padding-left: 10px;background-color:rgba(0, 0, 0, 0.55);'><small>AFC EAST</small></div>"; } else { $label = ""; }

	$AFCEw = $label.'<div class="info rushing">
					<a href="team.php?team='.$key.'" style="color:#fff;">
						'.$key.'
					</a>
					<small><span style="color:#aaa;">('.$wins["".$key.""].'-'.$losses["".$key.""].'-'.$ties["".$key.""].')</small></span>
                </div>';

    $AFCEa .= '<li>
    				<a href="team.php?team='.$key.'">
    					<span class="badge pull-right '.$key.'colors">'.$wins["".$key.""].'-'.$losses["".$key.""].'-'.$ties["".$key.""].'</span>
						<span>'.$key.' '.$teamname["".$key.""].'</span>
					</a>
				</li>';
    $confleaders[$w] = $AFCEw;
    $w++;
    $AFCEflag = true;
}

foreach ($AFCNs as $key => $tid) {

	if (!$AFCNflag) { $label = "<div class='info rushing' style='width: 100%;padding-top: 10px;padding-left: 10px;background-color:rgba(0, 0, 0, 0.55);'><small>AFC NORTH</small></div>"; } else { $label = ""; }

	$AFCNw = $label.'<div class="info rushing">
					<a href="team.php?team='.$key.'" style="color:#fff;">
						'.$key.'
					</a>
					<small><span style="color:#aaa;">('.$wins["".$key.""].'-'.$losses["".$key.""].'-'.$ties["".$key.""].')</small></span>
                </div>';
    $AFCNa .= '<li>
    				<a href="team.php?team='.$key.'">
    					<span class="badge pull-right '.$key.'colors">'.$wins["".$key.""].'-'.$losses["".$key.""].'-'.$ties["".$key.""].'</span>
						<span>'.$key.' '.$teamname["".$key.""].'</span>
					</a>
				</li>';
    $confleaders[$w] = $AFCNw;
    $w++;
    $AFCNflag = true;
}

foreach ($AFCSs as $key => $tid) {

	if (!$AFCSflag) { $label = "<div class='info rushing' style='width: 100%;padding-top: 10px;padding-left: 10px;background-color:rgba(0, 0, 0, 0.55);'><small>AFC SOUTH</small></div>"; } else { $label = ""; }

	$AFCSw = $label.'<div class="info rushing">
					<a href="team.php?team='.$key.'" style="color:#fff;">
						'.$key.'
					</a>
					<small><span style="color:#aaa;">('.$wins["".$key.""].'-'.$losses["".$key.""].'-'.$ties["".$key.""].')</small></span>
                </div>';
    $AFCSa .= '<li>
    				<a href="team.php?team='.$key.'">
    					<span class="badge pull-right '.$key.'colors">'.$wins["".$key.""].'-'.$losses["".$key.""].'-'.$ties["".$key.""].'</span>
						<span>'.$key.' '.$teamname["".$key.""].'</span>
					</a>
				</li>';
    $confleaders[$w] = $AFCSw;
    $w++;
    $AFCSflag = true;
} 

foreach ($AFCWs as $key => $tid) {

	if (!$AFCWflag) { $label = "<div class='info rushing' style='width: 100%;padding-top: 10px;padding-left: 10px;background-color:rgba(0, 0, 0, 0.55);'><small>AFC WEST</small></div>"; } else { $label = ""; }

	$AFCWw = $label.'<div class="info rushing">
					<a href="team.php?team='.$key.'" style="color:#fff;">
						'.$key.'
					</a>
					<small><span style="color:#aaa;">('.$wins["".$key.""].'-'.$losses["".$key.""].'-'.$ties["".$key.""].')</small></span>
                </div>';
    $AFCWa .= '<li>
    				<a href="team.php?team='.$key.'">
    					<span class="badge pull-right '.$key.'colors">'.$wins["".$key.""].'-'.$losses["".$key.""].'-'.$ties["".$key.""].'</span>
						<span>'.$key.' '.$teamname["".$key.""].'</span>
					</a>
				</li>';
    $confleaders[$w] = $AFCWw;
    $w++;
    $AFCWflag = true;
} 

// NFC
foreach ($NFCEs as $key => $tid) {

	if (!$NFCEflag) { $label = "<div class='info passing' style='width: 100%;padding-top: 10px;padding-left: 10px;background-color:rgba(0, 0, 0, 0.55);'><small>NFC EAST</small></div>"; } else { $label = ""; }

	$NFCEw = $label.'<div class="info passing">
					<a href="team.php?team='.$key.'" style="color:#fff;">
						'.$key.'
					</a>
					<small><span style="color:#aaa;">('.$wins["".$key.""].'-'.$losses["".$key.""].'-'.$ties["".$key.""].')</small></span>
                </div>';
    $NFCEa .= '<li>
    				<a href="team.php?team='.$key.'">
    					<span class="badge pull-right '.$key.'colors">'.$wins["".$key.""].'-'.$losses["".$key.""].'-'.$ties["".$key.""].'</span>
						<span>'.$key.' '.$teamname["".$key.""].'</span>
					</a>
				</li>';
    $confleaders[$w] = $NFCEw;
    $w++;
    $NFCEflag = true;
}

foreach ($NFCNs as $key => $tid) {
	if (!$NFCNflag) { $label = "<div class='info passing' style='width: 100%;padding-top: 10px;padding-left: 10px;background-color:rgba(0, 0, 0, 0.55);'><small>NFC NORTH</small></div>"; } else { $label = ""; }

	$NFCNw = $label.'<div class="info passing">
					<a href="team.php?team='.$key.'" style="color:#fff;">
						'.$key.'
					</a>
					<small><span style="color:#aaa;">('.$wins["".$key.""].'-'.$losses["".$key.""].'-'.$ties["".$key.""].')</small></span>
                </div>';
    $NFCNa .= '<li>
    				<a href="team.php?team='.$key.'">
    					<span class="badge pull-right '.$key.'colors">'.$wins["".$key.""].'-'.$losses["".$key.""].'-'.$ties["".$key.""].'</span>
						<span>'.$key.' '.$teamname["".$key.""].'</span>
					</a>
				</li>';
    $confleaders[$w] = $NFCNw;
    $w++;
    $NFCNflag = true;
}

foreach ($NFCSs as $key => $tid) {
	if (!$NFCSflag) { $label = "<div class='info passing' style='width: 100%;padding-top: 10px;padding-left: 10px;background-color:rgba(0, 0, 0, 0.55);'><small>NFC SOUTH</small></div>"; } else { $label = ""; }

	$NFCSw = $label.'<div class="info passing">
					<a href="team.php?team='.$key.'" style="color:#fff;">
						'.$key.'
					</a>
					<small><span style="color:#aaa;">('.$wins["".$key.""].'-'.$losses["".$key.""].'-'.$ties["".$key.""].')</small></span>
                </div>';
    $NFCSa .= '<li>
    				<a href="team.php?team='.$key.'">
    					<span class="badge pull-right '.$key.'colors">'.$wins["".$key.""].'-'.$losses["".$key.""].'-'.$ties["".$key.""].'</span>
						<span>'.$key.' '.$teamname["".$key.""].'</span>
					</a>
				</li>';
    $confleaders[$w] = $NFCSw;
    $w++;
    $NFCSflag = true;
	
} 

foreach ($NFCWs as $key => $tid) {
	if (!$NFCWflag) { $label = "<div class='info passing' style='width: 100%;padding-top: 10px;padding-left: 10px;background-color:rgba(0, 0, 0, 0.55);'><small>NFC WEST</small></div>"; } else { $label = ""; }

	$NFCWw = $label.'<div class="info passing">
					<a href="team.php?team='.$key.'" style="color:#fff;">
						'.$key.'
					</a>
					<small><span style="color:#aaa;">('.$wins["".$key.""].'-'.$losses["".$key.""].'-'.$ties["".$key.""].')</small></span>
                </div>';
    $NFCWa .= '<li>
    				<a href="team.php?team='.$key.'">
    					<span class="badge pull-right '.$key.'colors">'.$wins["".$key.""].'-'.$losses["".$key.""].'-'.$ties["".$key.""].'</span>
						<span>'.$key.' '.$teamname["".$key.""].'</span>
					</a>
				</li>';
    $confleaders[$w] = $NFCWw;
    $w++;
    $NFCWflag = true;
} 

if ($week > 1) {
	$weekfloat = $week - 1;
	$lastweek = '?type='.$season_type.'&year='.$year.'&week='.$weekfloat;
}
if ($year > 2010) {
	$yearfloat = $year - 1;
	$lyear = '?type='.$season_type.'&year='.$yearfloat.'&week='.$week;
}

require ASSETS . 'index-page' . EXT;