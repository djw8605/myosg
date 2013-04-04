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


$g_starttime = microtime(true);

function clearlog()
{
    unlink(config()->logfile);
    unlink(config()->error_logfile);
    unlink(config()->audit_logfile);
}

function setup_logs()
{
    //setup standard logs
    $writer = new Zend_Log_Writer_Stream(config()->logfile);
    $logger = new Zend_Log();
    $logger->addWriter($writer);
    Zend_Registry::set("logger", $logger);

    //setup firebug log
    $writer = new Zend_Log_Writer_Firebug();
    $logger = new Zend_Log();
    $logger->addWriter($writer);
    Zend_Registry::set("fb_logger", $logger);
}


function log_format($str)
{
    global $g_starttime;
    if($str === null) $str = "[null]";
    $time = microtime(true) - $g_starttime;
    $str = getmypid()."@".round($time, 3)." ".$str;

    return $str;
}

//debug log
function dlog($obj)
{
    if(config()->debug) {
        if(is_string($obj)) {
            $obj = log_format($obj);
        } 
        Zend_Registry::get("fb_logger")->log($obj, Zend_Log::DEBUG);
    }
}

function remoteinfo() {
    if(isset($_SERVER["REMOTE_ADDR"])) {
        $ip = $_SERVER["REMOTE_ADDR"];
        $name = gethostbyaddr($ip);
        return $ip."($name) ";
    } else {
        return "(unknown client) ";
    }
}

//error log
function elog($obj)
{
    if(is_string($obj)) {
        $obj = log_format($obj);
    } 
    Zend_Registry::get("logger")->log($obj, Zend_Log::ERR);

    $obj = remoteinfo().$obj;

    //send to error_log as well
    // 0) message is sent to PHP's system logger, using the Operating System's 
    // system logging mechanism or a file, depending on what the error_log  
    // configuration directive is set to. This is the default option. 
    error_log("[ERR]".$obj, 0); 
}

//warning log
function wlog($obj)
{
    if(is_string($obj)) {
        $obj = log_format($obj);
    }-
    Zend_Registry::get("logger")->log($obj, Zend_Log::WARN);

    $obj = remoteinfo().$obj;

    //send to error_log as well
    // 0) message is sent to PHP's system logger, using the Operating System's
    // system logging mechanism or a file, depending on what the error_log
    // configuration directive is set to. This is the default option.
    error_log("[WARN] ".$obj, 0);
}

//standard log
function slog($obj)
{
    if(is_string($obj)) {
        $obj = log_format($obj);
    } 
    Zend_Registry::get("logger")->log($obj, Zend_Log::INFO);
}

/*
//session message (in HTML format)
function message($type, $content)
{
    $messages = new Zend_Session_Namespace("messages");
    if(!isset($messages->msgs)) {
        $messages->msgs = array();
    }
    $messages->msgs[] = array($type, $content);
}
function flushMessages() {
    $messages = new Zend_Session_Namespace("messages");
    if(isset($messages->msgs)) {
        $msgs = $messages->msgs;
        unset($messages->msgs);
        return $msgs;
    }
    return array();
}
*/

//set $html to true if you want to pass html message
//type could be one of : error, success, info, block(use html), or warning
function message($type, $content, $html=false) {
    $message = new Zend_Session_Namespace('message');
    if(!$html) {
        $content = htmlentities($content);
    }
    //index by content to prevent dup
    $message->alerts[$content] = array("type"=>$type, "html"=>$content);
}

