<?php
//session_cache_limiter('private_no_expire');
//phpinfo();exit;

//init zend framework
set_include_path('lib/zf-1.7.3/library' . PATH_SEPARATOR . get_include_path());  
set_include_path('app/models' . PATH_SEPARATOR . get_include_path());  
set_include_path('app/controls' . PATH_SEPARATOR . get_include_path());  
require_once "Zend/Loader.php"; 
Zend_Loader::registerAutoload(); 
Zend_Session::start();

//load our stuff
require_once("config.php");
require_once("app/views/helper.php");
require_once("app/base.php");

remove_quotes();
setup_logs();
greet();
cert_authenticate();

ini_set('error_log', config()->error_logfile);
ini_set('display_errors', 0); 
ini_set('log_errors', 1); 
ini_set('display_startup_errors', 1);  
error_reporting(E_ALL | E_STRICT);  
date_default_timezone_set("UTC");

//dispatch
$frontController = Zend_Controller_Front::getInstance(); 
$frontController->setControllerDirectory('app/controls'); 
$frontController->dispatch(); 
