#!/usr/bin/env php
<?php
/**
 * @file
 * Script of the cron action for execution in out of the web apps
 * often use in system daemon behind the scene
 * 
 * Checks that the arguments are supplied, if not display help information.
 */

//echo $argc; exit;
if (in_array($argv, array('--help')) || $argc < 5) {
  ?>
  This is a script to run cron from the command line.
 
  It has only been tested with Drupal 7.2, and not
  widely tested either.
 
  It takes 2 arguments, which must both be specified:
  --key is the cron key for your website, found on your
  status report page, the part after ?cron_key=?
  --root is the path to your drupal root directory for
  your website.
 
  Usage:
  php localcron.php --key YOUR_CRON_KEY --root '/path/to/drupal/root'
 
<?php
} else {
  // Loop through arguments and extract the cron key and drupal root path.
  $starttime = time();
  for ($i = 1; $i < $argc; $i++) {
    switch ($argv[$i]) {
      case '--key':
        $i++;
        $ckey = $argv[$i];
        break;
      case '--root':
        $i++;
        $dpath = $argv[$i];
        break;
      case '--host':
        $i++;
        $rhost = $argv[$i];
        break;
    }
  }
 
  chdir($dpath);
  // Set cron key get variable to use below code with as
  // little modification as possible.
  $_GET['cron_key'] = $ckey;
  define('DRUPAL_ROOT', $dpath);
  include_once DRUPAL_ROOT . '/includes/bootstrap.inc';
 
  // Sets script name
//  $_SERVER['SCRIPT_NAME'] = $argv[0];
  $_SERVER['SCRIPT_NAME'] = '/scripts/' . basename(__FILE__);
  $rhost = empty($rhost) ? 'default' : $rhost;
 
  // Values as copied from drupal.sh
  $_SERVER['HTTP_HOST'] = $rhost;
  $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
  $_SERVER['SERVER_SOFTWARE'] = NULL;
  $_SERVER['REQUEST_METHOD'] = 'GET';
  $_SERVER['QUERY_STRING'] = '';
  $_SERVER['HTTP_USER_AGENT'] = 'console';
 
  // Code below is almost verbatim from cron.php, just the messages for
  // watchdog have been changed to indicate that the problem originated from
  // this script.
  drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);
 
  if (!isset($_GET['cron_key']) || variable_get('cron_key', 'drupal') != $_GET['cron_key']) {
    watchdog('cron', "Cron could not run via $argv[0] because an invalid key was used.", array(), WATCHDOG_NOTICE);
    drupal_access_denied();
  } elseif (variable_get('maintenance_mode', 0)) {
    watchdog('cron', "Cron could not run via $argv[0] because the site is in maintenance mode.", array(), WATCHDOG_NOTICE);
    drupal_access_denied();
  } else {
    drupal_cron_run();
  }
  $endtime = time();
  $duration = $endtime - $starttime;
  echo t('Start at') . ' ' . date('H:i:s', $starttime) . ' ' . t('to') . ' ' . date('H:i:s', $endtime) . "\n";
  echo t('Duration') . ': ' . $duration . ' ' . t('seconds') . ' ' . t('or') . ' ' . (ceil($duration / 60)) . ' ' . t('minutes') . "\n";
  unset ($starttime, $endtime, $duration);
}