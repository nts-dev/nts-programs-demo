<?php
//ini_set('display_errors', '1');

header('Access-Control-Allow-Origin: *');
require_once $_SERVER["DOCUMENT_ROOT"] . '/GoogleDocsAPI/vendor/autoload.php';

session_start();

$client = new Google_Client();
$client->setAuthConfigFile('client_secrets.json');
$client->setRedirectUri('http://bo.nts.nl/Google_docs_extract/oauth2callback.php');
$client->addScope(Google_Service_Docs::DOCUMENTS);
$client->addScope(Google_Service_Drive::DRIVE);

if (! isset($_GET['code'])) {

  $auth_url = $client->createAuthUrl();
  header('Access-Control-Allow-Origin: *, Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
} else {
  $client->authenticate($_GET['code']);
  $_SESSION['access_token'] = $client->getAccessToken();
  $redirect_uri = 'http://bo.nts.nl/Google_docs_extract/index.php';
  header('Access-Control-Allow-Origin: *, Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
}
