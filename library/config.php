<?php

defined("LIBRARY_PATH")
    or define("LIBRARY_PATH", dirname(__FILE__) . '/');
     
defined("TEMPLATES_PATH")
    or define("TEMPLATES_PATH", dirname(__FILE__) . '/../templates/');

defined("TRANSLATIONS_PATH")
    or define("TRANSLATIONS_PATH", dirname(__FILE__) . '/../translations/');

defined("LOGS_PATH")
    or define("LOGS_PATH", dirname(__FILE__) . '/../logs/');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
