<?php
global $CFG;
//$xml = simplexml_load_file(dirname(dirname(dirname(__FILE__))) ."/blocks/alarm/settings.xml");
$xml = simplexml_load_file($CFG->dirroot ."/blocks/alarm/settings.xml");
if($xml !== FALSE){
    $cronjobDays = (int)$xml->cronjob;
    $version = (int)$xml->version;
}
else{
    exit("settings.xml nicht gefunden!");
}
$plugin->version = $version; // YYYYMMDDHH (year, month, day, 24-hr time)
$plugin->requires = 2010112400; // YYYYMMDDHH (This is the release version for Moodle 2.0)
$plugin->cron = $cronjobDays * 24 * 60 * 60; // Seconds.
