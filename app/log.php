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

//standard log
function slog($str, $type = Zend_Log::INFO)
{
    global $g_starttime;

    if($str === null) $str = "[null]";
    $time = microtime(true) - $g_starttime;
    $str = round($time, 3)." ".$str;
    Zend_Registry::get("logger")->log($str, $type);
}
