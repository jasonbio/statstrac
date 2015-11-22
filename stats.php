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

if ($_GET["team"]) { $team = $_GET["team"]; }
if ($_GET["team_id"]) { $team = $_GET["team_id"]; }
if ($_GET["week"]) { $week = $_GET["week"]; }
if ($_GET["year"]) { $year = $_GET["year"]; }
if ($_GET["type"]) { $season_type = $_GET["type"]; }
if (isset($_GET["view"])) { $view = $_GET["view"]; $viewcontext = '&view='.$view; } else { $viewcontext = ''; }
if (!isset($view)) { header('Location: /404.html'); die(); }

if (!isset($week) && !isset($year) && !isset($season_type)) {
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
	$sort = '&order=start_time.asc';
	$sortdropdown = '<li class="active"><a href="?sort=date&type='.$season_type.'&year='.$year.'&week='.$week.'">Start Date</a></li>
		<li><a href="?sort=completed&type='.$season_type.'&year='.$year.'&week='.$week.'">Completed Games</a></li>
		<li><a href="?sort=score&type='.$season_type.'&year='.$year.'&week='.$week.'">Most Score</a></li>';
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

	if ($sort === "date") {
		$sort = '&order=start_time.asc';
		$sortdropdown = '<li class="active"><a href="?sort=date&type='.$season_type.'&year='.$year.'&week='.$week.'">Start Date</a></li>
		<li><a href="?sort=completed&type='.$season_type.'&year='.$year.'&week='.$week.'">Completed Games</a></li>
		<li><a href="?sort=score&type='.$season_type.'&year='.$year.'&week='.$week.'">Most Score</a></li>';
	} else if ($sort === "completed") {
		$sort = '&order=finished.asc';
		$sortdropdown = '<li><a href="?sort=date&type='.$season_type.'&year='.$year.'&week='.$week.'">Start Date</a></li>
		<li class="active"><a href="?sort=completed&type='.$season_type.'&year='.$year.'&week='.$week.'">Completed Games</a></li>
		<li><a href="?sort=score&type='.$season_type.'&year='.$year.'&week='.$week.'">Most Score</a></li>';
	} else if ($sort === "score") {
		$sort = '&order=home_score.desc,away_score,desc';
		$sortdropdown = '<li><a href="?sort=date&type='.$season_type.'&year='.$year.'&week='.$week.'">Start Date</a></li>
		<li><a href="?sort=completed&type='.$season_type.'&year='.$year.'&week='.$week.'">Completed Games</a></li>
		<li class="active"><a href="?sort=score&type='.$season_type.'&year='.$year.'&week='.$week.'">Most Score</a></li>';
	}
	if (!isset($sort)) {
		$sort = '&order=start_time.asc';
		$sortdropdown = '<li class="active"><a href="?sort=date&type='.$season_type.'&year='.$year.'&week='.$week.'">Start Date</a></li>
			<li><a href="?sort=completed&type='.$season_type.'&year='.$year.'&week='.$week.'">Completed Games</a></li>
			<li><a href="?sort=score&type='.$season_type.'&year='.$year.'&week='.$week.'">Most Score</a></li>';
	}
}

$context = $viewcontext;
$i = 0;
// NAV FUNCTIONS - WEEKS
$grab_weeks = CACHE . 'game/game?season_type=eq.'.$season_type.'&season_year=eq.'.$year.'&select=week,season_type&order=start_time.asc.json';
if (file_exists($grab_weeks)) {
	$grabweeksdata = file_get_contents($grab_weeks);
	$grabweeksobj = json_decode($grabweeksdata, true);
} else {
	$msg = file_get_contents($postgrest . 'game?season_type=eq.'.$season_type.'&season_year=eq.'.$year.'&select=week,season_type&order=start_time.asc');
	$f = fopen($grab_weeks, "w+");
	fwrite($f, $msg);
	fclose($f);
	$grabweeksobj = json_decode($msg, true);
}
foreach ($grabweeksobj as $key => $val) {
	$totalweeks = $val["week"];
	$totalseasontypes[$i] = $val["season_type"];
	$i++;
}
for ($i = 1; $i < $totalweeks+1; $i++) {
	if ($i == $week) {
		$weeksdropdown .= '<li class="active"><a href="?type='.$season_type.'&year='.$year.'&week='.$i.$context.'">Week '.$i.'</a></li>';
	} else {
		$weeksdropdown .= '<li><a href="?type='.$season_type.'&year='.$year.'&week='.$i.$context.'">Week '.$i.'</a></li>';
	}
}
$i = 0;
// NAV FUNCTIONS - YEARS
for ($i = $startyear; $i < $currentyear + 1; $i++) {
	if ($i == $year) {
		$yearsdropdown .= '<li class="active"><a href="?type='.$season_type.'&year='.$i.$context.'">'.$i.'</a></li>';
	} else {
		$yearsdropdown .= '<li><a href="?type='.$season_type.'&year='.$i.$context.'">'.$i.'</a></li>';
	}
}

$totalseasontypes = array_unique($totalseasontypes);
foreach ($totalseasontypes as $key => $val) {
	if ($val == $season_type) {
		$seasontypedropdown .= '<li class="active"><a href="?type='.$val.'&year='.$year.'&week='.$week.$context.'">'.$val.'</a></li>';
	} else {
		$seasontypedropdown .= '<li><a href="?type='.$val.'&year='.$year.'&week='.$week.$context.'">'.$val.'</a></li>';
	}
}
$i = 0;

$passing_stats_away = '';
$passing_stats_home = '';

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

$passing_sk_away = 0;
$passing_fumbles_lost = 0;
$passing_fumbles_tot = 0;
$passing_avg_away = 0;
$passing_ydsg_away = 0;
$summary_table_final = '';
$total_agg_tds_passing_away = 0;

$summary_table_rushing = '';

$playedgames = array();
$totalscore = 0;
$totalwon = 0;
$totallost = 0;
$totaltied = 0;
$p = 0;
$passing_rec = array();
$passing_play = array();
$name_rec = array();
$team_rec = array();
$pos_rec = array();

$leaderindex = array();
$passing_att_away = array();
$passing_cmp_away = array();
$passing_yds_away = array();
$passing_tds_away = array();
$passing_int_away = array();
$passing_id_away = array();
$passing_sk_away = array();
$passing_fumbles_lost = array();
$passing_fumbles_tot = array();
$total_agg_tds_passing_away = array();
$graph_stat_title = '';

$rushing_rec = array();
$rushing_att_away = array();
$rushing_tds_away = array();
$rushing_yds_away = array();
$rushing_fumbles_lost_away = array();
$rushing_fumbles_tot_away = array();

$receiving_rec = array();
$receiving_rec_away = array();
$receiving_tds_away = array();
$receiving_yds_away = array();
$receiving_fumbles_lost_away = array();
$receiving_fumbles_tot_away = array();
$rank = 0;

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

	if ($view === "passing") {

			// PASSING AWAY


		$passing_string_away = 'http://localhost:2096/play_player?gsis_id=eq.'.$val["gsis_id"].'&select=team,player_id,passing_att,passing_cmp,passing_int,passing_sk,passing_tds,passing_yds,fumbles_lost,fumbles_tot&team=not.eq.UNK&passing_att=not.eq.0';
		$jsonpassingaway = file_get_contents($passing_string_away);
		$objpassingaway = json_decode($jsonpassingaway,true);

		foreach ($objpassingaway as $pass => $stat) {
			$tempuid = $stat["player_id"];
			$passing_rec["".$stat["player_id"].""] = $stat["player_id"];
			$jsonplayeraway = file_get_contents('http://localhost:2096/player?player_id=eq.'.$stat["player_id"].'&team=not.eq.UNK');
			$objplayeraway = json_decode($jsonplayeraway,true);
			$name_rec["".$stat["player_id"].""] = $objplayeraway[0]["first_name"][0].".".$objplayeraway[0]["last_name"];
			$team_rec["".$stat["player_id"].""] = $objplayeraway[0]["team"];
			$pos_rec["".$stat["player_id"].""] = $objplayeraway[0]["position"];

			$passing_att_away["".$stat["player_id"].""] = $passing_att_away["".$stat["player_id"].""] + $stat["passing_att"];
			$passing_cmp_away["".$stat["player_id"].""] = $passing_cmp_away["".$stat["player_id"].""] + $stat["passing_cmp"];
			$passing_yds_away["".$stat["player_id"].""] = $passing_yds_away["".$stat["player_id"].""] + $stat["passing_yds"];
			$passing_tds_away["".$stat["player_id"].""] = $passing_tds_away["".$stat["player_id"].""] + $stat["passing_tds"];
			$passing_int_away["".$stat["player_id"].""] = $passing_int_away["".$stat["player_id"].""] + $stat["passing_int"];
			$passing_sk_away["".$stat["player_id"].""] = $passing_sk_away["".$stat["player_id"].""] + $stat["passing_sk"];
			$passing_fumbles_lost["".$stat["player_id"].""] = $passing_fumbles_lost["".$stat["player_id"].""] + $stat["fumbles_lost"];
			$passing_fumbles_tot["".$stat["player_id"].""] = $passing_fumbles_tot["".$stat["player_id"].""] + $stat["fumbles_tot"];
			$total_agg_tds_passing_away["".$stat["player_id"].""] = $total_agg_tds_passing_away["".$stat["player_id"].""] + $stat["passing_tds"];
		}


	} else if ($view === "rushing") {

		$rushing_string_away = 'http://localhost:2096/play_player?gsis_id=eq.'.$val["gsis_id"].'&select=player_id,team,rushing_att,rushing_tds,rushing_yds,fumbles_lost,fumbles_tot&rushing_att=not.eq.0&team=not.eq.UNK';
			$jsonrushingaway = file_get_contents($rushing_string_away);
			$objrushingaway = json_decode($jsonrushingaway,true);

			foreach ($objrushingaway as $rush => $stat) {
				$rushing_rec["".$stat["player_id"].""] = $stat["player_id"];
				$jsonplayeraway = file_get_contents('http://localhost:2096/player?player_id=eq.'.$stat["player_id"].'&team=not.eq.UNK');
				$objplayeraway = json_decode($jsonplayeraway,true);
				$name_rec["".$stat["player_id"].""] = $objplayeraway[0]["first_name"][0].".".$objplayeraway[0]["last_name"];
				$team_rec["".$stat["player_id"].""] = $objplayeraway[0]["team"];
				$pos_rec["".$stat["player_id"].""] = $objplayeraway[0]["position"];

				$rushing_att_away["".$stat["player_id"].""] = $rushing_att_away["".$stat["player_id"].""] + $stat["rushing_att"];
				$rushing_tds_away["".$stat["player_id"].""] = $rushing_tds_away["".$stat["player_id"].""] + $stat["rushing_tds"];
				$rushing_yds_away["".$stat["player_id"].""] = $rushing_yds_away["".$stat["player_id"].""] + $stat["rushing_yds"];
				$rushing_fumbles_lost_away["".$stat["player_id"].""] = $rushing_fumbles_lost_away["".$stat["player_id"].""] + $stat["fumbles_lost"];
				$rushing_fumbles_tot_away["".$stat["player_id"].""] = $rushing_fumbles_tot_away["".$stat["player_id"].""] + $stat["fumbles_tot"];
			}

	} else if ($view === "receiving") {

		$receiving_string_away = 'http://localhost:2096/play_player?gsis_id=eq.'.$val["gsis_id"].'&select=player_id,team,receiving_rec,receiving_tds,receiving_yds,fumbles_lost,fumbles_tot&receiving_rec=not.eq.0&team=not.eq.UNK';
			$jsonreceivingaway = file_get_contents($receiving_string_away);
			$objreceivingaway = json_decode($jsonreceivingaway,true);

			foreach ($objreceivingaway as $receive => $stat) {
				$receiving_rec["".$stat["player_id"].""] = $stat["player_id"];
				$jsonplayeraway = file_get_contents('http://localhost:2096/player?player_id=eq.'.$stat["player_id"].'&team=not.eq.UNK');
				$objplayeraway = json_decode($jsonplayeraway,true);
				$name_rec["".$stat["player_id"].""] = $objplayeraway[0]["first_name"][0].".".$objplayeraway[0]["last_name"];
				$team_rec["".$stat["player_id"].""] = $objplayeraway[0]["team"];
				$pos_rec["".$stat["player_id"].""] = $objplayeraway[0]["position"];

				if (is_numeric($stat["receiving_rec"]) && is_numeric($stat["receiving_tds"]) && is_numeric($stat["receiving_yds"]) && is_numeric($stat["fumbles_lost"]) && is_numeric($stat["fumbles_tot"])) {
					$receiving_rec_away["".$stat["player_id"].""] = $receiving_rec_away["".$stat["player_id"].""] + $stat["receiving_rec"];
					$receiving_tds_away["".$stat["player_id"].""] = $receiving_tds_away["".$stat["player_id"].""] + $stat["receiving_tds"];
					$receiving_yds_away["".$stat["player_id"].""] = $receiving_yds_away["".$stat["player_id"].""] + $stat["receiving_yds"];
					$receiving_fumbles_lost_away["".$stat["player_id"].""] = $receiving_fumbles_lost_away["".$stat["player_id"].""] + $stat["fumbles_lost"];
					$receiving_fumbles_tot_away["".$stat["player_id"].""] = $receiving_fumbles_tot_away["".$stat["player_id"].""] + $stat["fumbles_tot"];
				} else {
					continue;
				}
			}


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
			

		if ($view === "passing") {

			$passing_rec = array_unique($passing_rec);

			foreach ($passing_rec as $pass => $stat) {

					$passing_ydsg_away = $passing_yds_away["".$stat.""] / ($wins["".$team_rec["".$stat.""].""] + $losses["".$team_rec["".$stat.""].""] + $ties["".$team_rec["".$stat.""].""]);

					$passing_attg_away = $passing_att_away["".$stat.""] / ($wins["".$team_rec["".$stat.""].""] + $losses["".$team_rec["".$stat.""].""] + $ties["".$team_rec["".$stat.""].""]);

					$passing_stats_away .= "<tr>
								<td></td>
								<td><a href='player.php?player_id=".$stat."'>".$name_rec["".$stat.""]."</a></td>
								 <td><a href='team.php?team=".$team_rec["".$stat.""]."'>".$team_rec["".$stat.""]."</a></td>
								 <td>".$pos_rec["".$stat.""]."</td>
								 <td>".$passing_yds_away["".$stat.""]."</td>
								 <td>".$passing_ydsg_away."</td>
								 <td>".$passing_cmp_away["".$stat.""]."</td>
								 <td>".$passing_att_away["".$stat.""]."</td>
								 <td>".$passing_attg_away."</td>
								 <td>".$total_agg_tds_passing_away["".$stat.""]."</td>
								 <td>".$passing_int_away["".$stat.""]."</td>
								 <td>".$passing_sk_away["".$stat.""]."</td>
								 <td>".$passing_fumbles_tot["".$stat.""]."</td>
								 </tr>";
			}


			$summary_table_final = '<table id="data-table" class="table table-striped table-bordered nowrap display" width="100%" style="margin-bottom:0px;border: 0px !important;">
								<thead>
									<tr class="bg-black-darker" style="border: 0px !important;">
										<th style="border: 0px !important;">&nbsp;</th>
										<th style="border: 0px !important;"></th>
										<th style="border: 0px !important;"></th>
										<th style="border: 0px !important;"></th>
										<th style="border: 0px !important;"></th>
										<th style="border: 0px !important;"></th>
										<th style="border: 0px !important;"></th>
										<th style="border: 0px !important;"></th>
										<th style="border: 0px !important;"></th>
										<th style="border: 0px !important;"></th>
										<th style="border: 0px !important;"></th>
										<th style="border: 0px !important;"></th>
										<th style="border: 0px !important;"></th>
									</tr>
									<tr class="active">
										<th>Rank</th>
										<th>Name</th>
										<th>Team</th>
										<th>POS</th>
										<th>YDS</th>
										<th>YDS/G</th>
										<th>COMP</th>
										<th>ATT</th>
										<th>ATT/G</th>
										<th>TD</th>
										<th>INT</th>
										<th>SCK</th>
										<th>FUM</th>
									</tr>
								</thead>
								<tbody>
									'.$passing_stats_away.'
								</tbody>
							</table>
							</div>';

			$graph_stat_title = 'Passing Yard Leaders';

			$leaderindex = $passing_yds_away;

	} else if ($view === "rushing") {

		$rushing_rec = array_unique($rushing_rec);

		foreach ($rushing_rec as $rush => $stat) {

			$rushing_ydsg_away = $rushing_yds_away["".$stat.""] / ($wins["".$team_rec["".$stat.""].""] + $losses["".$team_rec["".$stat.""].""] + $ties["".$team_rec["".$stat.""].""]);

			$rushing_attg_away = $rushing_att_away["".$stat.""] / ($wins["".$team_rec["".$stat.""].""] + $losses["".$team_rec["".$stat.""].""] + $ties["".$team_rec["".$stat.""].""]);

				$rushing_stats_away .= "<tr>
							<td></td>
							<td><a href='player.php?player_id=".$stat."'>".$name_rec["".$stat.""]."</a></td>
							 <td><a href='team.php?team=".$team_rec["".$stat.""]."'>".$team_rec["".$stat.""]."</a></td>
							 <td>".$pos_rec["".$stat.""]."</td>
							 <td>".$rushing_yds_away["".$stat.""]."</td>
							 <td>".$rushing_ydsg_away."</td>
							 <td>".$rushing_att_away["".$stat.""]."</td>
							 <td>".$rushing_attg_away."</td>
							 <td>".$rushing_tds_away["".$stat.""]."</td>
							 <td>".$rushing_fumbles_tot_away["".$stat.""]."</td>
							 </tr>";
		}


		$summary_table_final = '<table id="data-table" class="table table-striped table-bordered nowrap display" width="100%" style="margin-bottom:0px;border: 0px !important;">
							<thead>
								<tr class="bg-black-darker" style="border: 0px !important;">
									<th style="border: 0px !important;">&nbsp;</th>
									<th style="border: 0px !important;"></th>
									<th style="border: 0px !important;"></th>
									<th style="border: 0px !important;"></th>
									<th style="border: 0px !important;"></th>
									<th style="border: 0px !important;"></th>
									<th style="border: 0px !important;"></th>
									<th style="border: 0px !important;"></th>
									<th style="border: 0px !important;"></th>
									<th style="border: 0px !important;"></th>
								</tr>
								<tr class="active">
									<th>Rank</th>
									<th>Name</th>
									<th>Team</th>
									<th>POS</th>
									<th>YDS</th>
									<th>YDS/G</th>
									<th>ATT</th>
									<th>ATT/G</th>
									<th>TD</th>
									<th>FUM</th>
								</tr>
							</thead>
							<tbody>
								'.$rushing_stats_away.'
							</tbody>
						</table>
						</div>';

			$graph_stat_title = 'Rushing Yard Leaders';

			$leaderindex = $rushing_yds_away;

	} else if ($view === "receiving") {

		$receiving_rec = array_unique($receiving_rec);

		foreach ($receiving_rec as $receive => $stat) {

			$receiving_ydsg_away = $receiving_yds_away["".$stat.""] / ($wins["".$team_rec["".$stat.""].""] + $losses["".$team_rec["".$stat.""].""] + $ties["".$team_rec["".$stat.""].""]);

				$receiving_stats_away .= "<tr>
							<td></td>
							<td><a href='player.php?player_id=".$stat."'>".$name_rec["".$stat.""]."</a></td>
							 <td><a href='team.php?team=".$team_rec["".$stat.""]."'>".$team_rec["".$stat.""]."</a></td>
							 <td>".$pos_rec["".$stat.""]."</td>
							 <td>".$receiving_yds_away["".$stat.""]."</td>
							 <td>".$receiving_ydsg_away."</td>
							 <td>".$receiving_rec_away["".$stat.""]."</td>
							 <td>".$receiving_tds_away["".$stat.""]."</td>
							 <td>".$receiving_fumbles_tot_away["".$stat.""]."</td>
							 </tr>";
		}


		$summary_table_final = '<table id="data-table" class="table table-striped table-bordered nowrap display" width="100%" style="margin-bottom:0px;border: 0px !important;">
							<thead>
								<tr class="bg-black-darker" style="border: 0px !important;">
									<th style="border: 0px !important;">&nbsp;</th>
									<th style="border: 0px !important;"></th>
									<th style="border: 0px !important;"></th>
									<th style="border: 0px !important;"></th>
									<th style="border: 0px !important;"></th>
									<th style="border: 0px !important;"></th>
									<th style="border: 0px !important;"></th>
									<th style="border: 0px !important;"></th>
									<th style="border: 0px !important;"></th>
								</tr>
								<tr class="active">
									<th>Rank</th>
									<th>Name</th>
									<th>Team</th>
									<th>POS</th>
									<th>YDS</th>
									<th>YDS/G</th>
									<th>REC</th>
									<th>TDS</th>
									<th>FUM</th>
								</tr>
							</thead>
							<tbody>
								'.$receiving_stats_away.'
							</tbody>
						</table>
						</div>';

			$graph_stat_title = 'Receiving Yard Leaders';

			$leaderindex = $receiving_yds_away;

				//$master_passing_yds_away["".$objplayeraway[0]["first_name"][0].".".$objplayeraway[0]["last_name"]." ".$date." ".$passing_id_away.""] = $passing_yds_away;
				//$master_passing_yds_away_summary["".$passing_id_away.""] = $passing_stats_away_sum;

				/*// PASSING HOME

				$passing_string_home = 'http://localhost:2096/play_player?gsis_id=eq.'.$date.'&team=eq.'.$team.'&select=team,player_id,passing_att,passing_cmp,passing_cmp_air_yds,passing_incmp,passing_incmp_air_yds,passing_int,passing_sk,passing_sk_yds,passing_tds,passing_twopta,passing_twoptm,passing_twoptmissed,passing_yds&passing_att=not.eq.0&order=team.asc,player_id.desc';
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

				$master_passing_yds_home["".$objplayerhome[0]["gsis_name"]." ".$date." ".$passing_id_home.""] = $passing_yds_home;
				$master_passing_yds_home_summary["".$passing_id_home.""] = $passing_stats_home_sum;*/

				/*// RUSHING AWAY

				$rushing_string_away = 'http://localhost:2096/play_player?gsis_id=eq.'.$date.'&team=eq.'.$team.'&select=team,player_id,rushing_att,rushing_loss,rushing_loss_yds,rushing_tds,rushing_twopta,rushing_twoptm,rushing_twoptmissed,rushing_yds&order=team.asc,player_id.desc';
				$jsonrushingaway = file_get_contents($rushing_string_away);
				$objrushingaway = json_decode($jsonrushingaway,true);

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
					$player_string_away = 'http://localhost:2096/player?player_id=eq.'.$rushstat;
					$player_save_id = $rushstat;
					$jsonplayeraway = file_get_contents($player_string_away);
					$objplayeraway = json_decode($jsonplayeraway,true);
					$name = $objplayeraway[0]["first_name"][0].".".$objplayeraway[0]["last_name"];
				    $rushing_string_away = 'http://localhost:2096/play_player?gsis_id=eq.'.$date.'&team=eq.'.$team.'&player_id=eq.'.$rushstat.'&select=team,player_id,rushing_att,rushing_loss,rushing_loss_yds,rushing_tds,rushing_twopta,rushing_twoptm,rushing_twoptmissed,rushing_yds&rushing_att=not.eq.0&order=team.asc,player_id.desc';
				    $jsonrushingaway = file_get_contents($rushing_string_away);
				    $objrushingaway = json_decode($jsonrushingaway,true);
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
					if ($rushing_att_away !== 0 || $rushing_yds_away !== 0 || $rushing_tds_away !== 0 || $rushing_loss_away !== 0) {
						$total_agg_tds_rushing_away = $total_agg_tds_rushing_away + $rushing_tds_away;
						$rushing_stats_away .= "<tr><td><a href='player.php?player_id=".$player_save_id."'>".$name."</a></td>
									 <td>".$rushing_att_away."</td>
									 <td>".$rushing_yds_away."</td>
									 <td>".$rushing_tds_away."</td>
									 <td>".$rushing_loss_away."</td></tr>";
						$rushing_stats_away_sum = "<tr><td><a href='player.php?player_id=".$player_save_id."'>".$name."</a></td>
									 <td>".$rushing_att_away."</td>
									 <td>".$rushing_yds_away."</td>
									 <td>".$rushing_tds_away."</td>
									 <td>".$rushing_loss_away."</td></tr>";

						$master_rushing_yds_away["".$name." ".$date." ".$player_save_id.""] = $rushing_yds_away;
						$master_rushing_yds_away_summary["".$player_save_id.""] = $rushing_stats_away_sum;
					}
				}

				$rushing_stats_away_body = '<tr class="active">
												<th>Rushing</th>
												<th>ATT</th>
												<th>YDS</th>
												<th>TD</th>
												<th>LOSS</th></tr>'.$rushing_stats_away;*/

				/*// RUSHING HOME

				$rushing_string_home = 'http://localhost:2096/play_player?gsis_id=eq.'.$date.'&team=eq.'.$team.'&select=team,player_id,rushing_att,rushing_loss,rushing_loss_yds,rushing_tds,rushing_twopta,rushing_twoptm,rushing_twoptmissed,rushing_yds&rushing_att=not.eq.0&order=team.asc,player_id.desc';
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
				    $rushing_string_home = 'http://localhost:2096/play_player?gsis_id=eq.'.$date.'&team=eq.'.$team.'&player_id=eq.'.$rushstat.'&select=team,player_id,rushing_att,rushing_loss,rushing_loss_yds,rushing_tds,rushing_twopta,rushing_twoptm,rushing_twoptmissed,rushing_yds&rushing_att=not.eq.0&order=team.asc,player_id.desc';
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

						$master_rushing_yds_home["".$name." ".$date." ".$player_save_id.""] = $rushing_yds_home;
						$master_rushing_yds_home_summary["".$player_save_id.""] = $rushing_stats_home_sum;
					}
				}

				$rushing_stats_home_body = '<tr class="active">
												<th>Rushing</th>
												<th>ATT</th>
												<th>YDS</th>
												<th>TD</th>
												<th>LOSS</th></tr>'.$rushing_stats_home;*/

				/*// RECEIVING AWAY

				$receiving_string_away = 'http://localhost:2096/play_player?gsis_id=eq.'.$date.'&team=eq.'.$team.'&select=team,player_id,receiving_rec,receiving_tar,receiving_tds,receiving_twopta,receiving_twoptm,receiving_twoptmissed,receiving_yac_yds,receiving_yds&receiving_rec=not.eq.0&order=team.asc,player_id.desc';
				$jsonreceivingaway = file_get_contents($receiving_string_away);
				$objreceivingaway = json_decode($jsonreceivingaway,true);

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
					$player_string_away = 'http://localhost:2096/player?player_id=eq.'.$receivestat;
					$player_save_id = $receivestat;
					$jsonplayeraway = file_get_contents($player_string_away);
					$objplayeraway = json_decode($jsonplayeraway,true);
					$name = $objplayeraway[0]["first_name"][0].".".$objplayeraway[0]["last_name"];
				    $receiving_string_away = 'http://localhost:2096/play_player?gsis_id=eq.'.$date.'&team=eq.'.$team.'&player_id=eq.'.$receivestat.'&select=team,player_id,receiving_rec,receiving_tar,receiving_tds,receiving_twopta,receiving_twoptm,receiving_twoptmissed,receiving_yac_yds,receiving_yds&order=team.asc,player_id.desc';
				    $jsonreceivingaway = file_get_contents($receiving_string_away);
				    $objreceivingaway = json_decode($jsonreceivingaway,true);
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
					if ($receiving_rec_away !== 0 || $receiving_yds_away !== 0 || $receiving_tds_away !== 0 || $receiving_yac_away !== 0) {
						$total_agg_tds_receiving_away = $total_agg_tds_receiving_away + $receiving_tds_away;
						$receiving_stats_away .= "<tr><td><a href='player.php?player_id=".$player_save_id."'>".$name."</a></td>
									 <td>".$receiving_rec_away."</td>
									 <td>".$receiving_yds_away."</td>
									 <td>".$receiving_tds_away."</td>
									 <td>".$receiving_yac_away."</td></tr>";
						$receiving_stats_away_sum = "<tr><td><a href='player.php?player_id=".$player_save_id."'>".$name."</a></td>
									 <td>".$receiving_rec_away."</td>
									 <td>".$receiving_yds_away."</td>
									 <td>".$receiving_tds_away."</td>
									 <td>".$receiving_yac_away."</td></tr>";

						$master_receiving_yds_away["".$name." ".$date." ".$player_save_id.""] = $receiving_yds_away;
						$master_receiving_yds_away_summary["".$player_save_id.""] = $receiving_stats_away_sum;
					}
				}

				$receiving_stats_away_body = '<tr class="active">
												<th>Receiving</th>
												<th>REC</th>
												<th>YDS</th>
												<th>TD</th>
												<th>YAC</th></tr>'.$receiving_stats_away;*/

				/*// RECEIVING HOME

				$receiving_string_home = 'http://localhost:2096/play_player?gsis_id=eq.'.$date.'&team=eq.'.$team.'&select=team,player_id,receiving_rec,receiving_tar,receiving_tds,receiving_twopta,receiving_twoptm,receiving_twoptmissed,receiving_yac_yds,receiving_yds&receiving_rec=not.eq.0&order=team.asc,player_id.desc';
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
				    $receiving_string_home = 'http://localhost:2096/play_player?gsis_id=eq.'.$date.'&team=eq.'.$team.'&player_id=eq.'.$receivestat.'&select=team,player_id,receiving_rec,receiving_tar,receiving_tds,receiving_twopta,receiving_twoptm,receiving_twoptmissed,receiving_yac_yds,receiving_yds&order=team.asc,player_id.desc';
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

						$master_receiving_yds_home["".$name." ".$date." ".$player_save_id.""] = $receiving_yds_home;
						$master_receiving_yds_home_summary["".$player_save_id.""] = $receiving_stats_home_sum;
					}
				}

				$receiving_stats_home_body = '<tr class="active">
												<th>Receiving</th>
												<th>REC</th>
												<th>YDS</th>
												<th>TD</th>
												<th>YAC</th></tr>'.$receiving_stats_home;*/

				/*// FUMBLES away

				$fumble_string_away = 'http://localhost:2096/play_player?gsis_id=eq.'.$date.'&team=eq.'.$team.'&select=player_id';
				$jsonfumbleaway = file_get_contents($fumble_string_away);
				$objfumbleaway = json_decode($jsonfumbleaway,true);

				$fumble_id_away = array();
				$f = 0;

				foreach ($objfumbleaway as $fumble => $fumblestat) {
					$fumble_id_away[$f] = $fumblestat["player_id"];
					$f++;
				}

				$f = 0;

				$fumble_id_away = array_unique($fumble_id_away);

				foreach ($fumble_id_away as $fumble => $fumblestat) {

					$player_string_away = 'http://localhost:2096/player?player_id=eq.'.$fumblestat;
					$player_string_save = $fumblestat;
					$jsonplayeraway = file_get_contents($player_string_away);
					$objplayeraway = json_decode($jsonplayeraway,true);
					$name = $objplayeraway[0]["first_name"][0].".".$objplayeraway[0]["last_name"];
					$fumble_string_away = 'http://localhost:2096/play_player?gsis_id=eq.'.$date.'&team=eq.'.$team.'&player_id=eq.'.$fumblestat.'&select=fumbles_lost,fumbles_rec,fumbles_rec_tds,fumbles_tot,fumbles_rec_yds,defense_frec,defense_frec_yds';
					$jsonfumbleaway = file_get_contents($fumble_string_away);
					$objfumbleaway = json_decode($jsonfumbleaway,true);
					$fumbles_lost_away = 0;
					$fumbles_rec_away = 0;
					$fumbles_rec_tds_away = 0;
					$fumbles_rec_yds_away = 0;
					$fumbles_tot_away = 0;
					$fumbles_frec_away = 0;
					$fumbles_frec_yds_away = 0;
					foreach ($objfumbleaway as $fumble => $fumblestat) {
						$fumbles_lost_away = $fumbles_lost_away + $fumblestat["fumbles_lost"];
						$fumbles_rec_away = $fumbles_rec_away + $fumblestat["fumbles_rec"];
						$fumbles_rec_tds_away = $fumbles_rec_tds_away + $fumblestat["fumbles_rec_tds"];
						$fumbles_rec_yds_away = $fumbles_rec_yds_away + $fumblestat["fumbles_rec_yds"];
						$fumbles_tot_away = $fumbles_tot_away + $fumblestat["fumbles_tot"];
						$fumbles_frec_away = $fumbles_frec_away + $fumblestat["defense_frec"];
						$fumbles_frec_yds_away = $fumbles_frec_yds_away + $fumblestat["defense_frec_yds"];
					}
					if ($fumbles_lost_away !== 0 || $fumbles_rec_away !== 0 || $fumbles_rec_tds_away !== 0 || $fumbles_rec_yds_away !== 0 || $fumbles_tot_away !== 0 || $fumbles_frec_away !== 0 || $fumbles_frec_yds_away !== 0) {
						$total_agg_tds_frec_away = $total_agg_tds_frec_away + $fumbles_rec_tds_away;
						$fumble_stats_away .= "<tr><td><a href='player.php?player_id=".$player_string_save."'>".$name."</a></td>
								 <td>".$fumbles_tot_away."</td>
								 <td>".$fumbles_lost_away."</td>
								 <td>".$fumbles_frec_away."</td>
								 <td>".$fumbles_frec_yds_away."</td></tr>";
					}
				}

				$fumbles_stats_away_body = '<tr class="active">
												<th>Fumbles</th>
												<th>FUM</th>
												<th>LOST</th>
												<th>REC</th>
												<th>YDS</th></tr>'.$fumble_stats_away;*/

				/*// FUMBLES HOME

				$fumble_string_home = 'http://localhost:2096/play_player?gsis_id=eq.'.$date.'&team=eq.'.$team.'&select=player_id';
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
					$fumble_string_home = 'http://localhost:2096/play_player?gsis_id=eq.'.$date.'&team=eq.'.$team.'&player_id=eq.'.$fumblestat.'&select=fumbles_lost,fumbles_rec,fumbles_rec_tds,fumbles_tot,fumbles_rec_yds,defense_frec,defense_frec_yds';
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

				/*// KICKING away

				$kicking_string_away = 'http://localhost:2096/play_player?gsis_id=eq.'.$date.'&team=eq.'.$team.'&select=team,player_id,kicking_fga,kicking_fgm,kicking_xpa,kicking_xpmade,kicking_yds&order=team.asc,player_id.desc';
				$jsonkickingaway = file_get_contents($kicking_string_away);
				$objkickingaway = json_decode($jsonkickingaway,true);

				$kicking_id_away = array();
				$k = 0;

				foreach ($objkickingaway as $kicking => $kickingstat) {
					$kicking_id_away[$k] = $kickingstat["player_id"];
					$k++;
				}

				$k = 0;

				$kicking_id_away = array_unique($kicking_id_away);

				foreach ($kicking_id_away as $kicking => $kickingstat) {

					$player_string_away = 'http://localhost:2096/player?player_id=eq.'.$kickingstat;
					$player_string_save = $kickingstat;
					$jsonplayeraway = file_get_contents($player_string_away);
					$objplayeraway = json_decode($jsonplayeraway,true);
					$name = $objplayeraway[0]["first_name"][0].".".$objplayeraway[0]["last_name"];
					$kicking_string_away = 'http://localhost:2096/play_player?gsis_id=eq.'.$date.'&team=eq.'.$team.'&player_id=eq.'.$kickingstat.'&select=team,player_id,kicking_fga,kicking_fgm,kicking_xpa,kicking_xpmade,kicking_yds&order=team.asc,player_id.desc';
					$jsonkickingaway = file_get_contents($kicking_string_away);
					$objkickingaway = json_decode($jsonkickingaway,true);
					$kicking_fga_away = 0;
					$kicking_fgm_away = 0;
					$kicking_xpa_away = 0;
					$kicking_xpmade_away = 0;
					$kicking_yds_away = 0;
					foreach ($objkickingaway as $kicking => $kickingstat) {
						$kicking_fga_away = $kicking_fga_away + $kickingstat["kicking_fga"];
						$kicking_fgm_away = $kicking_fgm_away + $kickingstat["kicking_fgm"];
						$kicking_xpa_away = $kicking_xpa_away + $kickingstat["kicking_xpa"];
						$kicking_xpmade_away = $kicking_xpmade_away + $kickingstat["kicking_xpmade"];
						$kicking_yds_away = $kicking_yds_away + $kickingstat["kicking_yds"];
					}
					if ($kicking_fga_away !== 0 || $kicking_fgm_away !== 0 || $kicking_xpa_away !== 0 || $kicking_xpmade_away !== 0 || $kicking_yds_away !== 0) {
						$kicking_pts_away = ($kicking_fgm_away * 3) + $kicking_xpmade_away;
						$kicking_stats_away .= "<tr><td><a href='player.php?player_id=".$player_string_save."'>".$name."</a></td>
								 <td>".$kicking_fga_away."/".$kicking_fgm_away."</td>
								 <td>".$kicking_yds_away."</td>
								 <td>".$kicking_xpa_away."/".$kicking_xpmade_away."</td>
								 <td>".$kicking_pts_away."</td></tr>";
					}
				}

				$kicking_stats_away_body = '<tr class="active">
												<th>Kicking</th>
												<th>FG</th>
												<th>YDS</th>
												<th>XP</th>
												<th>PTS</th></tr>'.$kicking_stats_away;*/

				/*// KICKING home

				$kicking_string_home = 'http://localhost:2096/play_player?gsis_id=eq.'.$date.'&team=eq.'.$team.'&select=team,player_id,kicking_fga,kicking_fgm,kicking_xpa,kicking_xpmade,kicking_yds&order=team.asc,player_id.desc';
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
					$kicking_string_home = 'http://localhost:2096/play_player?gsis_id=eq.'.$date.'&team=eq.'.$team.'&player_id=eq.'.$kickingstat.'&select=team,player_id,kicking_fga,kicking_fgm,kicking_xpa,kicking_xpmade,kicking_yds&order=team.asc,player_id.desc';
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

				/*// punting away

				$punting_string_away = 'http://localhost:2096/play_player?gsis_id=eq.'.$date.'&team=eq.'.$team.'&select=team,player_id,punting_tot,punting_yds,punting_i20&order=team.asc,player_id.desc';
				$jsonpuntingaway = file_get_contents($punting_string_away);
				$objpuntingaway = json_decode($jsonpuntingaway,true);

				$punting_id_away = array();
				$p = 0;

				foreach ($objpuntingaway as $punting => $puntingstat) {
					$punting_id_away[$p] = $puntingstat["player_id"];
					$p++;
				}

				$p = 0;

				$punting_id_away = array_unique($punting_id_away);

				foreach ($punting_id_away as $punting => $puntingstat) {

					$player_string_away = 'http://localhost:2096/player?player_id=eq.'.$puntingstat;
					$player_string_save = $puntingstat;
					$jsonplayeraway = file_get_contents($player_string_away);
					$objplayeraway = json_decode($jsonplayeraway,true);
					$name = $objplayeraway[0]["first_name"][0].".".$objplayeraway[0]["last_name"];
					$punting_string_away = 'http://localhost:2096/play_player?gsis_id=eq.'.$date.'&team=eq.'.$team.'&player_id=eq.'.$puntingstat.'&select=team,player_id,punting_tot,punting_yds,punting_i20&order=team.asc,player_id.desc';
					$jsonpuntingaway = file_get_contents($punting_string_away);
					$objpuntingaway = json_decode($jsonpuntingaway,true);
					$punting_tot_away = 0;
					$punting_yds_away = 0;
					$punting_i20_away = 0;
					foreach ($objpuntingaway as $punting => $puntingstat) {
						$punting_tot_away = $punting_tot_away + $puntingstat["punting_tot"];
						$punting_yds_away = $punting_yds_away + $puntingstat["punting_yds"];
						$punting_i20_away = $punting_i20_away + $puntingstat["punting_i20"];
					}
					if ($punting_tot_away !== 0 || $punting_yds_away !== 0 || $punting_i20_away !== 0) {
						$punting_avg_away = number_format($punting_yds_away / $punting_tot_away, 1);
						$punting_stats_away .= "<tr><td><a href='player.php?player_id=".$player_string_save."'>".$name."</a></td>
								 <td>".$punting_tot_away."</td>
								 <td>".$punting_avg_away."</td>
								 <td>".$punting_i20_away."</td>
								 <td>".$punting_yds_away."</td></tr>";
					}
				}

				$punting_stats_away_body = '<tr class="active">
												<th>Punting</th>
												<th>NO</th>
												<th>AVG</th>
												<th>I20</th>
												<th>YDS</th></tr>'.$punting_stats_away;*/

				/*// punting home

				$punting_string_home = 'http://localhost:2096/play_player?gsis_id=eq.'.$date.'&team=eq.'.$team.'&select=team,player_id,punting_tot,punting_yds,punting_i20&order=team.asc,player_id.desc';
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
					$punting_string_home = 'http://localhost:2096/play_player?gsis_id=eq.'.$date.'&team=eq.'.$team.'&player_id=eq.'.$puntingstat.'&select=team,player_id,punting_tot,punting_yds,punting_i20&order=team.asc,player_id.desc';
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

				/*// kickret away

				$kickret_string_away = 'http://localhost:2096/play_player?gsis_id=eq.'.$date.'&team=eq.'.$team.'&select=team,player_id,kickret_fair,kickret_oob,kickret_ret,kickret_tds,kickret_touchback,kickret_yds&order=team.asc,player_id.desc';
				$jsonkickretaway = file_get_contents($kickret_string_away);
				$objkickretaway = json_decode($jsonkickretaway,true);

				$kickret_id_away = array();
				$k = 0;

				foreach ($objkickretaway as $kickret => $kickretstat) {
					$kickret_id_away[$k] = $kickretstat["player_id"];
					$k++;
				}

				$k = 0;

				$kickret_id_away = array_unique($kickret_id_away);

				foreach ($kickret_id_away as $kickret => $kickretstat) {

					$player_string_away = 'http://localhost:2096/player?player_id=eq.'.$kickretstat;
					$player_string_save = $kickretstat;
					$jsonplayeraway = file_get_contents($player_string_away);
					$objplayeraway = json_decode($jsonplayeraway,true);
					$name = $objplayeraway[0]["first_name"][0].".".$objplayeraway[0]["last_name"];
					$kickret_string_away = 'http://localhost:2096/play_player?gsis_id=eq.'.$date.'&team=eq.'.$team.'&player_id=eq.'.$kickretstat.'&select=team,player_id,kickret_fair,kickret_oob,kickret_ret,kickret_tds,kickret_touchback,kickret_yds&order=team.asc,player_id.desc';
					$jsonkickretaway = file_get_contents($kickret_string_away);
					$objkickretaway = json_decode($jsonkickretaway,true);
					$kickret_ret_away = 0;
					$kickret_tds_away = 0;
					$kickret_yds_away = 0;
					foreach ($objkickretaway as $kickret => $kickretstat) {
						$kickret_ret_away = $kickret_ret_away + $kickretstat["kickret_ret"];
						$kickret_tds_away = $kickret_tds_away + $kickretstat["kickret_tds"];
						$kickret_yds_away = $kickret_yds_away + $kickretstat["kickret_yds"];
					}
					if ($kickret_ret_away !== 0 || $kickret_tds_away !== 0 || $kickret_yds_away !== 0) {
						$total_agg_tds_kickret_away = $total_agg_tds_kickret_away + $kickret_tds_away;
						$kickret_avg_away = number_format($kickret_yds_away / $kickret_ret_away, 1);
						$kickret_stats_away .= "<tr><td><a href='player.php?player_id=".$player_string_save."'>".$name."</a></td>
								 <td>".$kickret_ret_away."</td>
								 <td>".$kickret_avg_away."</td>
								 <td>".$kickret_tds_away."</td>
								 <td>".$kickret_yds_away."</td></tr>";
					}
				}

				$kickret_stats_away_body = '<tr class="active">
												<th>Kickoff Returns</th>
												<th>NO</th>
												<th>AVG</th>
												<th>TD</th>
												<th>YDS</th></tr>'.$kickret_stats_away;*/

				/*// kickret home

				$kickret_string_home = 'http://localhost:2096/play_player?gsis_id=eq.'.$date.'&team=eq.'.$team.'&select=team,player_id,kickret_fair,kickret_oob,kickret_ret,kickret_tds,kickret_touchback,kickret_yds&order=team.asc,player_id.desc';
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
					$kickret_string_home = 'http://localhost:2096/play_player?gsis_id=eq.'.$date.'&team=eq.'.$team.'&player_id=eq.'.$kickretstat.'&select=team,player_id,kickret_fair,kickret_oob,kickret_ret,kickret_tds,kickret_touchback,kickret_yds&order=team.asc,player_id.desc';
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

				/*// puntret away

				$puntret_string_away = 'http://localhost:2096/play_player?gsis_id=eq.'.$date.'&team=eq.'.$team.'&select=team,player_id,puntret_fair,puntret_oob,puntret_tot,puntret_tds,puntret_touchback,puntret_yds&order=team.asc,player_id.desc';
				$jsonpuntretaway = file_get_contents($puntret_string_away);
				$objpuntretaway = json_decode($jsonpuntretaway,true);

				$puntret_id_away = array();
				$k = 0;

				foreach ($objpuntretaway as $puntret => $puntretstat) {
					$puntret_id_away[$k] = $puntretstat["player_id"];
					$k++;
				}

				$k = 0;

				$puntret_id_away = array_unique($puntret_id_away);

				foreach ($puntret_id_away as $puntret => $puntretstat) {

					$player_string_away = 'http://localhost:2096/player?player_id=eq.'.$puntretstat;
					$player_string_save = $puntretstat;
					$jsonplayeraway = file_get_contents($player_string_away);
					$objplayeraway = json_decode($jsonplayeraway,true);
					$name = $objplayeraway[0]["first_name"][0].".".$objplayeraway[0]["last_name"];
					$puntret_string_away = 'http://localhost:2096/play_player?gsis_id=eq.'.$date.'&team=eq.'.$team.'&player_id=eq.'.$puntretstat.'&select=team,player_id,puntret_fair,puntret_oob,puntret_tot,puntret_tds,puntret_touchback,puntret_yds&order=team.asc,player_id.desc';
					$jsonpuntretaway = file_get_contents($puntret_string_away);
					$objpuntretaway = json_decode($jsonpuntretaway,true);
					$puntret_tot_away = 0;
					$puntret_tds_away = 0;
					$puntret_yds_away = 0;
					foreach ($objpuntretaway as $puntret => $puntretstat) {
						$puntret_tot_away = $puntret_tot_away + $puntretstat["puntret_tot"];
						$puntret_tds_away = $puntret_tds_away + $puntretstat["puntret_tds"];
						$puntret_yds_away = $puntret_yds_away + $puntretstat["puntret_yds"];
					}
					if ($puntret_tot_away !== 0 || $puntret_tds_away !== 0 || $puntret_yds_away !== 0) {
						$total_agg_tds_puntret_away = $total_agg_tds_puntret_away + $puntret_tds_away;
						$puntret_avg_away = number_format($puntret_yds_away / $puntret_tot_away, 1);
						$puntret_stats_away .= "<tr><td><a href='player.php?player_id=".$player_string_save."'>".$name."</a></td>
								 <td>".$puntret_tot_away."</td>
								 <td>".$puntret_avg_away."</td>
								 <td>".$puntret_tds_away."</td>
								 <td>".$puntret_yds_away."</td></tr>";
					}
				}

				$puntret_stats_away_body = '<tr class="active">
												<th>Punt Returns</th>
												<th>NO</th>
												<th>AVG</th>
												<th>TD</th>
												<th>YDS</th></tr>'.$puntret_stats_away;*/

				/*// puntret home

				$puntret_string_home = 'http://localhost:2096/play_player?gsis_id=eq.'.$date.'&team=eq.'.$team.'&select=team,player_id,puntret_fair,puntret_oob,puntret_tot,puntret_tds,puntret_touchback,puntret_yds&order=team.asc,player_id.desc';
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
					$puntret_string_home = 'http://localhost:2096/play_player?gsis_id=eq.'.$date.'&team=eq.'.$team.'&player_id=eq.'.$puntretstat.'&select=team,player_id,puntret_fair,puntret_oob,puntret_tot,puntret_tds,puntret_touchback,puntret_yds&order=team.asc,player_id.desc';
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

				/*// defense away

				$defense_string_away = 'http://localhost:2096/play_player?gsis_id=eq.'.$date.'&team=eq.'.$team.'&select=team,player_id,defense_ast,defense_tkl,defense_sk,defense_int,defense_ffum&order=defense_tkl.desc,player_id.desc';
				$jsondefenseaway = file_get_contents($defense_string_away);
				$objdefenseaway = json_decode($jsondefenseaway,true);

				$defense_id_away = array();
				$d = 0;

				foreach ($objdefenseaway as $defense => $defensestat) {
					$defense_id_away[$d] = $defensestat["player_id"];
					$d++;
				}

				$d = 0;

				$defense_id_away = array_unique($defense_id_away);

				foreach ($defense_id_away as $defense => $defensestat) {

					$player_string_away = 'http://localhost:2096/player?player_id=eq.'.$defensestat;
					$player_string_save = $defensestat;
					$jsonplayeraway = file_get_contents($player_string_away);
					$objplayeraway = json_decode($jsonplayeraway,true);
					if ($objplayeraway[0]["position"] !== "ILB" && $objplayeraway[0]["position"] !== "SS" && $objplayeraway[0]["position"] !== "CB" && $objplayeraway[0]["position"] !== "OLB" && $objplayeraway[0]["position"] !== "FS" && $objplayeraway[0]["position"] !== "DE" && $objplayeraway[0]["position"] !== "NT" && $objplayeraway[0]["position"] !== "LB"  && $objplayeraway[0]["position"] !== "DT" && $objplayeraway[0]["position"] !== "MLB" && $objplayeraway[0]["position"] !== "S" && $objplayeraway[0]["position"] !== "DL" && $objplayeraway[0]["position"] !== "NB") {
						continue;
					}
					$name = $objplayeraway[0]["first_name"][0].".".$objplayeraway[0]["last_name"];
					$defense_string_away = 'http://localhost:2096/play_player?gsis_id=eq.'.$date.'&team=eq.'.$team.'&player_id=eq.'.$defensestat.'&select=team,player_id,defense_ast,defense_tkl,defense_sk,defense_int,defense_ffum,defense_int_tds,defense_frec_tds&order=defense_tkl.desc,player_id.desc';
					$jsondefenseaway = file_get_contents($defense_string_away);
					$objdefenseaway = json_decode($jsondefenseaway,true);
					$defense_ast_away = 0;
					$defense_tkl_away = 0;
					$defense_sk_away = 0;
					$defense_int_away = 0;
					$defense_ffum_away = 0;
					$defense_int_tds_away = 0;
					$defense_frec_tds_away = 0;
					foreach ($objdefenseaway as $defense => $defensestat) {
						$defense_ast_away = $defense_ast_away + $defensestat["defense_ast"];
						$defense_tkl_away = $defense_tkl_away + $defensestat["defense_tkl"];
						$defense_sk_away = $defense_sk_away + $defensestat["defense_sk"];
						$defense_int_away = $defense_int_away + $defensestat["defense_int"];
						$defense_ffum_away = $defense_ffum_away + $defensestat["defense_ffum"];
						$defense_int_tds_away = $defense_int_tds_away + $defensestat["defense_int_tds"];
						$defense_frec_tds_away = $defense_frec_tds_away + $defensestat["defense_frec_tds"];
					}
					if ($defense_ast_away !== 0 || $defense_tkl_away !== 0 || $defense_sk_away !== 0 || $defense_int_away !== 0 || $defense_ffum_away !== 0 || $defense_int_tds_away !== 0 || $defense_frec_tds_away !== 0) {
						$total_agg_tds_int_away = $total_agg_tds_int_away + $defense_int_tds_away;
						$total_agg_tds_frec_away = $total_agg_tds_frec_away + $defense_frec_tds_away;
						$defense_stats_away .= "<tr><td><a href='player.php?player_id=".$player_string_save."'>".$name."</a></td>
								 <td>".$defense_tkl_away."-".$defense_ast_away."</td>
								 <td>".$defense_sk_away."</td>
								 <td>".$defense_int_away."</td>
								 <td>".$defense_ffum_away."</td></tr>";
					}
				}

				$defense_stats_away_body = '<tr class="active">
												<th>Defense</th>
												<th>T-A</th>
												<th>SCK</th>
												<th>INT</th>
												<th>FF</th></tr>'.$defense_stats_away;*/

				/*// defense home

				$defense_string_home = 'http://localhost:2096/play_player?gsis_id=eq.'.$date.'&team=eq.'.$team.'&select=team,player_id,defense_ast,defense_tkl,defense_sk,defense_int,defense_int_tds,defense_ffum&order=defense_tkl.desc,player_id.desc';
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
					$defense_string_home = 'http://localhost:2096/play_player?gsis_id=eq.'.$date.'&team=eq.'.$team.'&player_id=eq.'.$defensestat.'&select=team,player_id,defense_ast,defense_tkl,defense_sk,defense_int,defense_ffum,defense_int_tds,defense_frec_tds&order=defense_tkl.desc,player_id.desc';
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


				// CALCULATE INDIVIDUAL GAME HIGHS
				//foreach ($master_passing_yds_away_summary as $val) {
					//$summary_passing_stats_away = $val;
				//}

				/*foreach ($master_passing_yds_home_summary as $val) {
					$summary_passing_stats_home = $val;
				}*/





				//$globalleader_passing_yds[array_search(max($master_passing_yds_away), $master_passing_yds_away)] = max($master_passing_yds_away);
				//$globalleader_passing_yds[array_search(max($master_passing_yds_home), $master_passing_yds_home)] = max($master_passing_yds_home);

				//$globalleader_rushing_yds[array_search(max($master_rushing_yds_away), $master_rushing_yds_away)] = max($master_rushing_yds_away);
				//$globalleader_rushing_yds[array_search(max($master_rushing_yds_home), $master_rushing_yds_home)] = max($master_rushing_yds_home);

				//$globalleader_receiving_yds[array_search(max($master_receiving_yds_away), $master_receiving_yds_away)] = max($master_receiving_yds_away);
				//$globalleader_receiving_yds[array_search(max($master_receiving_yds_home), $master_receiving_yds_home)] = max($master_receiving_yds_home);


				// FRONT SUMMARY HOME

				/*$front_summary_home_string = 'http://localhost:2096/play?gsis_id=eq.'.$date.'&pos_team=eq.'.$team.'&order=drive_id.asc,play_id.asc';
				$jsonfront_summary_home = file_get_contents($front_summary_home_string);
				$objfront_summary_home = json_decode($jsonfront_summary_home,true);

				foreach ($objfront_summary_home as $play => $stat) {
					// FIRST DOWNS
					$firstdowns_passing_home = $firstdowns_passing_home + $stat["passing_first_down"];
					$firstdowns_rushing_home = $firstdowns_rushing_home + $stat["rushing_first_down"];
					$firstdowns_penalty_home = $firstdowns_penalty_home + $stat["penalty_first_down"];
					$firstdowns_home_total = $firstdowns_home_total + $stat["first_down"];
					// Third downs
					$third_down_att_home = $third_down_att_home + $stat["third_down_att"];
					$third_down_conv_home = $third_down_conv_home + $stat["third_down_conv"];
					// Fourth downs
					$fourth_down_att_home = $fourth_down_att_home + $stat["fourth_down_att"];
					$fourth_down_conv_home = $fourth_down_conv_home + $stat["fourth_down_conv"];
					// Penalty
					$penalty_yards_home = $penalty_yards_home + $stat["penalty_yds"];
					$penalty_home = $penalty_home + $stat["penalty"];
				}

				if ($third_down_conv_home !== 0) {
					$third_down_eff_home = floor($third_down_conv_home * 100 / $third_down_att_home) . '%';
				} else {
					$third_down_eff_home = "0%";
				}

				if ($fourth_down_conv_home !== 0) {
					$fourth_down_eff_home = floor($fourth_down_conv_home * 100 / $fourth_down_att_home) . '%';
				} else {
					$fourth_down_eff_home = "0%";
				}

				if ($total_agg_passing_plays_home !== 0) {
					$total_home_avg_passing_yards_play = number_format($total_agg_passing_yds_home / $total_agg_passing_plays_home, 1);
				} else {
					$total_home_avg_passing_yards_play = "0%";
				}

				if ($total_agg_rushing_plays_home !== 0) {
					$total_home_avg_rushing_yards_play = number_format($total_agg_rushing_yds_home / $total_agg_rushing_plays_home, 1);
				} else {
					$total_home_avg_rushing_yards_play = "0%";
				}

				$firstdowns_home_table = '<tr class="active">
											<td><strong>Total First Downs</strong></td>
											<td><strong>'.$firstdowns_home_total.'</strong></td>
										</tr>
										<tr>
											<td>By Passing</td>
											<td>'.$firstdowns_passing_home.'</td>
										</tr>
										<tr>
											<td>By Rushing</td>
											<td>'.$firstdowns_rushing_home.'</td>
										</tr>
										<tr>
											<td>By Penalty</td>
											<td>'.$firstdowns_penalty_home.'</td>
										</tr>';

				$thirddowns_home_table = '<tr class="active">
											<td><strong>Third Down Efficiency</strong></td>
											<td><strong>'.$third_down_eff_home.'</strong></td>
										</tr>
										<tr>
											<td>Attempts</td>
											<td>'.$third_down_att_home.'</td>
										</tr>
										<tr>
											<td>Conversions</td>
											<td>'.$third_down_conv_home.'</td>
										</tr>';

				$fourthdowns_home_table = '<tr class="active">
											<td><strong>Fourth Down Efficiency</strong></td>
											<td><strong>'.$fourth_down_eff_home.'</strong></td>
										</tr>
										<tr>
											<td>Attempts</td>
											<td>'.$fourth_down_att_home.'</td>
										</tr>
										<tr>
											<td>Conversions</td>
											<td>'.$fourth_down_conv_home.'</td>
										</tr>';

				$net_yards_home_table = '<tr class="active">
											<td><strong>Total Net Yards</strong></td>
											<td><strong>'.$total_home_net_yards_gained.'</strong></td>
										</tr>';

				$net_yards_passing_home_table = '<tr class="active">
											<td><strong>Net Yards Passing</strong></td>
											<td><strong>'.$total_home_net_passing_yards_gained.'</strong></td>
										</tr>
										<tr>
											<td>Total Passing Plays</td>
											<td>'.$total_agg_passing_plays_home.'</td>
										</tr>
										<tr>
											<td>Average Gain per Passing Play</td>
											<td>'.$total_home_avg_passing_yards_play.'</td>
										</tr>';

				$net_yards_rushing_home_table = '<tr class="active">
											<td><strong>Net Yards Rushing</strong></td>
											<td><strong>'.$total_agg_rushing_yds_home.'</strong></td>
										</tr>
										<tr>
											<td>Total Rushing Plays</td>
											<td>'.$total_agg_rushing_plays_home.'</td>
										</tr>
										<tr>
											<td>Average Gain per Rushing Play</td>
											<td>'.$total_home_avg_rushing_yards_play.'</td>
										</tr>';

				$touchdowns_home_table = '<tr class="active">
											<td><strong>Touchdowns</strong></td>
											<td><strong>'.$total_agg_tds_home.'</strong></td>
										</tr>
										<tr>
											<td>By Passing</td>
											<td>'.$total_agg_tds_passing_home.'</td>
										</tr>
										<tr>
											<td>By Rushing</td>
											<td>'.$total_agg_tds_rushing_home.'</td>
										</tr>
										<tr>
											<td>By Interceptions</td>
											<td>'.$total_agg_tds_int_home.'</td>
										</tr>
										<tr>
											<td>By Fumble Returns</td>
											<td>'.$total_agg_tds_frec_home.'</td>
										</tr>
										<tr>
											<td>By Kickoff Returns</td>
											<td>'.$total_agg_tds_kickret_home.'</td>
										</tr>
										<tr>
											<td>By Punt Returns</td>
											<td>'.$total_agg_tds_puntret_home.'</td>
										</tr>';

				$front_summary_home_table = '<div class="table-responsive">
				<table class="table" style="margin-bottom:0px;">
									<thead>
										<tr class="'.$team.'colors">
											<th class="border-color: transparent!important;"><a href="team.php?team='.$team.'" class="'.$team.'colors">'.$hometeam.'</a> <span class="'.$team.'colors" style="font-weight:normal;">('.$wins["".$team.""].'-'.$losses["".$team.""].'-'.$ties["".$team.""].')</span></th>
											<th class="border-color: transparent!important;"></th>
										</tr>
									</thead>
									<tbody>
									</tbody>
								</table>
								</div>';

				$front_summary_home_table .= '<div class="table-responsive">
				<table class="table table-bordered display2" style="margin-bottom:0px;">
									<thead>
										<tr style="display:none!important;">
											<th></th>
											<th></th>
										</tr>
									</thead>
									<tbody>
										'.$firstdowns_home_table.'
										'.$thirddowns_home_table.'
										'.$fourthdowns_home_table.'
										'.$net_yards_home_table.'
										'.$net_yards_passing_home_table.'
										'.$net_yards_rushing_home_table.'
										'.$touchdowns_home_table.'
									</tbody>
								</table>
								</div>';*/


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

		//$agg_yds = $total_away_net_yards_gained+$total_home_net_yards_gained;
		
}

arsort($leaderindex);
$barchart = '';
$barcolors = '';
$barcolorassign = '';
$labels = '';
$i = 0;
foreach ($leaderindex as $key => $val) {
	if ($i <= 5) {
		if ($view === "passing") {
			$tds = $passing_tds_away["".$key.""];
		} else if ($view === "rushing") {
			$tds = $rushing_tds_away["".$key.""];
		} else if ($view === "receiving") {
			$tds = $receiving_tds_away["".$key.""];
		}
		$bval = $val / ($wins["".$team_rec["".$key.""].""] + $losses["".$team_rec["".$key.""].""] + $ties["".$team_rec["".$key.""].""]);
		if ($bval == NULL) {
			$bval = 0;
		}
		$barchart .= '{y: "'.$name_rec["".$key.""].'", a: '.$val.', b: '.$bval.', c: '.$tds.'},';
		$barcolors .= 'var '.$team_rec["".$key.""].'color = "'.$teamcolor["".$team_rec["".$key.""].""].'";';
		$barcolorassign .= "".$team_rec["".$key.""]."color,";
		$i++;
	} else {
		break;
	}
}
$barchart = rtrim($barchart, ',');
$barcolorassign = rtrim($barcolorassign, ',');

		$score_morris_area_chart = '<div class="col-md-12" style="padding:0px;">
			        <div class="widget-chart with-sidebar bg-black" style="-webkit-border-radius: 0px;-moz-border-radius: 0px;border-radius: 0px;margin-bottom: 0px;">
			            <div class="widget-chart-content">
			                <h4 class="chart-title">
			                    '.$year.' '.$graph_stat_title.'
			                    <small>by yards</small>
			                </h4>
			                <div id="stats-bar-chart" class="morris-inverse" style="height: 260px;"></div>
			            </div>
			            <div class="widget-chart-sidebar bg-black-darker" style="background-color:#151515!important;">
			                <div class="chart-number">
			                </div>
			                <div id="score-donut-chart" style="height: 160px"></div>
			                <ul class="chart-legend">
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
    var away = '';
    var awayLight = '';
    var home = '';
    var homeLight = '';
    var blackTransparent = 'rgba(0,0,0,0.80)';
    var whiteTransparent = 'rgba(255,255,255,0.4)';
    
    Morris.Line({
        element: 'score-line-chart',
        data: [
            {x: 'P1', y: 0, z: 0},
            {x: 'P2', y: 0, z: 0},
            {x: 'P3', y: 0, z: 0},
            {x: 'P4', y: 0, z: 0},
            {x: 'P5', y: 0, z: 0},
            {x: 'P6', y: 0, z: 0}
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
    var away = '';
    var awayLight = '';
    var home = '';
    var homeLight = '';
    Morris.Donut({
        element: 'score-donut-chart',
        data: [
            {label: \"By Passing\", value: 0},
            {label: \"By Rushing\", value: 0},
            {label: \"By Penalty\", value: 0}
        ],
        colors: [green, greenLight, greenDark],
        labelFamily: 'Open Sans',
        labelColor: 'rgba(255,255,255,0.4)',
        labelTextSize: '12px',
        backgroundColor: '#242a30'
    });
};

var handleMorrisBarChart = function() {
    ".$barcolors."
    
    Morris.Bar({
        element: 'stats-bar-chart',
        data: [
            ".$barchart."
        ],
        xkey: 'y',
        ykeys: ['a', 'b', 'c'],
        parseTime: false,
        labels: ['Yards', 'Yards per Game', 'Touchdowns'],
        barRatio: 0.4,
        xLabelAngle: 35,
        hideHover: 'auto',
        resize: true,
        barColors: [green, greenLight, aquaLight]
    });
};

var handleFdownsYdsDonutChart = function() {
    var away = '';
    var awayLight = '';
    var home = '';
    var homeLight = '';
    Morris.Donut({
        element: 'fdowns-donut-chart',
        data: [
            {label: \"\", value: ".$firstdowns_away_total."0},
            {label: \"\", value: ".$firstdowns_home_total."0}
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
            handleMorrisBarChart();
        }
    };
}();
DashboardV2.init();";

	/*$tabbody = '<ul class="nav nav-tabs">
								<li class="active"><a href="#default-tab-1" data-toggle="tab">Stats</a></li>
	                    		<li><a href="#default-tab-2" data-toggle="tab">Roster</a></li>
	                    		<li class=""><a href="#default-tab-3" data-toggle="tab">Schedule</a></li>
	                    	</ul>
	                    	<div class="tab-content">
	                    		<div class="tab-pane fade active in" id="default-tab-1">
	                    			'.$front_summary_away_table.'
	                    		</div>
	                    		<div class="tab-pane fade" id="default-tab-2">
	                    			'.$roster_table.'
	                    		</div>
	                    		<div class="tab-pane fade" id="default-tab-3">
	                    			'.$schedule_table.'
	                    		</div>
	                    	</div>';*/

	$shareurl = 'http://statstrac.com/stats.php?view='.$view;
	$shareurl = urlencode($shareurl);

	//$sortid = $i + 1;

	$widget = '<div class="panel panel-inverse">
                        <div class="panel-heading">
                            <div class="panel-heading-btn pull-right">
                            	<a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand" style="color:#000;"><i class="fa fa-expand"></i></a>
                                <a href="http://www.facebook.com/share.php?u='.$shareurl.'" target="_blank" class="btn btn-primary btn-icon btn-circle btn-xs"><i class="fa fa-facebook"></i></a>
                                <a href="http://twitter.com/intent/tweet?status='.$shareurl.'" target="_blank" class="btn btn-info btn-icon btn-circle btn-xs"><i class="fa fa-twitter"></i></a>
                            </div>
                            <h4 class="panel-title">'.$year.' '.$graph_stat_title.'</h4>
                        </div>
                        <div class="panel-body">
                        	'.$score_morris_area_chart.'
                        	<ul class="nav nav-tabs">
							</ul>
							<div class="tab-content" style="margin-bottom:0px;">
								<div class="tab-pane fade active in" id="'.$hashtag.'-tab-1">
									<div class="col-md-12" style="padding:0px;">
									'.$summary_table_final.'
									</div>
                            	</div>
							</div>
						</div>
                    </div>';

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
	$lastweek = '/?type='.$season_type.'&year='.$year.'&week='.$weekfloat;
}
if ($year > 2010) {
	$yearfloat = $year - 1;
	$lyear = '/?type='.$season_type.'&year='.$yearfloat.'&week='.$week;
}

require ASSETS . 'stats-page' . EXT;