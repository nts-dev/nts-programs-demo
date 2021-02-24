<?php

ini_set('display_errors', '1');

/* Default time zone ,to be able to send mail */
date_default_timezone_set('UTC');

require_once '../../config.php';

$dbc = mysqli_connect($NTS_CFG->dbhost, $NTS_CFG->dbuser, $NTS_CFG->dbpass, $NTS_CFG->dbname);

if (mysqli_connect_errno()) {
    echo 'Could not connect to MySQL: ' . mysqli_connect_error();
	exit();
}

// session_start();