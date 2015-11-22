<?php

/**
 * statstrac - open NFL statistics platform
 */

define('DS', DIRECTORY_SEPARATOR);
define('PATH', dirname(__FILE__) . DS);
define('CACHE', PATH . 'cache' . DS);
define('ASSETS', PATH . 'assets' . DS);
define('EXT', '.php');

require PATH . 'config' . EXT;

if (isset($_GET["player"])) { $player_id = $_GET["player"]; }
if (isset($_GET["player_id"])) { $player_id = $_GET["player_id"]; }
if (isset($_GET["week"])) { $week = $_GET["week"]; }
if (isset($_GET["year"])) { $year = $_GET["year"]; }
if (isset($_GET["type"])) { $season_type = $_GET["type"]; }
if (isset($_GET["game"])) { $game = $_GET["game"]; $gamecontext = '&gsis_id=eq.'.$game; }
if (isset($_GET["game_id"])) { $game = $_GET["game_id"]; $gamecontext = '&gsis_id=eq.'.$game; }
if (isset($_GET["play"])) { $play = $_GET["play"]; $playcontext = '&play_id=eq.'.$play; }
if (isset($_GET["play_id"])) { $play = $_GET["play_id"]; $playcontext = '&play_id=eq.'.$play; }
if (isset($_GET["drive"])) { $drive = $_GET["drive"]; $drivecontext = '&drive_id=eq.'.$drive; }
if (isset($_GET["drive_id"])) { $drive = $_GET["drive_id"]; $drivecontext = '&drive_id=eq.'.$drive; }
if (!isset($player_id)) { header('Location: /404.html'); die(); }

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
if (!isset($week)) {
	$week = $objmeta[0]["week"];
}
if (!isset($year)) {
	$year = $objmeta[0]["season_year"];
}
if (!isset($season_type)) {
	$season_type = $objmeta[0]["season_type"];
}
if (!isset($game)) {
	$gamecontext = '';
}
if (!isset($play)) {
	$playcontext = '';
}
if (!isset($drive)) {
	$drivecontext = '';
}

$context = $gamecontext.$playcontext.$drivecontext;
$last_update = $objmeta[0]["last_roster_download"];
$date_u = strtotime($last_update);
$date_u = "Last updated ".date('D\, M j Y', $date_u);
$sort = '&order=start_time.asc';
$i = 0;
// NAV FUNCTIONS - WEEKS
$grab_weeks = CACHE . 'game/game?season_type=eq.'.$season_type.'&season_year=eq.'.$year.'&select=week,season_type,gsis_id,finished&order=start_time.asc.json';
if (file_exists($grab_weeks)) {
	$grabweeksdata = file_get_contents($grab_weeks);
	$grabweeksobj = json_decode($grabweeksdata, true);
} else {
	$msg = file_get_contents($postgrest . 'game?season_type=eq.'.$season_type.'&season_year=eq.'.$year.'&select=week,season_type,gsis_id,finished&order=start_time.asc');
	$f = fopen($grab_weeks, "w+");
	fwrite($f, $msg);
	fclose($f);
	$grabweeksobj = json_decode($msg, true);
}
foreach ($grabweeksobj as $key => $val) {
	if ($val["finished"] === true) {
		$finishedgames[$i] = $val["gsis_id"];
	}
	$totalweeks = $val["week"];
	$totalseasontypes[$i] = $val["season_type"];
	$i++;
}
for ($i = 1; $i < $totalweeks+1; $i++) {
	if ($i == $week) {
		$weeksdropdown .= '<li class="active"><a href="/?type='.$season_type.'&year='.$year.'&week='.$i.'">Week '.$i.'</a></li>';
	} else {
		$weeksdropdown .= '<li><a href="/?type='.$season_type.'&year='.$year.'&week='.$i.'">Week '.$i.'</a></li>';
	}
}
$i = 0;

// NAV FUNCTIONS - YEARS
for ($i = $startyear; $i < $currentyear + 1; $i++) {
	if ($i == $year) {
		$yearsdropdown .= '<li class="active"><a href="/?type='.$season_type.'&year='.$i.'">'.$i.'</a></li>';
	} else {
		$yearsdropdown .= '<li><a href="/?type='.$season_type.'&year='.$i.'">'.$i.'</a></li>';
	}
}

// NAV FUNCTIONS - SEASON TYPES
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
			$conference["".$val["team_id"].""] = 'AFC East Conference';
		} else if ($val["team_id"] === "CIN" || $val["team_id"] === "CLE" || $val["team_id"] === "PIT" || $val["team_id"] === "BAL") {
			$AFCN[$t] = $val["team_id"];
			$conference["".$val["team_id"].""] = 'AFC North Conference';
		} else if ($val["team_id"] === "JAC" || $val["team_id"] === "TEN" || $val["team_id"] === "IND" || $val["team_id"] === "HOU") {
			$AFCS[$t] = $val["team_id"];
			$conference["".$val["team_id"].""] = 'AFC South Conference';
		} else if ($val["team_id"] === "DEN" || $val["team_id"] === "OAK" || $val["team_id"] === "SD" || $val["team_id"] === "KC") {
			$AFCW[$t] = $val["team_id"];
			$conference["".$val["team_id"].""] = 'AFC West Conference';
		} else if ($val["team_id"] === "DAL" || $val["team_id"] === "WAS" || $val["team_id"] === "NYG" || $val["team_id"] === "PHI") {
			$NFCE[$t] = $val["team_id"];
			$conference["".$val["team_id"].""] = 'NFC East Conference';
		} else if ($val["team_id"] === "GB" || $val["team_id"] === "MIN" || $val["team_id"] === "DET" || $val["team_id"] === "CHI") {
			$NFCN[$t] = $val["team_id"];
			$conference["".$val["team_id"].""] = 'NFC North Conference';
		} else if ($val["team_id"] === "ATL" || $val["team_id"] === "CAR" || $val["team_id"] === "TB" || $val["team_id"] === "NO") {
			$NFCS[$t] = $val["team_id"];
			$conference["".$val["team_id"].""] = 'NFC South Conference';
		} else if ($val["team_id"] === "ARI" || $val["team_id"] === "STL" || $val["team_id"] === "SF" || $val["team_id"] === "SEA") {
			$NFCW[$t] = $val["team_id"];
			$conference["".$val["team_id"].""] = 'NFC West Conference';
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
		$teamname["".$val["team_id"].""] = $val["name"];
		$teamcity["".$val["team_id"].""] = $val["city"];
		$total_teamid[$t] = $val["team_id"];
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

$i = 0;
$json_string = 'http://localhost:2096/player?player_id=eq.'.$player_id;
$jsondata = file_get_contents($json_string);
$obj = json_decode($jsondata,true);
foreach ($obj as $key => $val) {
	$gsis_name = $val["gsis_name"];
	$full_name = $val["full_name"];
	$last_name = $val["last_name"];
	$first_name = $val["first_name"];
	$uniform_number = $val["uniform_number"];
	$position = $val["position"];
	$status = $val["status"];
	$height = $val["height"];
	$weight = $val["weight"];
	$birthdate = $val["birthdate"];
	$exp = $val["years_pro"];
	$college = $val["college"];
	$team = $val["team"];
	$profile_url = $val["profile_url"];
}




					 $passing_stats_away = '';
			$passing_stats_home = '';
			$stats_table_away = '';

			$rushing_stats_away = '';
			$rushing_stats_home = '';

			$receiving_stats_away = '';
			$receiving_stats_home = '';

			$fumble_stats_away = '';
			$fumble_stats_home = '';

			$kicking_stats_away = '';
			$kicking_stats_home = '';

			$punting_stats_away = '';
			$punting_stats_home = '';

			$kickret_stats_away = '';
			$kickret_stats_home = '';

			$puntret_stats_away = '';
			$puntret_stats_home = '';

			$defense_stats_away = '';
			$defense_stats_home = '';

			$summary_passing_stats_away = '';
			$summary_passing_stats_home = '';

			$summary_rushing_stats_away = '';
			$summary_rushing_stats_home = '';

			$summary_receiving_stats_away = '';
			$summary_receiving_stats_home = '';

			$summary_fumble_stats_home = '';

			$progress_updater = '';

			    $receiving_rec_away = 0;
				$receiving_yds_away = 0;
				$receiving_tds_away = 0;
				$receiving_yac_away = 0;

				$passing_att_away = 0;
				$passing_cmp_away = 0;
				$passing_yds_away = 0;
				$passing_tds_away = 0;
				$passing_int_away = 0;
				$passing_id_away = 0;

				    $rushing_att_away = 0;
					$rushing_yds_away = 0;
					$rushing_tds_away = 0;
					$rushing_loss_away = 0;

					$fumbles_lost_away = 0;
					$fumbles_rec_away = 0;
					$fumbles_rec_tds_away = 0;
					$fumbles_rec_yds_away = 0;
					$fumbles_tot_away = 0;
					$fumbles_frec_away = 0;
					$fumbles_frec_yds_away = 0;

					$kicking_fga_away = 0;
					$kicking_fgm_away = 0;
					$kicking_xpa_away = 0;
					$kicking_xpmade_away = 0;
					$kicking_yds_away = 0;

					$kickret_ret_away = 0;
						$kickret_tds_away = 0;
						$kickret_yds_away = 0;

					$passing_stats_away_body= '';
					$rushing_stats_away_body= '';
										$receiving_stats_away_body= '';
										$fumbles_stats_away_body= '';
										$kicking_stats_away_body= '';
										$punting_stats_away_body= '';
										$kickret_stats_away_body= '';
										$puntret_stats_away_body= '';
										$defense_stats_away_body= '';

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

			$awayteam_f = $team;
			$hometeam_f = $team;

		$drives_stats = '';

		$total_away_yards_gained = 0;
		$total_away_penalty_yards = 0;

		$total_home_yards_gained = 0;
		$total_home_penalty_yards = 0;

		$total_agg_rushing_yds_away = 0;
		$total_agg_passing_yds_away = 0;
		$total_agg_passing_sk_yds_away = 0;

		$total_agg_passing_plays_away = 0;
		$total_agg_rushing_plays_away = 0;

		$total_agg_rushing_yds_home = 0;
		$total_agg_passing_yds_home = 0;
		$total_agg_passing_sk_yds_home = 0;

		$total_agg_passing_plays_home = 0;
		$total_agg_rushing_plays_home = 0;

		$total_agg_tds_away = 0;
		$total_agg_tds_passing_away = 0;
		$total_agg_tds_receiving_away = 0;
		$total_agg_tds_rushing_away = 0;
		$total_agg_tds_int_away = 0;
		$total_agg_tds_frec_away = 0;
		$total_agg_tds_kickret_away = 0;
		$total_agg_tds_puntret_away = 0;

		$total_agg_tds_home = 0;
		$total_agg_tds_passing_home = 0;
		$total_agg_tds_receiving_home = 0;
		$total_agg_tds_rushing_home = 0;
		$total_agg_tds_int_home = 0;
		$total_agg_tds_frec_home = 0;
		$total_agg_tds_kickret_home = 0;
		$total_agg_tds_puntret_home = 0;

		$firstdowns_passing_away = 0;
		$firstdowns_rushing_away = 0;
		$firstdowns_penalty_away = 0;
		$firstdowns_away_total = 0;
		$third_down_att_away = 0;
		$third_down_conv_away = 0;
		$third_down_eff_away = 0;
		$fourth_down_att_away = 0;
		$fourth_down_conv_away = 0;
		$fourth_down_eff_away = 0;
		$penalty_yards_away = 0;
		$penalty_away = 0;

		$firstdowns_passing_home = 0;
		$firstdowns_rushing_home = 0;
		$firstdowns_penalty_home = 0;
		$firstdowns_home_total = 0;
		$third_down_att_home = 0;
		$third_down_conv_home = 0;
		$third_down_eff_home = 0;
		$fourth_down_att_home = 0;
		$fourth_down_conv_home = 0;
		$fourth_down_eff_home = 0;
		$penalty_yards_home = 0;
		$penalty_home = 0;
		$total_agg_passing_yds_team = 0;
		$passing_yds_cmp_save_1 = null;
		$passing_yds_cmp_save_2 = null;
		$passing_yds_incmp_save_1 = null;
		$passing_yds_incmp_save_2 = null;

		$passing_yds_cmp_save_3 = null;
		$passing_yds_cmp_save_4 = null;
		$passing_yds_incmp_save_3 = null;
		$passing_yds_incmp_save_4 = null;

		$passing_yds_cmp_save_5 = null;
		$passing_yds_cmp_save_6 = null;
		$passing_yds_incmp_save_5 = null;
		$passing_yds_incmp_save_6 = null;

		$punting_tot_away = 0;
		$punting_yds_away = 0;
		$punting_i20_away = 0;
		$punting_avg_away = 0;

		$defense_ast_away = 0;
		$defense_tkl_away = 0;
		$defense_sk_away = 0;
		$defense_int_away = 0;
		$defense_ffum_away = 0;
		$defense_int_tds_away = 0;
		$defense_frec_tds_away = 0;

		$puntret_tot_away = 0;
		$puntret_tds_away = 0;
		$puntret_yds_away = 0;
		$puntret_avg_away = 0;


	// PASSING AWAY

		if ($position === "QB") {

			foreach ($finishedgames as $game => $id) {

					$passing_string_away = 'http://localhost:2096/play_player?gsis_id=eq.'.$id.'&player_id=eq.'.$player_id.'&team=eq.'.$team.'&select=team,player_id,passing_att,passing_cmp,passing_cmp_air_yds,passing_incmp,passing_incmp_air_yds,passing_int,passing_sk,passing_sk_yds,passing_tds,passing_twopta,passing_twoptm,passing_twoptmissed,passing_yds&passing_att=not.eq.0&order=team.asc,player_id.desc';
					$jsonpassingaway = file_get_contents($passing_string_away);
					$objpassingaway = json_decode($jsonpassingaway,true);

					foreach ($objpassingaway as $pass => $stat) {
					    $passing_id_away = $stat["player_id"];
						$passing_att_away = $passing_att_away + $stat["passing_att"];
						$passing_cmp_away = $passing_cmp_away + $stat["passing_cmp"];
						if ($passing_yds_cmp_save_1 == NULL && $stat["passing_cmp_air_yds"] > 0) {
							$passing_yds_cmp_save_1 = $stat["passing_cmp_air_yds"];
						} else if ($passing_yds_cmp_save_2 == NULL && $stat["passing_cmp_air_yds"] > 0) {
							$passing_yds_cmp_save_2 = $stat["passing_cmp_air_yds"];
						} else if ($passing_yds_incmp_save_1 == NULL && $stat["passing_incmp_air_yds"] > 0) {
							$passing_yds_incmp_save_1 = $stat["passing_incmp_air_yds"];
						} else if ($passing_yds_incmp_save_2 == NULL && $stat["passing_incmp_air_yds"] > 0) {
							$passing_yds_incmp_save_2 = $stat["passing_incmp_air_yds"];
						} else if ($passing_yds_cmp_save_3 == NULL && $stat["passing_cmp_air_yds"] > 0) {
							$passing_yds_cmp_save_3 = $stat["passing_cmp_air_yds"];
						} else if ($passing_yds_cmp_save_4 == NULL && $stat["passing_cmp_air_yds"] > 0) {
							$passing_yds_cmp_save_4 = $stat["passing_cmp_air_yds"];
						} else if ($passing_yds_incmp_save_3 == NULL && $stat["passing_incmp_air_yds"] > 0) {
							$passing_yds_incmp_save_3 = $stat["passing_incmp_air_yds"];
						} else if ($passing_yds_incmp_save_4 == NULL && $stat["passing_incmp_air_yds"] > 0) {
							$passing_yds_incmp_save_4 = $stat["passing_incmp_air_yds"];
						} else if ($passing_yds_cmp_save_5 == NULL && $stat["passing_cmp_air_yds"] > 0) {
							$passing_yds_cmp_save_5 = $stat["passing_cmp_air_yds"];
						} else if ($passing_yds_cmp_save_6 == NULL && $stat["passing_cmp_air_yds"] > 0) {
							$passing_yds_cmp_save_6 = $stat["passing_cmp_air_yds"];
						} else if ($passing_yds_incmp_save_5 == NULL && $stat["passing_incmp_air_yds"] > 0) {
							$passing_yds_incmp_save_5 = $stat["passing_incmp_air_yds"];
						} else if ($passing_yds_incmp_save_6 == NULL && $stat["passing_incmp_air_yds"] > 0) {
							$passing_yds_incmp_save_6 = $stat["passing_incmp_air_yds"];
						}
						$passing_yds_away = $passing_yds_away + $stat["passing_yds"];
						$passing_tds_away = $passing_tds_away + $stat["passing_tds"];
						$passing_int_away = $passing_int_away + $stat["passing_int"];
						$total_agg_tds_passing_away = $total_agg_tds_passing_away + $stat["passing_tds"];
					}

			}

				
					$passing_stats_away .= "<tr>
									 <td>".$passing_cmp_away."/".$passing_att_away."</td>
									 <td>".$passing_yds_away."</td>
									 <td>".$passing_tds_away."</td>
									 <td>".$passing_int_away."</td></tr>";

					$passing_stats_away_body = '<tr class="active">
													<th>Passing</th>
													<th>CP/AT</th>
													<th>YDS</th>
													<th>TD</th>
													<th>INT</th></tr>'.$passing_stats_away;

					//$master_passing_yds_away["".$objplayeraway[0]["first_name"][0].".".$objplayeraway[0]["last_name"]." ".$player_id." ".$passing_id_away.""] = $passing_yds_away;
					//$master_passing_yds_away_summary["".$passing_id_away.""] = $passing_stats_away_sum;

		}

					/*// PASSING HOME

					$passing_string_home = 'http://localhost:2096/play_player?player_id=eq.'.$player_id.'&team=eq.'.$team.'&select=team,player_id,passing_att,passing_cmp,passing_cmp_air_yds,passing_incmp,passing_incmp_air_yds,passing_int,passing_sk,passing_sk_yds,passing_tds,passing_twopta,passing_twoptm,passing_twoptmissed,passing_yds&passing_att=not.eq.0&order=team.asc,player_id.desc';
					$jsonpassinghome = file_get_contents($passing_string_home);
					$objpassinghome = json_decode($jsonpassinghome,true);

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
						$total_agg_tds_passing_home = $total_agg_tds_passing_home + $passstat["passing_tds"];
					}

					$player_string_home = 'http://localhost:2096/player?player_id=eq.'.$passing_id_home;
					$jsonplayerhome = file_get_contents($player_string_home);
					$objplayerhome = json_decode($jsonplayerhome,true);
					$passing_stats_home .= "<tr><td><a href='player.php?player_id=".$passing_id_home."'>".$objplayerhome[0]["gsis_name"]."</a></td>
									 <td>".$passing_cmp_home."/".$passing_att_home."</td>
									 <td>".$passing_yds_home."</td>
									 <td>".$passing_tds_home."</td>
									 <td>".$passing_int_home."</td></tr>";
					$passing_stats_home_sum = "<tr><td><a href='player.php?player_id=".$passing_id_home."'>".$objplayerhome[0]["gsis_name"]."</a></td>
									 <td>".$passing_cmp_home."/".$passing_att_home."</td>
									 <td>".$passing_yds_home."</td>
									 <td>".$passing_tds_home."</td>
									 <td>".$passing_int_home."</td></tr>";

					$passing_stats_home_body = '<tr class="active">
													<th>Passing</th>
													<th>CP/AT</th>
													<th>YDS</th>
													<th>TD</th>
													<th>INT</th></tr>'.$passing_stats_home;

					$master_passing_yds_home["".$objplayerhome[0]["gsis_name"]." ".$player_id." ".$passing_id_home.""] = $passing_yds_home;
					$master_passing_yds_home_summary["".$passing_id_home.""] = $passing_stats_home_sum;*/

					// RUSHING AWAY
		if ($position === "QB" || $position === "RB") {

			foreach ($finishedgames as $game => $id) {

					$rushing_string_away = 'http://localhost:2096/play_player?gsis_id=eq.'.$id.'&player_id=eq.'.$player_id.'&team=eq.'.$team.'&select=team,player_id,rushing_att,rushing_loss,rushing_loss_yds,rushing_tds,rushing_twopta,rushing_twoptm,rushing_twoptmissed,rushing_yds&rushing_att=not.eq.0&order=team.asc,player_id.desc';
					    $jsonrushingaway = file_get_contents($rushing_string_away);
					    $objrushingaway = json_decode($jsonrushingaway,true);
					    foreach ($objrushingaway as $rush => $rushstat) {
							$rushing_att_away = $rushing_att_away + $rushstat["rushing_att"];
							$rushing_yds_away = $rushing_yds_away + $rushstat["rushing_yds"];
							$rushing_tds_away = $rushing_tds_away + $rushstat["rushing_tds"];
							$rushing_loss_away = $rushing_loss_away + $rushstat["rushing_loss"];
						}

			}
						if ($rushing_att_away !== 0 || $rushing_yds_away !== 0 || $rushing_tds_away !== 0 || $rushing_loss_away !== 0) {
							$total_agg_tds_rushing_away = $total_agg_tds_rushing_away + $rushing_tds_away;
							$rushing_stats_away .= "<tr>
										 <td>".$rushing_att_away."</td>
										 <td>".$rushing_yds_away."</td>
										 <td>".$rushing_tds_away."</td>
										 <td>".$rushing_loss_away."</td></tr>";
							$rushing_stats_away_sum = "<tr><td><a href='player.php?player_id=".$player_save_id."'>".$name."</a></td>
										 <td>".$rushing_att_away."</td>
										 <td>".$rushing_yds_away."</td>
										 <td>".$rushing_tds_away."</td>
										 <td>".$rushing_loss_away."</td></tr>";

							//$master_rushing_yds_away["".$name." ".$player_id." ".$player_save_id.""] = $rushing_yds_away;
							//$master_rushing_yds_away_summary["".$player_save_id.""] = $rushing_stats_away_sum;
						}

					$rushing_stats_away_body = '<tr class="active">
													<th>Rushing</th>
													<th>ATT</th>
													<th>YDS</th>
													<th>TD</th>
													<th>LOSS</th></tr>'.$rushing_stats_away;
		}
					/*// RUSHING HOME

					$rushing_string_home = 'http://localhost:2096/play_player?player_id=eq.'.$player_id.'&team=eq.'.$team.'&select=team,player_id,rushing_att,rushing_loss,rushing_loss_yds,rushing_tds,rushing_twopta,rushing_twoptm,rushing_twoptmissed,rushing_yds&rushing_att=not.eq.0&order=team.asc,player_id.desc';
					$jsonrushinghome = file_get_contents($rushing_string_home);
					$objrushinghome = json_decode($jsonrushinghome,true);

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
						$player_string_home = 'http://localhost:2096/player?player_id=eq.'.$rushstat;
						$player_save_id = $rushstat;
						$jsonplayerhome = file_get_contents($player_string_home);
						$objplayerhome = json_decode($jsonplayerhome,true);
						$name = $objplayerhome[0]["first_name"][0].".".$objplayerhome[0]["last_name"];
					    $rushing_string_home = 'http://localhost:2096/play_player?player_id=eq.'.$player_id.'&team=eq.'.$team.'&player_id=eq.'.$rushstat.'&select=team,player_id,rushing_att,rushing_loss,rushing_loss_yds,rushing_tds,rushing_twopta,rushing_twoptm,rushing_twoptmissed,rushing_yds&rushing_att=not.eq.0&order=team.asc,player_id.desc';
					    $jsonrushinghome = file_get_contents($rushing_string_home);
					    $objrushinghome = json_decode($jsonrushinghome,true);
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
						if ($rushing_att_home !== 0 || $rushing_yds_home !== 0 || $rushing_tds_home !== 0 || $rushing_loss_home !== 0) {
							$total_agg_tds_rushing_home = $total_agg_tds_rushing_home + $rushing_tds_home;
							$rushing_stats_home .= "<tr><td><a href='player.php?player_id=".$player_save_id."'>".$name."</a></td>
										 <td>".$rushing_att_home."</td>
										 <td>".$rushing_yds_home."</td>
										 <td>".$rushing_tds_home."</td>
										 <td>".$rushing_loss_home."</td></tr>";
							$rushing_stats_home_sum = "<tr><td><a href='player.php?player_id=".$player_save_id."'>".$name."</a></td>
										 <td>".$rushing_att_home."</td>
										 <td>".$rushing_yds_home."</td>
										 <td>".$rushing_tds_home."</td>
										 <td>".$rushing_loss_home."</td></tr>";

							$master_rushing_yds_home["".$name." ".$player_id." ".$player_save_id.""] = $rushing_yds_home;
							$master_rushing_yds_home_summary["".$player_save_id.""] = $rushing_stats_home_sum;
						}
					}

					$rushing_stats_home_body = '<tr class="active">
													<th>Rushing</th>
													<th>ATT</th>
													<th>YDS</th>
													<th>TD</th>
													<th>LOSS</th></tr>'.$rushing_stats_home;*/
		if ($position === "WR" || $position === "TE" || $position === "RB") {

			foreach ($finishedgames as $game => $id) {
					// RECEIVING AWAY

					$receiving_string_away = 'http://localhost:2096/play_player?gsis_id=eq.'.$id.'&player_id=eq.'.$player_id.'&team=eq.'.$team.'&select=team,player_id,receiving_rec,receiving_tar,receiving_tds,receiving_twopta,receiving_twoptm,receiving_twoptmissed,receiving_yac_yds,receiving_yds';

					    $jsonreceivingaway = file_get_contents($receiving_string_away);
					    $objreceivingaway = json_decode($jsonreceivingaway,true);
					    foreach ($objreceivingaway as $receive => $receivestat) {
							$receiving_rec_away = $receiving_rec_away + $receivestat["receiving_rec"];
							$receiving_yds_away = $receiving_yds_away + $receivestat["receiving_yds"];
							$receiving_tds_away = $receiving_tds_away + $receivestat["receiving_tds"];
							$receiving_yac_away = $receiving_yac_away + $receivestat["receiving_yac_yds"];
						}
			}

			if ($receiving_rec_away !== 0 || $receiving_yds_away !== 0 || $receiving_tds_away !== 0 || $receiving_yac_away !== 0) {
				$total_agg_tds_receiving_away = $total_agg_tds_receiving_away + $receiving_tds_away;
				$receiving_stats_away .= "<tr></td>
										 <td>".$receiving_rec_away."</td>
										 <td>".$receiving_yds_away."</td>
										 <td>".$receiving_tds_away."</td>
										 <td>".$receiving_yac_away."</td></tr>";
							$receiving_stats_away_sum = "<tr><td><a href='player.php?player_id=".$player_save_id."'>".$name."</a></td>
										 <td>".$receiving_rec_away."</td>
										 <td>".$receiving_yds_away."</td>
										 <td>".$receiving_tds_away."</td>
										 <td>".$receiving_yac_away."</td></tr>";

				//$master_receiving_yds_away["".$name." ".$player_id." ".$player_save_id.""] = $receiving_yds_away;
				//$master_receiving_yds_away_summary["".$player_save_id.""] = $receiving_stats_away_sum;
			}

			$receiving_stats_away_body = '<tr class="active">
													<th>Receiving</th>
													<th>REC</th>
													<th>YDS</th>
													<th>TD</th>
													<th>YAC</th></tr>'.$receiving_stats_away;
	}
					/*// RECEIVING HOME

					$receiving_string_home = 'http://localhost:2096/play_player?player_id=eq.'.$player_id.'&team=eq.'.$team.'&select=team,player_id,receiving_rec,receiving_tar,receiving_tds,receiving_twopta,receiving_twoptm,receiving_twoptmissed,receiving_yac_yds,receiving_yds&receiving_rec=not.eq.0&order=team.asc,player_id.desc';
					$jsonreceivinghome = file_get_contents($receiving_string_home);
					$objreceivinghome = json_decode($jsonreceivinghome,true);

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
						$player_string_home = 'http://localhost:2096/player?player_id=eq.'.$receivestat;
						$player_save_id = $receivestat;
						$jsonplayerhome = file_get_contents($player_string_home);
						$objplayerhome = json_decode($jsonplayerhome,true);
						$name = $objplayerhome[0]["first_name"][0].".".$objplayerhome[0]["last_name"];
					    $receiving_string_home = 'http://localhost:2096/play_player?player_id=eq.'.$player_id.'&team=eq.'.$team.'&player_id=eq.'.$receivestat.'&select=team,player_id,receiving_rec,receiving_tar,receiving_tds,receiving_twopta,receiving_twoptm,receiving_twoptmissed,receiving_yac_yds,receiving_yds&order=team.asc,player_id.desc';
					    $jsonreceivinghome = file_get_contents($receiving_string_home);
					    $objreceivinghome = json_decode($jsonreceivinghome,true);
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
						if ($receiving_rec_home !== 0 || $receiving_yds_home !== 0 || $receiving_tds_home !== 0 || $receiving_yac_home !== 0) {
							$total_agg_tds_receiving_home = $total_agg_tds_receiving_home + $receiving_tds_home;
							$receiving_stats_home .= "<tr><td><a href='player.php?player_id=".$player_save_id."'>".$name."</a></td>
										 <td>".$receiving_rec_home."</td>
										 <td>".$receiving_yds_home."</td>
										 <td>".$receiving_tds_home."</td>
										 <td>".$receiving_yac_home."</td></tr>";
							$receiving_stats_home_sum = "<tr><td><a href='player.php?player_id=".$player_save_id."'>".$name."</a></td>
										 <td>".$receiving_rec_home."</td>
										 <td>".$receiving_yds_home."</td>
										 <td>".$receiving_tds_home."</td>
										 <td>".$receiving_yac_home."</td></tr>";

							$master_receiving_yds_home["".$name." ".$player_id." ".$player_save_id.""] = $receiving_yds_home;
							$master_receiving_yds_home_summary["".$player_save_id.""] = $receiving_stats_home_sum;
						}
					}

					$receiving_stats_home_body = '<tr class="active">
													<th>Receiving</th>
													<th>REC</th>
													<th>YDS</th>
													<th>TD</th>
													<th>YAC</th></tr>'.$receiving_stats_home;*/

					// FUMBLES away


			foreach ($finishedgames as $game => $id) {

						
						$fumble_string_away = 'http://localhost:2096/play_player?gsis_id=eq.'.$id.'&player_id=eq.'.$player_id.'&team=eq.'.$team.'&select=fumbles_lost,fumbles_rec,fumbles_rec_tds,fumbles_tot,fumbles_rec_yds,defense_frec,defense_frec_yds';
						$jsonfumbleaway = file_get_contents($fumble_string_away);
						$objfumbleaway = json_decode($jsonfumbleaway,true);
						foreach ($objfumbleaway as $fumble => $fumblestat) {
							$fumbles_lost_away = $fumbles_lost_away + $fumblestat["fumbles_lost"];
							$fumbles_rec_away = $fumbles_rec_away + $fumblestat["fumbles_rec"];
							$fumbles_rec_tds_away = $fumbles_rec_tds_away + $fumblestat["fumbles_rec_tds"];
							$fumbles_rec_yds_away = $fumbles_rec_yds_away + $fumblestat["fumbles_rec_yds"];
							$fumbles_tot_away = $fumbles_tot_away + $fumblestat["fumbles_tot"];
							$fumbles_frec_away = $fumbles_frec_away + $fumblestat["defense_frec"];
							$fumbles_frec_yds_away = $fumbles_frec_yds_away + $fumblestat["defense_frec_yds"];
						}

			}

			if ($fumbles_lost_away !== 0 || $fumbles_rec_away !== 0 || $fumbles_rec_tds_away !== 0 || $fumbles_rec_yds_away !== 0 || $fumbles_tot_away !== 0 || $fumbles_frec_away !== 0 || $fumbles_frec_yds_away !== 0) {
				$total_agg_tds_frec_away = $total_agg_tds_frec_away + $fumbles_rec_tds_away;
				$fumble_stats_away .= "<tr>
									 <td>".$fumbles_tot_away."</td>
									 <td>".$fumbles_lost_away."</td>
									 <td>".$fumbles_frec_away."</td>
									 <td>".$fumbles_frec_yds_away."</td></tr>";
			}

			$fumbles_stats_away_body = '<tr class="active">
													<th>Fumbles</th>
													<th>FUM</th>
													<th>LOST</th>
													<th>REC</th>
													<th>YDS</th></tr>'.$fumble_stats_away;

					/*// FUMBLES HOME

					$fumble_string_home = 'http://localhost:2096/play_player?player_id=eq.'.$player_id.'&team=eq.'.$team.'&select=player_id';
					$jsonfumblehome = file_get_contents($fumble_string_home);
					$objfumblehome = json_decode($jsonfumblehome,true);

					$fumble_id_home = array();
					$f = 0;

					foreach ($objfumblehome as $fumble => $fumblestat) {
						$fumble_id_home[$f] = $fumblestat["player_id"];
						$f++;
					}

					$f = 0;

					$fumble_id_home = array_unique($fumble_id_home);

					foreach ($fumble_id_home as $fumble => $fumblestat) {

						$player_string_home = 'http://localhost:2096/player?player_id=eq.'.$fumblestat;
						$player_string_save = $fumblestat;
						$jsonplayerhome = file_get_contents($player_string_home);
						$objplayerhome = json_decode($jsonplayerhome,true);
						$name = $objplayerhome[0]["first_name"][0].".".$objplayerhome[0]["last_name"];
						$fumble_string_home = 'http://localhost:2096/play_player?player_id=eq.'.$player_id.'&team=eq.'.$team.'&player_id=eq.'.$fumblestat.'&select=fumbles_lost,fumbles_rec,fumbles_rec_tds,fumbles_tot,fumbles_rec_yds,defense_frec,defense_frec_yds';
						$jsonfumblehome = file_get_contents($fumble_string_home);
						$objfumblehome = json_decode($jsonfumblehome,true);
						$fumbles_lost_home = 0;
						$fumbles_rec_home = 0;
						$fumbles_rec_tds_home = 0;
						$fumbles_rec_yds_home = 0;
						$fumbles_tot_home = 0;
						$fumbles_frec_home = 0;
						$fumbles_frec_yds_home = 0;
						foreach ($objfumblehome as $fumble => $fumblestat) {
							$fumbles_lost_home = $fumbles_lost_home + $fumblestat["fumbles_lost"];
							$fumbles_rec_home = $fumbles_rec_home + $fumblestat["fumbles_rec"];
							$fumbles_rec_tds_home = $fumbles_rec_tds_home + $fumblestat["fumbles_rec_tds"];
							$fumbles_rec_yds_home = $fumbles_rec_yds_home + $fumblestat["fumbles_rec_yds"];
							$fumbles_tot_home = $fumbles_tot_home + $fumblestat["fumbles_tot"];
							$fumbles_frec_home = $fumbles_frec_home + $fumblestat["defense_frec"];
							$fumbles_frec_yds_home = $fumbles_frec_yds_home + $fumblestat["defense_frec_yds"];
						}
						if ($fumbles_lost_home !== 0 || $fumbles_rec_home !== 0 || $fumbles_rec_tds_home !== 0 || $fumbles_rec_yds_home !== 0 || $fumbles_tot_home !== 0 || $fumbles_frec_home !== 0 || $fumbles_frec_yds_home !== 0) {
							$total_agg_tds_frec_home = $total_agg_tds_frec_home + $fumbles_rec_tds_home;
							$fumble_stats_home .= "<tr><td><a href='player.php?player_id=".$player_string_save."'>".$name."</a></td>
									 <td>".$fumbles_tot_home."</td>
									 <td>".$fumbles_lost_home."</td>
									 <td>".$fumbles_frec_home."</td>
									 <td>".$fumbles_frec_yds_home."</td></tr>";
						}
					}

					$fumbles_stats_home_body = '<tr class="active">
													<th>Fumbles</th>
													<th>FUM</th>
													<th>LOST</th>
													<th>REC</th>
													<th>YDS</th></tr>'.$fumble_stats_home;*/

					// KICKING away
		if ($position === "K" || $position === "P" || $position === "PK") {

			foreach ($finishedgames as $game => $id) {

					$kicking_string_away = 'http://localhost:2096/play_player?gsis_id=eq.'.$id.'&player_id=eq.'.$player_id.'&team=eq.'.$team.'&select=team,player_id,kicking_fga,kicking_fgm,kicking_xpa,kicking_xpmade,kicking_yds&order=team.asc,player_id.desc';
					$jsonkickingaway = file_get_contents($kicking_string_away);
					$objkickingaway = json_decode($jsonkickingaway,true);
					foreach ($objkickingaway as $kicking => $kickingstat) {
							$kicking_fga_away = $kicking_fga_away + $kickingstat["kicking_fga"];
							$kicking_fgm_away = $kicking_fgm_away + $kickingstat["kicking_fgm"];
							$kicking_xpa_away = $kicking_xpa_away + $kickingstat["kicking_xpa"];
							$kicking_xpmade_away = $kicking_xpmade_away + $kickingstat["kicking_xpmade"];
							$kicking_yds_away = $kicking_yds_away + $kickingstat["kicking_yds"];
					}

			}

						if ($kicking_fga_away !== 0 || $kicking_fgm_away !== 0 || $kicking_xpa_away !== 0 || $kicking_xpmade_away !== 0 || $kicking_yds_away !== 0) {
							$kicking_pts_away = ($kicking_fgm_away * 3) + $kicking_xpmade_away;
							$kicking_stats_away .= "<tr>
									 <td>".$kicking_fga_away."/".$kicking_fgm_away."</td>
									 <td>".$kicking_yds_away."</td>
									 <td>".$kicking_xpa_away."/".$kicking_xpmade_away."</td>
									 <td>".$kicking_pts_away."</td></tr>";
						}
					$kicking_stats_away_body = '<tr class="active">
													<th>Kicking</th>
													<th>FG</th>
													<th>YDS</th>
													<th>XP</th>
													<th>PTS</th></tr>'.$kicking_stats_away;
		
					/*// KICKING home

					$kicking_string_home = 'http://localhost:2096/play_player?player_id=eq.'.$player_id.'&team=eq.'.$team.'&select=team,player_id,kicking_fga,kicking_fgm,kicking_xpa,kicking_xpmade,kicking_yds&order=team.asc,player_id.desc';
					$jsonkickinghome = file_get_contents($kicking_string_home);
					$objkickinghome = json_decode($jsonkickinghome,true);

					$kicking_id_home = array();
					$k = 0;

					foreach ($objkickinghome as $kicking => $kickingstat) {
						$kicking_id_home[$k] = $kickingstat["player_id"];
						$k++;
					}

					$k = 0;

					$kicking_id_home = array_unique($kicking_id_home);

					foreach ($kicking_id_home as $kicking => $kickingstat) {

						$player_string_home = 'http://localhost:2096/player?player_id=eq.'.$kickingstat;
						$player_string_save = $kickingstat;
						$jsonplayerhome = file_get_contents($player_string_home);
						$objplayerhome = json_decode($jsonplayerhome,true);
						$name = $objplayerhome[0]["first_name"][0].".".$objplayerhome[0]["last_name"];
						$kicking_string_home = 'http://localhost:2096/play_player?player_id=eq.'.$player_id.'&team=eq.'.$team.'&player_id=eq.'.$kickingstat.'&select=team,player_id,kicking_fga,kicking_fgm,kicking_xpa,kicking_xpmade,kicking_yds&order=team.asc,player_id.desc';
						$jsonkickinghome = file_get_contents($kicking_string_home);
						$objkickinghome = json_decode($jsonkickinghome,true);
						$kicking_fga_home = 0;
						$kicking_fgm_home = 0;
						$kicking_xpa_home = 0;
						$kicking_xpmade_home = 0;
						$kicking_yds_home = 0;
						foreach ($objkickinghome as $kicking => $kickingstat) {
							$kicking_fga_home = $kicking_fga_home + $kickingstat["kicking_fga"];
							$kicking_fgm_home = $kicking_fgm_home + $kickingstat["kicking_fgm"];
							$kicking_xpa_home = $kicking_xpa_home + $kickingstat["kicking_xpa"];
							$kicking_xpmade_home = $kicking_xpmade_home + $kickingstat["kicking_xpmade"];
							$kicking_yds_home = $kicking_yds_home + $kickingstat["kicking_yds"];
						}
						if ($kicking_fga_home !== 0 || $kicking_fgm_home !== 0 || $kicking_xpa_home !== 0 || $kicking_xpmade_home !== 0 || $kicking_yds_home !== 0) {
							$kicking_pts_home = ($kicking_fgm_home * 3) + $kicking_xpmade_home;
							$kicking_stats_home .= "<tr><td><a href='player.php?player_id=".$player_string_save."'>".$name."</a></td>
									 <td>".$kicking_fga_home."/".$kicking_fgm_home."</td>
									 <td>".$kicking_yds_home."</td>
									 <td>".$kicking_xpa_home."/".$kicking_xpmade_home."</td>
									 <td>".$kicking_pts_home."</td></tr>";
						}
					}

					$kicking_stats_home_body = '<tr class="active">
													<th>Kicking</th>
													<th>FG</th>
													<th>YDS</th>
													<th>XP</th>
													<th>PTS</th></tr>'.$kicking_stats_home;*/
			foreach ($finishedgames as $game => $id) {
					// punting away

					$punting_string_away = 'http://localhost:2096/play_player?gsis_id=eq.'.$id.'&player_id=eq.'.$player_id.'&team=eq.'.$team.'&select=team,player_id,punting_tot,punting_yds,punting_i20&order=team.asc,player_id.desc';
						$jsonpuntingaway = file_get_contents($punting_string_away);
						$objpuntingaway = json_decode($jsonpuntingaway,true);
						foreach ($objpuntingaway as $punting => $puntingstat) {
							$punting_tot_away = $punting_tot_away + $puntingstat["punting_tot"];
							$punting_yds_away = $punting_yds_away + $puntingstat["punting_yds"];
							$punting_i20_away = $punting_i20_away + $puntingstat["punting_i20"];
						}
			}
						if ($punting_tot_away !== 0 || $punting_yds_away !== 0 || $punting_i20_away !== 0) {
							$punting_avg_away = number_format($punting_yds_away / $punting_tot_away, 1);
							$punting_stats_away .= "<tr>
									 <td>".$punting_tot_away."</td>
									 <td>".$punting_avg_away."</td>
									 <td>".$punting_i20_away."</td>
									 <td>".$punting_yds_away."</td></tr>";
						}

					$punting_stats_away_body = '<tr class="active">
													<th>Punting</th>
													<th>NO</th>
													<th>AVG</th>
													<th>I20</th>
													<th>YDS</th></tr>'.$punting_stats_away;
		}
					/*// punting home

					$punting_string_home = 'http://localhost:2096/play_player?player_id=eq.'.$player_id.'&team=eq.'.$team.'&select=team,player_id,punting_tot,punting_yds,punting_i20&order=team.asc,player_id.desc';
					$jsonpuntinghome = file_get_contents($punting_string_home);
					$objpuntinghome = json_decode($jsonpuntinghome,true);

					$punting_id_home = array();
					$p = 0;

					foreach ($objpuntinghome as $punting => $puntingstat) {
						$punting_id_home[$p] = $puntingstat["player_id"];
						$p++;
					}

					$p = 0;

					$punting_id_home = array_unique($punting_id_home);

					foreach ($punting_id_home as $punting => $puntingstat) {

						$player_string_home = 'http://localhost:2096/player?player_id=eq.'.$puntingstat;
						$player_string_save = $puntingstat;
						$jsonplayerhome = file_get_contents($player_string_home);
						$objplayerhome = json_decode($jsonplayerhome,true);
						$name = $objplayerhome[0]["first_name"][0].".".$objplayerhome[0]["last_name"];
						$punting_string_home = 'http://localhost:2096/play_player?player_id=eq.'.$player_id.'&team=eq.'.$team.'&player_id=eq.'.$puntingstat.'&select=team,player_id,punting_tot,punting_yds,punting_i20&order=team.asc,player_id.desc';
						$jsonpuntinghome = file_get_contents($punting_string_home);
						$objpuntinghome = json_decode($jsonpuntinghome,true);
						$punting_tot_home = 0;
						$punting_yds_home = 0;
						$punting_i20_home = 0;
						foreach ($objpuntinghome as $punting => $puntingstat) {
							$punting_tot_home = $punting_tot_home + $puntingstat["punting_tot"];
							$punting_yds_home = $punting_yds_home + $puntingstat["punting_yds"];
							$punting_i20_home = $punting_i20_home + $puntingstat["punting_i20"];
						}
						if ($punting_tot_home !== 0 || $punting_yds_home !== 0 || $punting_i20_home !== 0) {
							$punting_avg_home = number_format($punting_yds_home / $punting_tot_home, 1);
							$punting_stats_home .= "<tr><td><a href='player.php?player_id=".$player_string_save."'>".$name."</a></td>
									 <td>".$punting_tot_home."</td>
									 <td>".$punting_avg_home."</td>
									 <td>".$punting_i20_home."</td>
									 <td>".$punting_yds_home."</td></tr>";
						}
					}

					$punting_stats_home_body = '<tr class="active">
													<th>Punting</th>
													<th>NO</th>
													<th>AVG</th>
													<th>I20</th>
													<th>YDS</th></tr>'.$punting_stats_home;*/

					// kickret away
		if ($position === "WR" || $position === "TE" || $position === "CB" || $position === "DT" || $position === "DE" || $position === "RB") {

			foreach ($finishedgames as $game => $id) {

					$kickret_string_away = 'http://localhost:2096/play_player?gsis_id=eq.'.$id.'&player_id=eq.'.$player_id.'&team=eq.'.$team.'&select=team,player_id,kickret_fair,kickret_oob,kickret_ret,kickret_tds,kickret_touchback,kickret_yds&order=team.asc,player_id.desc';
						$jsonkickretaway = file_get_contents($kickret_string_away);
						$objkickretaway = json_decode($jsonkickretaway,true);
						foreach ($objkickretaway as $kickret => $kickretstat) {
							$kickret_ret_away = $kickret_ret_away + $kickretstat["kickret_ret"];
							$kickret_tds_away = $kickret_tds_away + $kickretstat["kickret_tds"];
							$kickret_yds_away = $kickret_yds_away + $kickretstat["kickret_yds"];
						}
			}
						if ($kickret_ret_away !== 0 || $kickret_tds_away !== 0 || $kickret_yds_away !== 0) {
							$total_agg_tds_kickret_away = $total_agg_tds_kickret_away + $kickret_tds_away;
							$kickret_avg_away = number_format($kickret_yds_away / $kickret_ret_away, 1);
							$kickret_stats_away .= "<tr>
									 <td>".$kickret_ret_away."</td>
									 <td>".$kickret_avg_away."</td>
									 <td>".$kickret_tds_away."</td>
									 <td>".$kickret_yds_away."</td></tr>";
						}

					$kickret_stats_away_body = '<tr class="active">
													<th>Kickoff Returns</th>
													<th>NO</th>
													<th>AVG</th>
													<th>TD</th>
													<th>YDS</th></tr>'.$kickret_stats_away;

					/*// kickret home

					$kickret_string_home = 'http://localhost:2096/play_player?player_id=eq.'.$player_id.'&team=eq.'.$team.'&select=team,player_id,kickret_fair,kickret_oob,kickret_ret,kickret_tds,kickret_touchback,kickret_yds&order=team.asc,player_id.desc';
					$jsonkickrethome = file_get_contents($kickret_string_home);
					$objkickrethome = json_decode($jsonkickrethome,true);

					$kickret_id_home = array();
					$k = 0;

					foreach ($objkickrethome as $kickret => $kickretstat) {
						$kickret_id_home[$k] = $kickretstat["player_id"];
						$k++;
					}

					$k = 0;

					$kickret_id_home = array_unique($kickret_id_home);

					foreach ($kickret_id_home as $kickret => $kickretstat) {

						$player_string_home = 'http://localhost:2096/player?player_id=eq.'.$kickretstat;
						$player_string_save = $kickretstat;
						$jsonplayerhome = file_get_contents($player_string_home);
						$objplayerhome = json_decode($jsonplayerhome,true);
						$name = $objplayerhome[0]["first_name"][0].".".$objplayerhome[0]["last_name"];
						$kickret_string_home = 'http://localhost:2096/play_player?player_id=eq.'.$player_id.'&team=eq.'.$team.'&player_id=eq.'.$kickretstat.'&select=team,player_id,kickret_fair,kickret_oob,kickret_ret,kickret_tds,kickret_touchback,kickret_yds&order=team.asc,player_id.desc';
						$jsonkickrethome = file_get_contents($kickret_string_home);
						$objkickrethome = json_decode($jsonkickrethome,true);
						$kickret_ret_home = 0;
						$kickret_tds_home = 0;
						$kickret_yds_home = 0;
						foreach ($objkickrethome as $kickret => $kickretstat) {
							$kickret_ret_home = $kickret_ret_home + $kickretstat["kickret_ret"];
							$kickret_tds_home = $kickret_tds_home + $kickretstat["kickret_tds"];
							$kickret_yds_home = $kickret_yds_home + $kickretstat["kickret_yds"];
						}
						if ($kickret_ret_home !== 0 || $kickret_tds_home !== 0 || $kickret_yds_home !== 0) {
							$total_agg_tds_kickret_home = $total_agg_tds_kickret_home + $kickret_tds_home;
							$kickret_avg_home = number_format($kickret_yds_home / $kickret_ret_home, 1);
							$kickret_stats_home .= "<tr><td><a href='player.php?player_id=".$player_string_save."'>".$name."</a></td>
									 <td>".$kickret_ret_home."</td>
									 <td>".$kickret_avg_home."</td>
									 <td>".$kickret_tds_home."</td>
									 <td>".$kickret_yds_home."</td></tr>";
						}
					}

					$kickret_stats_home_body = '<tr class="active">
													<th>Kickoff Returns</th>
													<th>NO</th>
													<th>AVG</th>
													<th>TD</th>
													<th>YDS</th></tr>'.$kickret_stats_home;*/

					// puntret away
			foreach ($finishedgames as $game => $id) {

					$puntret_string_away = 'http://localhost:2096/play_player?gsis_id=eq.'.$id.'&player_id=eq.'.$player_id.'&team=eq.'.$team.'&select=team,player_id,puntret_fair,puntret_oob,puntret_tot,puntret_tds,puntret_touchback,puntret_yds&order=team.asc,player_id.desc';
						$jsonpuntretaway = file_get_contents($puntret_string_away);
						$objpuntretaway = json_decode($jsonpuntretaway,true);
						foreach ($objpuntretaway as $puntret => $puntretstat) {
							$puntret_tot_away = $puntret_tot_away + $puntretstat["puntret_tot"];
							$puntret_tds_away = $puntret_tds_away + $puntretstat["puntret_tds"];
							$puntret_yds_away = $puntret_yds_away + $puntretstat["puntret_yds"];
						}
				}
						if ($puntret_tot_away !== 0 || $puntret_tds_away !== 0 || $puntret_yds_away !== 0) {
							$total_agg_tds_puntret_away = $total_agg_tds_puntret_away + $puntret_tds_away;
							$puntret_avg_away = number_format($puntret_yds_away / $puntret_tot_away, 1);
							$puntret_stats_away .= "<tr>
									 <td>".$puntret_tot_away."</td>
									 <td>".$puntret_avg_away."</td>
									 <td>".$puntret_tds_away."</td>
									 <td>".$puntret_yds_away."</td></tr>";
						}

					$puntret_stats_away_body = '<tr class="active">
													<th>Punt Returns</th>
													<th>NO</th>
													<th>AVG</th>
													<th>TD</th>
													<th>YDS</th></tr>'.$puntret_stats_away;
		}
					/*// puntret home

					$puntret_string_home = 'http://localhost:2096/play_player?player_id=eq.'.$player_id.'&team=eq.'.$team.'&select=team,player_id,puntret_fair,puntret_oob,puntret_tot,puntret_tds,puntret_touchback,puntret_yds&order=team.asc,player_id.desc';
					$jsonpuntrethome = file_get_contents($puntret_string_home);
					$objpuntrethome = json_decode($jsonpuntrethome,true);

					$puntret_id_home = array();
					$k = 0;

					foreach ($objpuntrethome as $puntret => $puntretstat) {
						$puntret_id_home[$k] = $puntretstat["player_id"];
						$k++;
					}

					$k = 0;

					$puntret_id_home = array_unique($puntret_id_home);

					foreach ($puntret_id_home as $puntret => $puntretstat) {

						$player_string_home = 'http://localhost:2096/player?player_id=eq.'.$puntretstat;
						$player_string_save = $puntretstat;
						$jsonplayerhome = file_get_contents($player_string_home);
						$objplayerhome = json_decode($jsonplayerhome,true);
						$name = $objplayerhome[0]["first_name"][0].".".$objplayerhome[0]["last_name"];
						$puntret_string_home = 'http://localhost:2096/play_player?player_id=eq.'.$player_id.'&team=eq.'.$team.'&player_id=eq.'.$puntretstat.'&select=team,player_id,puntret_fair,puntret_oob,puntret_tot,puntret_tds,puntret_touchback,puntret_yds&order=team.asc,player_id.desc';
						$jsonpuntrethome = file_get_contents($puntret_string_home);
						$objpuntrethome = json_decode($jsonpuntrethome,true);
						$puntret_tot_home = 0;
						$puntret_tds_home = 0;
						$puntret_yds_home = 0;
						foreach ($objpuntrethome as $puntret => $puntretstat) {
							$puntret_tot_home = $puntret_tot_home + $puntretstat["puntret_tot"];
							$puntret_tds_home = $puntret_tds_home + $puntretstat["puntret_tds"];
							$puntret_yds_home = $puntret_yds_home + $puntretstat["puntret_yds"];
						}
						if ($puntret_tot_home !== 0 || $puntret_tds_home !== 0 || $puntret_yds_home !== 0) {
							$total_agg_tds_puntret_home = $total_agg_tds_puntret_home + $puntret_tds_home;
							$puntret_avg_home = number_format($puntret_yds_home / $puntret_tot_home, 1);
							$puntret_stats_home .= "<tr><td><a href='player.php?player_id=".$player_string_save."'>".$name."</a></td>
									 <td>".$puntret_tot_home."</td>
									 <td>".$puntret_avg_home."</td>
									 <td>".$puntret_tds_home."</td>
									 <td>".$puntret_yds_home."</td></tr>";
						}
					}

					$puntret_stats_home_body = '<tr class="active">
													<th>Punt Returns</th>
													<th>NO</th>
													<th>AVG</th>
													<th>TD</th>
													<th>YDS</th></tr>'.$puntret_stats_home;*/

					// defense away

		if ($position === "ILB" || $position === "SS" || $position === "CB" || $position === "OLB" || $position === "FS" || $position === "DE" || $position === "NT" || $position === "LB"  || $position === "DT" || $position === "MLB" || $position === "S" || $position === "DL" || $position === "NB") {

			foreach ($finishedgames as $game => $id) {

					$defense_string_away = 'http://localhost:2096/play_player?gsis_id=eq.'.$id.'&player_id=eq.'.$player_id.'&team=eq.'.$team.'&select=team,player_id,defense_ast,defense_tkl,defense_sk,defense_int,defense_ffum,defense_int_tds,defense_frec_tds&order=defense_tkl.desc,player_id.desc';
						$jsondefenseaway = file_get_contents($defense_string_away);
						$objdefenseaway = json_decode($jsondefenseaway,true);
						foreach ($objdefenseaway as $defense => $defensestat) {
							$defense_ast_away = $defense_ast_away + $defensestat["defense_ast"];
							$defense_tkl_away = $defense_tkl_away + $defensestat["defense_tkl"];
							$defense_sk_away = $defense_sk_away + $defensestat["defense_sk"];
							$defense_int_away = $defense_int_away + $defensestat["defense_int"];
							$defense_ffum_away = $defense_ffum_away + $defensestat["defense_ffum"];
							$defense_int_tds_away = $defense_int_tds_away + $defensestat["defense_int_tds"];
							$defense_frec_tds_away = $defense_frec_tds_away + $defensestat["defense_frec_tds"];
						}
			}
						if ($defense_ast_away !== 0 || $defense_tkl_away !== 0 || $defense_sk_away !== 0 || $defense_int_away !== 0 || $defense_ffum_away !== 0 || $defense_int_tds_away !== 0 || $defense_frec_tds_away !== 0) {
							$total_agg_tds_int_away = $total_agg_tds_int_away + $defense_int_tds_away;
							$total_agg_tds_frec_away = $total_agg_tds_frec_away + $defense_frec_tds_away;
							$defense_stats_away .= "<tr>
									 <td>".$defense_tkl_away."-".$defense_ast_away."</td>
									 <td>".$defense_sk_away."</td>
									 <td>".$defense_int_away."</td>
									 <td>".$defense_ffum_away."</td></tr>";
						}

					$defense_stats_away_body = '<tr class="active">
													<th>Defense</th>
													<th>T-A</th>
													<th>SCK</th>
													<th>INT</th>
													<th>FF</th></tr>'.$defense_stats_away;
		}
					/*// defense home

					$defense_string_home = 'http://localhost:2096/play_player?player_id=eq.'.$player_id.'&team=eq.'.$team.'&select=team,player_id,defense_ast,defense_tkl,defense_sk,defense_int,defense_int_tds,defense_ffum&order=defense_tkl.desc,player_id.desc';
					$jsondefensehome = file_get_contents($defense_string_home);
					$objdefensehome = json_decode($jsondefensehome,true);

					$defense_id_home = array();
					$d = 0;

					foreach ($objdefensehome as $defense => $defensestat) {
						$defense_id_home[$d] = $defensestat["player_id"];
						$d++;
					}

					$d = 0;

					$defense_id_home = array_unique($defense_id_home);

					foreach ($defense_id_home as $defense => $defensestat) {

						$player_string_home = 'http://localhost:2096/player?player_id=eq.'.$defensestat;
						$player_string_save = $defensestat;
						$jsonplayerhome = file_get_contents($player_string_home);
						$objplayerhome = json_decode($jsonplayerhome,true);
						if ($objplayerhome[0]["position"] !== "ILB" && $objplayerhome[0]["position"] !== "SS" && $objplayerhome[0]["position"] !== "CB" && $objplayerhome[0]["position"] !== "OLB" && $objplayerhome[0]["position"] !== "FS" && $objplayerhome[0]["position"] !== "DE" && $objplayerhome[0]["position"] !== "NT" && $objplayerhome[0]["position"] !== "LB"  && $objplayerhome[0]["position"] !== "DT" && $objplayerhome[0]["position"] !== "MLB" && $objplayerhome[0]["position"] !== "S" && $objplayerhome[0]["position"] !== "DL" && $objplayerhome[0]["position"] !== "NB") {
							continue;
						}
						$name = $objplayerhome[0]["first_name"][0].".".$objplayerhome[0]["last_name"];
						$defense_string_home = 'http://localhost:2096/play_player?player_id=eq.'.$player_id.'&team=eq.'.$team.'&player_id=eq.'.$defensestat.'&select=team,player_id,defense_ast,defense_tkl,defense_sk,defense_int,defense_ffum,defense_int_tds,defense_frec_tds&order=defense_tkl.desc,player_id.desc';
						$jsondefensehome = file_get_contents($defense_string_home);
						$objdefensehome = json_decode($jsondefensehome,true);
						$defense_ast_home = 0;
						$defense_tkl_home = 0;
						$defense_sk_home = 0;
						$defense_int_home = 0;
						$defense_ffum_home = 0;
						$defense_int_tds_home = 0;
						$defense_frec_tds_home = 0;
						foreach ($objdefensehome as $defense => $defensestat) {
							$defense_ast_home = $defense_ast_home + $defensestat["defense_ast"];
							$defense_tkl_home = $defense_tkl_home + $defensestat["defense_tkl"];
							$defense_sk_home = $defense_sk_home + $defensestat["defense_sk"];
							$defense_int_home = $defense_int_home + $defensestat["defense_int"];
							$defense_ffum_home = $defense_ffum_home + $defensestat["defense_ffum"];
							$defense_int_tds_home = $defense_int_tds_home + $defensestat["defense_int_tds"];
							$defense_frec_tds_home = $defense_frec_tds_home + $defensestat["defense_frec_tds"];
						}
						if ($defense_ast_home !== 0 || $defense_tkl_home !== 0 || $defense_sk_home !== 0 || $defense_int_home !== 0 || $defense_ffum_home !== 0 || $defense_int_tds_home !== 0 || $defense_frec_tds_home !== 0) {
							$total_agg_tds_int_home = $total_agg_tds_int_home + $defense_int_tds_home;
							$total_agg_tds_frec_home = $total_agg_tds_frec_home + $defense_frec_tds_home;
							$defense_stats_home .= "<tr><td><a href='player.php?player_id=".$player_string_save."'>".$name."</a></td>
									 <td>".$defense_tkl_home."-".$defense_ast_home."</td>
									 <td>".$defense_sk_home."</td>
									 <td>".$defense_int_home."</td>
									 <td>".$defense_ffum_home."</td></tr>";
						}
					}

					$defense_stats_home_body = '<tr class="active">
													<th>Defense</th>
													<th>T-A</th>
													<th>SCK</th>
													<th>INT</th>
													<th>FF</th></tr>'.$defense_stats_home;*/


				
					//$globalleader_receiving_yds[array_search(max($master_receiving_yds_home), $master_receiving_yds_home)] = max($master_receiving_yds_home);


				if ($position === "QB") {
					$summary_table_away = '<table class="table" style="margin-bottom:0px;">
									<thead>
										<tr class="'.$team.'colors">
											<th>&nbsp;</th>
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
									</tbody>
								</table>';
				} 

				/*$summary_table_home = '<table class="table" style="margin-bottom:0px;">
									<thead>
										<tr class="'.$team.'colors">
											<th>Team Leaders: <a href="team.php?team='.$team.'">'.$hometeam.'</a> <span style="font-weight:normal;">('.$wins["".$team.""].'-'.$losses["".$team.""].'-'.$ties["".$team.""].')</span></th>
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
									</tbody>
								</table>';*/

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
									</tbody>
								</table>';

				/*$summary_table_home .= '<table class="table">
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
									</tbody>
								</table>';*/

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
									</tbody>
								</table>';

				/*$summary_table_home .= '<table class="table">
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
									</tbody>
								</table>';*/

				$stats_table_away_faux = '<table class="table table-bordered display3" style="margin-bottom:0px;z-index: -1;position: absolute;">
									<thead>
										<tr class="'.$team.'colors">
											<th>&nbsp;</th>
											<th></th>
											<th></th>
											<th></th>
											<th></th>
										</tr>
									</thead>
									<tbody style="z-index: -1;position: absolute;">
										'.$passing_stats_away_body.'
										'.$rushing_stats_away_body.'
										'.$receiving_stats_away_body.'
										'.$fumbles_stats_away_body.'
										'.$kicking_stats_away_body.'
										'.$punting_stats_away_body.'
										'.$kickret_stats_away_body.'
										'.$puntret_stats_away_body.'
										'.$defense_stats_away_body.'
									</tbody>
								</table>';

				/*$stats_table_home_faux = '<table class="table table-bordered display3" style="margin-bottom:0px;z-index: -1;position: absolute;">
									<thead>
										<tr class="'.$team.'colors">
											<th><a href="team.php?team='.$team.'" class="'.$team.'colors">'.$hometeam.'</a> <span style="font-weight:normal;" class="'.$team.'colors">('.$wins["".$team.""].'-'.$losses["".$team.""].'-'.$ties["".$team.""].')</span></th>
											<th></th>
											<th></th>
											<th></th>
											<th></th>
										</tr>
									</thead>
									<tbody style="z-index: -1;position: absolute;">
										'.$passing_stats_home_body.'
										'.$rushing_stats_home_body.'
										'.$receiving_stats_home_body.'
										'.$fumbles_stats_home_body.'
										'.$kicking_stats_home_body.'
										'.$punting_stats_home_body.'
										'.$kickret_stats_home_body.'
										'.$puntret_stats_home_body.'
										'.$defense_stats_home_body.'
									</tbody>
								</table>';*/

				/*$stats_table_away = '<table class="table" style="margin-bottom:0px;">
									<thead>
										<tr class="'.$team.'colors">
											<th>&nbsp;</th>
											<th></th>
											<th></th>
											<th></th>
											<th></th>
										</tr>
									</thead>
									<tbody>
									</tbody>
								</table>';*/

				/*$stats_table_home = '<table class="table" style="margin-bottom:0px;">
									<thead>
										<tr class="'.$team.'colors">
											<th><a href="team.php?team='.$team.'" class="'.$team.'colors">'.$hometeam.'</a> <span style="font-weight:normal;" class="'.$team.'colors">('.$wins["".$team.""].'-'.$losses["".$team.""].'-'.$ties["".$team.""].')</span></th>
											<th></th>
											<th></th>
											<th></th>
											<th></th>
										</tr>
									</thead>
									<tbody>
									</tbody>
								</table>';*/

			if ($position === "QB") {

				$stats_table_away = '<table class="table table-bordered display">
									<thead>
										<tr class="'.$team.'colors">
											<th>Passing</th>
											<th></th>
											<th></th>
											<th></th>
										</tr>
										<tr class="active">
											<th>CP/AT</th>
											<th>YDS</th>
											<th>TD</th>
											<th>INT</th>
										</tr>
									</thead>
									<tbody>
										'.$passing_stats_away.'
									</tbody>
								</table>';

			}

				/*$stats_table_home .= '<table class="table table-bordered display">
									<thead>
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
									</tbody>
								</table>';*/
			if ($position === "QB" || $position === "RB") {

				$stats_table_away .= '<table id="data-table" class="table table-bordered display">
									<thead>
										<tr class="'.$team.'colors">
											<th>Rushing</th>
											<th></th>
											<th></th>
											<th></th>
										</tr>
										<tr class="active">
											<th>ATT</th>
											<th>YDS</th>
											<th>TD</th>
											<th>LOSS</th>
										</tr>
									</thead>
									<tbody>
										'.$rushing_stats_away.'
									</tbody>
								</table>';

			}

				/*$stats_table_home .= '<table id="data-table" class="table table-bordered display">
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
									</tbody>
								</table>';*/

			if ($position === "WR" || $position === "TE" || $position === "RB") {

				$stats_table_away .= '<table id="data-table" class="table table-bordered display">
									<thead>
										<tr class="'.$team.'colors">
											<th>Receiving</th>
											<th></th>
											<th></th>
											<th></th>
										</tr>
										<tr class="active">
											<th>REC</th>
											<th>YDS</th>
											<th>TD</th>
											<th>YAC</th>
										</tr>
									</thead>
									<tbody>
										'.$receiving_stats_away.'
									</tbody>
								</table>';
				}

				/*$stats_table_home .= '<table id="data-table" class="table table-bordered display">
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
									</tbody>
								</table>';*/

				$stats_table_away .= '<table id="data-table" class="table table-bordered display">
									<thead>
										<tr class="'.$team.'colors">
											<th>Fumbles</th>
											<th></th>
											<th></th>
											<th></th>
										</tr>
										<tr class="active">
											<th>FUM</th>
											<th>LOST</th>
											<th>REC</th>
											<th>YDS</th>
										</tr>
									</thead>
									<tbody>
										'.$fumble_stats_away.'
									</tbody>
								</table>';

				/*$stats_table_home .= '<table id="data-table" class="table table-bordered display">
									<thead>
										<tr class="active">
											<th>Fumbles</th>
											<th>FUM</th>
											<th>LOST</th>
											<th>REC</th>
											<th>YDS</th>
										</tr>
									</thead>
									<tbody>
										'.$fumble_stats_home.'
									</tbody>
								</table>';*/
if ($position === "K" || $position === "P" || $position === "PK") {
				$stats_table_away .= '<table id="data-table" class="table table-bordered display">
									<thead>
										<tr class="'.$team.'colors">
											<th>Kicking</th>
											<th></th>
											<th></th>
											<th></th>
										</tr>
										<tr class="active">
											<th>FG</th>
											<th>YDS</th>
											<th>XP</th>
											<th>PTS</th>
										</tr>
									</thead>
									<tbody>
										'.$kicking_stats_away.'
									</tbody>
								</table>';

				/*$stats_table_home .= '<table id="data-table" class="table table-bordered display">
									<thead>
										<tr class="active">
											<th>Kicking</th>
											<th>FG</th>
											<th>YDS</th>
											<th>XP</th>
											<th>PTS</th>
										</tr>
									</thead>
									<tbody>
										'.$kicking_stats_home.'
									</tbody>
								</table>';*/

				$stats_table_away .= '<table id="data-table" class="table table-bordered display">
									<thead>
										<tr class="'.$team.'colors">
											<th>Punting</th>
											<th></th>
											<th></th>
											<th></th>
										</tr>
										<tr class="active">
											<th>NO</th>
											<th>AVG</th>
											<th>I20</th>
											<th>YDS</th>
										</tr>
									</thead>
									<tbody>
										'.$punting_stats_away.'
									</tbody>
								</table>';
}
				/*$stats_table_home .= '<table id="data-table" class="table table-bordered display">
									<thead>
										<tr class="active">
											<th>Punting</th>
											<th>NO</th>
											<th>AVG</th>
											<th>I20</th>
											<th>YDS</th>
										</tr>
									</thead>
									<tbody>
										'.$punting_stats_home.'
									</tbody>
								</table>';*/
if ($position === "WR" || $position === "TE" || $position === "RB" || $position === "QB") {
				$stats_table_away .= '<table id="data-table" class="table table-bordered display">
									<thead>
										<tr class="'.$team.'colors">
											<th>Kickoff Returns</th>
											<th></th>
											<th></th>
											<th></th>
										</tr>
										<tr class="active">
											<th>NO</th>
											<th>AVG</th>
											<th>TD</th>
											<th>YDS</th>
										</tr>
									</thead>
									<tbody>
										'.$kickret_stats_away.'
									</tbody>
								</table>';

				/*$stats_table_home .= '<table id="data-table" class="table table-bordered display">
									<thead>
										<tr class="active">
											<th>Kickoff Returns</th>
											<th>NO</th>
											<th>AVG</th>
											<th>TD</th>
											<th>YDS</th>
										</tr>
									</thead>
									<tbody>
										'.$kickret_stats_home.'
									</tbody>
								</table>';*/

				$stats_table_away .= '<table id="data-table" class="table table-bordered display">
									<thead>
										<tr class="'.$team.'colors">
											<th>Punt Returns</th>
											<th></th>
											<th></th>
											<th></th>
										</tr>
										<tr class="active">
											<th>NO</th>
											<th>AVG</th>
											<th>TD</th>
											<th>YDS</th>
										</tr>
									</thead>
									<tbody>
										'.$puntret_stats_away.'
									</tbody>
								</table>';
}
				/*$stats_table_home .= '<table id="data-table" class="table table-bordered display">
									<thead>
										<tr class="active">
											<th>Punt Returns</th>
											<th>NO</th>
											<th>AVG</th>
											<th>TD</th>
											<th>YDS</th>
										</tr>
									</thead>
									<tbody>
										'.$puntret_stats_home.'
									</tbody>
								</table>';*/
if ($position === "ILB" || $position === "SS" || $position === "CB" || $position === "OLB" || $position === "FS" || $position === "DE" || $position === "NT" || $position === "LB"  || $position === "DT" || $position === "MLB" || $position === "S" || $position === "DL" || $position === "NB") {
				$stats_table_away .= '<table id="data-table" class="table table-bordered display">
									<thead>
										<tr class="'.$team.'colors">
											<th>Defense</th>
											<th></th>
											<th></th>
											<th></th>
										</tr>
										<tr class="active">
											<th>T-A</th>
											<th>SCK</th>
											<th>INT</th>
											<th>FF</th>
										</tr>
									</thead>
									<tbody>
										'.$defense_stats_away.'
									</tbody>
								</table>';
}
				/*$stats_table_home .= '<table id="data-table" class="table table-bordered display">
									<thead>
										<tr class="active">
											<th>Defense</th>
											<th>T-A</th>
											<th>SCK</th>
											<th>INT</th>
											<th>FF</th>
										</tr>
									</thead>
									<tbody>
										'.$defense_stats_home.'
									</tbody>
								</table>';*/

					$total_agg_tds_away = $total_agg_tds_passing_away + $total_agg_tds_rushing_away + $total_agg_tds_int_away + $total_agg_tds_frec_away + $total_agg_tds_kickret_away + $total_agg_tds_puntret_away;

					$total_agg_tds_away = $total_agg_tds_passing_away + $total_agg_tds_rushing_away + $total_agg_tds_int_away + $total_agg_tds_frec_away + $total_agg_tds_kickret_away + $total_agg_tds_puntret_away;

				//$total_agg_tds_home = $total_agg_tds_passing_home + $total_agg_tds_rushing_home + $total_agg_tds_int_home + $total_agg_tds_frec_home + $total_agg_tds_kickret_home + $total_agg_tds_puntret_home;

				
		/*$q2a = $awayscore_q1+$awayscore_q2;
		$q3a = $awayscore_q1+$awayscore_q2+$awayscore_q3;
		$q4a = $awayscore_q1+$awayscore_q2+$awayscore_q3+$awayscore_q4;
		$q5a = $awayscore_q1+$awayscore_q2+$awayscore_q3+$awayscore_q4+$awayscore_q5;

		$q2h = $homescore_q1+$homescore_q2;
		$q3h = $homescore_q1+$homescore_q2+$homescore_q3;
		$q4h = $homescore_q1+$homescore_q2+$homescore_q3+$homescore_q4;
		$q5h = $homescore_q1+$homescore_q2+$homescore_q3+$homescore_q4+$homescore_q5;

		if ($homescore > $awayscore) {
			$adjusted = $homescore;
		} else if ($awayscore > $homescore) {
			$adjusted = $awayscore;
		}*/

		
		$winbg = "background: url('/assets/img/teams/".$team."_lg.png') no-repeat;background-size: cover;background-position-y: 50%;background-color: #000;";
		$winbg2 = "-ms-filter:'progid:DXImageTransform.Microsoft.Alpha(Opacity=99)';filter: alpha(opacity=99);opacity: 0.99;background-color:#222!important;";

		//$agg_yds = $total_away_net_yards_gained+$total_home_net_yards_gained;

		//$agg_fdowns = $firstdowns_away_total+$firstdowns_home_total;

		$score_morris_area_chart = '<div class="col-md-12" style="padding:0px;'.$winbg.'">
			        <div class="widget-chart with-sidebar bg-black" style="-webkit-border-radius: 0px;-moz-border-radius: 0px;border-radius: 0px;margin-bottom: 0px;'.$winbg2.'">
			            <div class="widget-chart-content">
			                <h4 class="chart-title">
			                    Complete Pass Yard Cost
			                    <small>how many incomplete yards before completed pass</small>
			                </h4>
			                <div id="score-line-chart" class="morris-inverse" style="height: 260px;"></div>
			            </div>
			            <div class="widget-chart-sidebar bg-black-darker" style="background-color:#151515!important;">
			                <div class="chart-number">
			                    '.$firstdowns_away_total.' First Downs
			                    <small>in current season</small>
			                </div>
			                <div id="score-donut-chart" style="height: 160px"></div>
			                <ul class="chart-legend">
			                    <li><i class="fa fa-circle-o fa-fw m-r-5" style="color:#00acac;"></i> '.$firstdowns_passing_away.' <span>By Passing</span></li>
			                    <li><i class="fa fa-circle-o fa-fw m-r-5" style="color:#33bdbd;"></i> '.$firstdowns_rushing_away.' <span>By Rushing</span></li>
			                   	<li><i class="fa fa-circle-o fa-fw m-r-5" style="color:#008a8a;"></i> '.$firstdowns_penalty_away.' <span>By Penalty</span></li>
			                </ul>
			            </div>
			        </div>
			    </div>';

		$score_morris_area_chart_js = "
var green		= '#00acac',
	greenLight	= '#33bdbd',
	aquaLight	= '#6dc5de',
	greenDark	= '#008a8a';

var getMonthName = function(number) {
    var month = [];
    month[0] = \"Q1\";
    month[1] = \"Q2\";
    month[2] = \"Q3\";
    month[3] = \"Q4\";
    month[4] = \"T\";
    
    return month[number];
};

var getDate = function(date) {
    var currentDate = new Date(date);
    var dd = currentDate.getDate();
    var mm = currentDate.getMonth() + 1;
    var yyyy = currentDate.getFullYear();
    
    if (dd < 10) {
        dd = '0' + dd;
    }
    if (mm < 10) {
        mm = '0' + mm;
    }
    currentDate = yyyy+'-'+mm+'-'+dd;
    
    return currentDate;
};

var handleScoreLineChart = function() {
    var away = '".$teamcolor["".$team.""]."';
    var awayLight = '".$teamcolor["".$team.""]."';
    var home = '".$teamcolor["".$team.""]."';
    var homeLight = '".$teamcolor["".$team.""]."';
    var blackTransparent = 'rgba(0,0,0,0.80)';
    var whiteTransparent = 'rgba(255,255,255,0.4)';
    
    Morris.Line({
        element: 'score-line-chart',
        data: [
            {x: 'P1', y: ".$passing_yds_cmp_save_1."0, z: ".$passing_yds_incmp_save_1."0},
            {x: 'P2', y: ".$passing_yds_cmp_save_2."0, z: ".$passing_yds_incmp_save_2."0},
            {x: 'P3', y: ".$passing_yds_cmp_save_3."0, z: ".$passing_yds_incmp_save_3."0},
            {x: 'P4', y: ".$passing_yds_cmp_save_4."0, z: ".$passing_yds_incmp_save_4."0},
            {x: 'P5', y: ".$passing_yds_cmp_save_5."0, z: ".$passing_yds_incmp_save_5."0},
            {x: 'P6', y: ".$passing_yds_cmp_save_6."0, z: ".$passing_yds_incmp_save_6."0}
        ],
        xkey: 'x',
        ykeys: ['y', 'z'],
        parseTime: false,
        labels: ['Completed Pass YDS', 'Incompleted Pass YDS'],
        lineColors: [away, aquaLight],
        pointFillColors: [away, aquaLight],
        lineWidth: '2px',
        pointStrokeColors: [blackTransparent, blackTransparent],
        resize: true,
        gridTextFamily: 'Open Sans',
        gridTextColor: whiteTransparent,
        gridTextWeight: 'normal',
        gridTextSize: '11px',
        gridLineColor: '#444',
        hideHover: 'auto',
    });
};

var handleNetYdsDonutChart = function() {
    var away = '".$teamcolor["".$team.""]."';
    var awayLight = '".$teamcolor["".$team.""]."';
    var home = '".$teamcolor["".$team.""]."';
    var homeLight = '".$teamcolor["".$team.""]."';
    Morris.Donut({
        element: 'score-donut-chart',
        data: [
            {label: \"By Passing\", value: ".$firstdowns_passing_away."},
            {label: \"By Rushing\", value: ".$firstdowns_rushing_away."},
            {label: \"By Penalty\", value: ".$firstdowns_penalty_away."}
        ],
        colors: [green, greenLight, greenDark],
        labelFamily: 'Open Sans',
        labelColor: 'rgba(255,255,255,0.4)',
        labelTextSize: '12px',
        backgroundColor: '#242a30'
    });
};

var handleFdownsYdsDonutChart = function() {
    var away = '".$teamcolor["".$team.""]."';
    var awayLight = '".$teamcolor["".$team.""]."';
    var home = '".$teamcolor["".$team.""]."';
    var homeLight = '".$teamcolor["".$team.""]."';
    Morris.Donut({
        element: 'fdowns-donut-chart',
        data: [
            {label: \"".$teamname["".$team.""]."\", value: ".$firstdowns_away_total."},
            {label: \"".$teamname["".$team.""]."\", value: ".$firstdowns_home_total."}
        ],
        colors: [away, greenLight],
        labelFamily: 'Open Sans',
        labelColor: 'rgba(255,255,255,0.4)',
        labelTextSize: '12px',
        backgroundColor: '#242a30'
    });
};

var DashboardV2 = function () {
	\"use strict\";
    return {
        //main function
        init: function () {
            handleScoreLineChart();
            handleNetYdsDonutChart();
        }
    };
}();
DashboardV2.init();";

	$tabbody = '<ul class="nav nav-tabs bg-black-darker" style="background:rgba(27, 26, 26, 0.98)!important;">
								<li class="active"><a href="#default-tab-1" data-toggle="tab">'.$year.' Season Stats</a></li>
	                    		<li><a href="#default-tab-2" data-toggle="tab">Career Highs</a></li>
	                    	</ul>
	                    	<div class="tab-content">
	                    		<div class="tab-pane fade active in" id="default-tab-1">
	                    			'.$stats_table_away.'
	                    		</div>
	                    		<div class="tab-pane fade" id="default-tab-2">
	                    			
	                    		</div>
	                    	</div>';

	$shareurl = 'http://statstrac.com/player.php?player_id='.$player_id;
	$shareurl = urlencode($shareurl);

	$sortid = $i + 1;

	$widget[$i] = '<div class="panel panel-inverse">
                        <div class="panel-heading">
                            <div class="panel-heading-btn pull-right">
                            	<a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand" style="color:#000;"><i class="fa fa-expand"></i></a>
                                <a href="http://www.facebook.com/share.php?u='.$shareurl.'" target="_blank" class="btn btn-primary btn-icon btn-circle btn-xs"><i class="fa fa-facebook"></i></a>
                                <a href="http://twitter.com/intent/tweet?status='.$shareurl.'" target="_blank" class="btn btn-info btn-icon btn-circle btn-xs"><i class="fa fa-twitter"></i></a>
                            </div>
                            '.$progress_button.'
                            <h4 class="panel-title"><a href="player.php?team='.$player_id.'">'.$year.' '.$first_name.' '.$last_name.' (#'.$uniform_number.' '.$position.') <strong>'.$conference["".$team.""].'</strong></a></h4>
                        </div>
                        <div class="panel-body">
                        	'.$score_morris_area_chart.'
                        	<ul class="nav nav-tabs">
								'.$tabs.'
							</ul>
							<div class="tab-content" style="margin-bottom:0px;">
								<div class="tab-pane fade active in" id="'.$hashtag.'-tab-1">
									<div class="col-md-12" style="padding:0px;background:rgba(27, 27, 27, 0.85)!important;">
	                            		<table class="table score" style="margin-bottom:0px;">
											<thead>
												<tr class="'.$team.'colors">
													<th class="'.$team.'colors" style="border: 0px !important;">Name</th>
													<th class="'.$team.'colors" style="border: 0px !important;">NO</th>
													<th class="'.$team.'colors" style="border: 0px !important;">POS</th>
													<th class="'.$team.'colors" style="border: 0px !important;">Team</th>
													<th class="'.$team.'colors" style="border: 0px !important;">Height</th>
													<th class="'.$team.'colors" style="border: 0px !important;">Weight</th>
													<th class="'.$team.'colors" style="border: 0px !important;">Birthday</th>
													<th class="'.$team.'colors" style="border: 0px !important;">Exp</th>
													<th class="'.$team.'colors" style="border: 0px !important;">College</th>
												</tr>
											</thead>
											<tbody>
												<tr>
													<td style="border: 0px !important;padding-left: 10px;"><a href="team.php?team='.$team.'"><img src="/assets/img/teams/'.$team.'.png" style="float:left;" /><span style="float:left;padding-top:10px;padding-left:10px;vertical-align: middle;font-weight: bold;display: inline-block;"><h5><strong><span class="label '.$team.'colors">'.$first_name.' '.$last_name.'</span></h5></strong></span></a></td>
													<td style="border: 0px !important;"><h5><strong><span class="label '.$team.'colors">'.$uniform_number.'</span></h5></strong></td>
													<td style="border: 0px !important;"><h5><strong><span class="label '.$team.'colors">'.$position.'</span></h5></strong></td>
													<td style="border: 0px !important;"><h5><strong><a href="team.php?team='.$team.'"><span class="label '.$team.'colors">'.$team.'</span></a></h5></strong></td>
													<td style="border: 0px !important;"><h5><strong><span class="label '.$team.'colors">'.$height.'</span></h5></strong></td>
													<td style="border: 0px !important;"><h5><strong><span class="label '.$team.'colors">'.$weight.'</span></h5></strong></td>
													<td style="border: 0px !important;"><h5><strong><span class="label '.$team.'colors">'.$birthdate.'</span></h5></strong></td>
													<td style="border: 0px !important;"><h5><strong><span class="label '.$team.'colors">'.$exp.'</span></h5></strong></td>
													<td style="border: 0px !important;"><h5><strong><span class="label '.$team.'colors">'.$college.'</span></h5></strong></td>
												</tr>
											</tbody>
										</table>
									</div>
									'.$bottom_line.'
                            	</div>
                            	'.$tabbody.'
							</div>
						</div>
                    </div>';
    $i++;

if ($week > 1) {
	$weekfloat = $week - 1;
	$lastweek = '/?type='.$season_type.'&year='.$year.'&week='.$weekfloat;
}
if ($year > 2010) {
	$yearfloat = $year - 1;
	$lyear = '/?type='.$season_type.'&year='.$yearfloat.'&week='.$week;
}

require ASSETS . 'player-page' . EXT;