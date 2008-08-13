<?

function getLast24HourRange()
{
    return array(time()-3600*24, time());
}

function getLast24HourRangeProper()
{
    $d_day = date('j', time());
    $d_month = date('n', time());
    $d_year = date('Y', time());
    $today = mktime(0,0,0,$d_month, $d_day, $d_year);
    $yesterday = $today - 3600*24;

    return array($yesterday, $today);
}
