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

function computePeriodStartEnd($dirty_period)
{
    switch($dirty_period) {
    case "week":
        $end_time = (int)(time() / 86400) * 86400 - 86400;
        $start_time = $end_time - 86400*6;
        break;
    case "30days":
        $end_time = (int)(time() / 86400) * 86400 - 86400;
        $start_time = $end_time - 86400*29;
        break;
    default:
        throw new exception("bad period: $dirty_period");
    }

    return array($start_time, $end_time);
}

