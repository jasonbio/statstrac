<?php
/**
 * Configuration variables
 */
date_default_timezone_set('America/New_York');

// Path to your working postgrest server
$postgrest = 'http://localhost:2096/';

// Google Analytics UA ID
$uaid = 'UA-21656782-24';

// With a normal nfldb installation the start year will be 2009, data for earlier years can be applied however
$startyear = 2009;
$currentyear = date('Y');
$totalweeks = 0;
$i = 0;

$totalseasontypes = array();
$totalgames = array();

$weeksdropdown = '';
$yearsdropdown = '';
$seasondropdown = '';
$seasontypedropdown = '';
$sortdropdown = '';

// NAV FUNCTIONS - TEAMS
$conference = array();
$teamname = array();
$teamcity = array();
$teamcolor = array();
$AFCE = array();
$AFCN = array();
$AFCS = array();
$AFCW = array();
$NFCE = array();
$NFCN = array();
$NFCS = array();
$NFCW = array();
$AFCEs = array();
$AFCNs = array();
$AFCSs = array();
$AFCWs = array();
$NFCEs = array();
$NFCNs = array();
$NFCSs = array();
$NFCWs = array();
$total_teamname = array();
$total_teamcity = array();
$total_teamid = array();
$AFCEw = '<h1 class="page-header">AFC East</h1>';
$AFCNw = '<h1 class="page-header">AFC North</h1>';
$AFCSw = '<h1 class="page-header">AFC South</h1>';
$AFCWw = '<h1 class="page-header">AFC West</h1>';
$NFCEw = '<h1 class="page-header">NFC EAST</h1>';
$NFCNw = '<h1 class="page-header">NFC North</h1>';
$NFCSw = '<h1 class="page-header">NFC South</h1>';
$NFCWw = '<h1 class="page-header">NFC West</h1>';
$teamnav = '';
$t = 0;

$wins = array();
$losses = array();
$ties = array();
$finishedgames = array();
$tempuid = '';
$g = 0;

$confleaders = array();
$w = 0;
$AFCEflag = false;
$AFCNflag = false;
$AFCSflag = false;
$AFCWflag = false;
$NFCEflag = false;
$NFCNflag = false;
$NFCSflag = false;
$NFCWflag = false;
$AFCEa = '';
$AFCNa = '';
$AFCSa = '';
$AFCWa = '';
$NFCEa = '';
$NFCNa = '';
$NFCSa = '';
$NFCWa = '';

$globalleader_passing_yds = array();
$globalleader_rushing_yds = array();
$globalleader_receiving_yds = array();
$globalgameref = array();
$widget = array();

$weeklyleaders = array();
$pflag = false;
$ruflag = false;
$reflag = false;