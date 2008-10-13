<?

class MetricData extends Model
{
    public function sql($params)
    {
        $where_timestamp = "";
        if(isset($params["time"])) {
            $time = $params["time"];
            $where_timestamp = " and timestamp <= " . $time . " ";
        }
        $resource_id = $params["resource_id"];
        $sql = "select * from metricdata m, ".
            "(select max(timestamp) last_timestamp ,metric_id from metricdata ".
            "where resource_id = ". $resource_id . " " . $where_timestamp .
            "group by metric_id) last ".
            "where m.timestamp = last.last_timestamp and m.metric_id = last.metric_id and m.resource_id = $resource_id ".
            "order by timestamp";
        //elog($sql);
        return $sql;
    }
    static public function isFresh($metric_timestamp, $metric_freshfor, $at) {
        if($at < $metric_timestamp + $metric_freshfor) {
            return true;
        }
        return false;
    }
}


?>
