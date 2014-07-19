<?php
require_once dirname(__FILE__) . '/../../../config.php';

if (!isset($argv[1])) {
	exit($c->error("This script is not intended to be run manually, it is called from postprocess_threaded.py."));
}

$pdo = new \nzedb\db\Settings();
$c = new ColorCLI();

$tmux = new Tmux;
$torun = $tmux->get()->post;

$pieces = explode('           =+=            ', $argv[1]);
$postprocess = new PostProcess(true);
$nfopostprocess = new Nfo(true);
if (isset($pieces[2])) {
	$postprocess->processMovies($argv[1]);
	echo '.';
} else if (isset($pieces[1]) && $pieces[1] == "additional") {
	// Create the connection here and pass, this is for post processing, so check for alternate
	$nntp = new NNTP();
	if (($pdo->getSetting('alternate_nntp') == '1' ? $nntp->doConnect(true, true) : $nntp->doConnect()) !== true) {
		exit($c->error("Unable to connect to usenet."));
	}
	if ($pdo->getSetting('nntpproxy') == "1") {
		usleep(500000);
	}

	$postprocess->processAdditional($nntp, '', $pieces[0]);
	if ($pdo->getSetting('nntpproxy') != "1") {
		$nntp->doQuit();
	}
} else if (isset($pieces[1]) && $pieces[1] == "nfo") {
	// Create the connection here and pass, this is for post processing, so check for alternate
	$nntp = new NNTP();
	if (($pdo->getSetting('alternate_nntp') == '1' ? $nntp->doConnect(true, true) : $nntp->doConnect()) !== true) {
		exit($c->error("Unable to connect to usenet."));
	}
	if ($pdo->getSetting('nntpproxy') == "1") {
		usleep(500000);
	}
	$nfopostprocess->processNfoFiles('', 1, 1, $pieces[0], $nntp);
	//$postprocess->processNfos($argv[1], $nntp);
	if ($pdo->getSetting('nntpproxy') != "1") {
		$nntp->doQuit();
	}
} else if (isset($pieces[1])) {
	$postprocess->processTv($argv[1]);
}
