<?

$g_starttime = microtime(true);

function setup_logs()
{
    //setup debug logs
    $logger = new Zend_Log();
    $writer = new Zend_Log_Writer_Stream(config()->debug_logfile);
    $logger->addWriter($writer);
    Zend_Registry::set("debug_logger", $logger);

    dlog('----------------------------------------------------------------------');
    dlog('RSV Viewer session starting.. '.$_SERVER["REQUEST_URI"]);
    dlog(print_r($_REQUEST, true));
}

//debug log
function dlog($str, $type = Zend_Log::INFO)
{
    global $g_starttime;

    if(config()->debug) {
        if($str === null) $str = "[null]";
        $time = microtime(true) - $g_starttime;
        $str = round($time, 3)." ".$str;
        Zend_Registry::get("debug_logger")->log($str, $type);
    }
}
