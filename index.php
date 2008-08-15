<?php

session_start();

//init zend framework
set_include_path('lib/zf/library' . PATH_SEPARATOR . get_include_path());  
set_include_path('app/models' . PATH_SEPARATOR . get_include_path());  
require_once "Zend/Loader.php"; 
Zend_Loader::registerAutoload(); 

//load our stuff
require_once("config.php");
require_once("app/views/helper.php");
require_once("app/models/db.php");
require_once("app/log.php");
require_once("app/authentication.php");
setup_logs();
cert_authenticate();

//redirect error to error log
ini_set('error_log', config()->error_logfile);
ini_set('display_errors', 0); 
ini_set('log_errors', 1); 
ini_set('display_startup_errors', 1);  
//init php
error_reporting(E_ALL | E_STRICT);  
date_default_timezone_set("UTC");

//dispatch
$frontController = Zend_Controller_Front::getInstance(); 
$frontController->setControllerDirectory('app/controls'); 
$frontController->dispatch(); 

//at the end..
if(config()->profile_db) {
    log_db_profile();
}
