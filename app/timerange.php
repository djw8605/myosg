<?

function getLastNDayRange($nday = 1)
{
    return array(time()-3600*24*$nday, time());
}

function getLastNDayRangeProper($nday = 1)
{
    $today = (int)(time() / 86400) * 86400;
    $before = $today - 3600*24*$nday;
    return array($before, $today);
}
