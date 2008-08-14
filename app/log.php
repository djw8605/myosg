<?

$g_starttime = microtime(true);

function setup_logs()
{
    //setup logs
    $logger = new Zend_Log();
    $writer = new Zend_Log_Writer_Stream(config()->logfile);
    $logger->addWriter($writer);
    Zend_Registry::set("logger", $logger);

    slog('----------------------------------------------------------------------');
    slog('RSV Viewer session starting.. '.$_SERVER["REQUEST_URI"]);
    slog('Process ID:'.getmypid());

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
        Zend_Registry::get("logger")->log($str, $type);
    }
}

//error log
function elog($str, $type = Zend_Log::ERR)
{
    global $g_starttime;

    //log to the logger
    if($str === null) $str = "[null]";
    $time = microtime(true) - $g_starttime;
    $str = round($time, 3)." ".$str;
    Zend_Registry::get("logger")->log($str, $type);

    //send to error_log as well
    // 0) message is sent to PHP's system logger, using the Operating System's 
    // system logging mechanism or a file, depending on what the error_log  
    // configuration directive is set to. This is the default option. 
    error_log($str, 0); 
}

//standard log
function slog($str, $type = Zend_Log::INFO)
{
    global $g_starttime;

    if($str === null) $str = "[null]";
    $time = microtime(true) - $g_starttime;
    $str = round($time, 3)." ".$str;
    Zend_Registry::get("logger")->log($str, $type);
}
