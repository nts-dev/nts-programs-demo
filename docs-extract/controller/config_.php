<?php

ini_set('display_errors', '1');

/* Default time zone ,to be able to send mail */
date_default_timezone_set('Africa/Nairobi');


$dbc = mysqli_connect('db', 'projectuser', 'wgnd8b', 'nts_site');

if (!$dbc) {
    trigger_error('Could not connect to MySQL: ' . mysqli_connect_error());
}

// session_start();