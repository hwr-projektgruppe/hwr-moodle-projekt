<?php
$cronjobHours = 12;
$plugin->version = 2015070121; // YYYYMMDDHH (year, month, day, 24-hr time)
$plugin->requires = 2010112400; // YYYYMMDDHH (This is the release version for Moodle 2.0)
$plugin->cron = $cronjobHours * 60 * 60; // Seconds.
