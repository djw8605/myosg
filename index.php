<?php
/*#################################################################################################

Copyright 2009 The Trustees of Indiana University

Licensed under the Apache License, Version 2.0 (the "License"); you may not use this file except in
compliance with the License. You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software distributed under the License
is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or
implied. See the License for the specific language governing permissions and limitations under the
License.

#################################################################################################*/

//init zend framework
//set_include_path('lib/zf-1.9.2/library' . PATH_SEPARATOR . get_include_path());  
set_include_path('lib/ZendFramework-1.10.3/library' . PATH_SEPARATOR . get_include_path());  
set_include_path('app/models' . PATH_SEPARATOR . get_include_path());  
set_include_path('app/controls' . PATH_SEPARATOR . get_include_path());  
require_once "Zend/Loader/Autoloader.php";
$autoloader = Zend_Loader_Autoloader::getInstance();
$autoloader->setFallbackAutoloader(true);
Zend_Session::start();


//check to make sure our site installation is done
if(!file_exists("config.php")) {
    echo "please create site specific config.php";
    exit;
}
if(!file_exists(".htaccess")) {
    echo ".htaccess file doesn't exist. please create";
    exit;
}
//load our stuff
require_once("config.php");
require_once("app/views/helper.php");
require_once("app/base.php");

try {
    //bootstrap
    remove_quotes();
    setup_logs();
    greet();
    cert_authenticate();

    ini_set('error_log', config()->error_logfile);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL | E_STRICT);

    date_default_timezone_set(user()->getTimeZone());

} catch(exception $e) {
    //when a catastrohpic failure occure (like disk goes read-only..) emailing is the only way we got..
    if(!config()->debug) {
        mail(config()->elog_email_address, "[gocticket] Caught exception during bootstrap", $e, "From: ".config()->email_from);
    }
    header("HTTP/1.0 500 Internal Server Error");
    echo "Boot Error";
    echo "<pre>".$e->getMessage()."</pre>";
    exit;
}

//dispatch
$frontController = Zend_Controller_Front::getInstance(); 
$frontController->setControllerDirectory('app/controls'); 
$frontController->dispatch(); 

slog("-- end  --------------------------------------------------------------");
