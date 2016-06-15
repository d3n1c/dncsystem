#!/usr/bin/php
<?php

/**
 * @file
 * Script to execute some function out of the box
 */

for ($i = 1; $i < $argc; $i++) {
  switch ($argv[$i]) {
    case '--function':
      $i++;
      $function_name = $argv[$i];
      break;
    case '--host':
      $i++;
      $rhost = $argv[$i];
      break;
    case '--root':
      $i++;
      $dpath = $argv[$i];
      break;
  }
}

if (in_array($argv, array('--help')) || empty($rhost) || empty($dpath) || empty($function_name)) {
  unset ($rhost, $dpath, $function_name);
?>

  This is a script to run execution of function from the command line.
 
  It has only been tested with Drupal 7.2, and not
  widely tested either.
 
  It takes 3 arguments, which must both be specified:
  --host is the host name for your website, found on your
  url domain
  --root is the path to your drupal root directory for
  your website.
  --function is the name of function that will execute
  by this action
 
  Usage:
  php localcron.php --host 'SERVER_HOST_NAME' --root '/path/to/drupal/root' --function FUNCTION_NAME

<?php
  exit;
}

$path = dirname(__FILE__);

$drupal_path = $dpath;

//----------------------------------------------------

chdir($drupal_path);

define('DRUPAL_ROOT', $drupal_path);
include_once DRUPAL_ROOT . '/includes/bootstrap.inc';
$_SERVER['SCRIPT_NAME'] = '/' . basename(__FILE__);
$_SERVER['HTTP_HOST'] = $rhost;
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['SERVER_SOFTWARE'] = NULL;
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['QUERY_STRING'] = '';
$_SERVER['HTTP_USER_AGENT'] = 'console';
drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);

if (function_exists($function_name)) {
  $result = $function_name();
  print_r($result);
  unset ($result);
}

echo 'Done' . "\n";