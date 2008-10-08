<?

class MetricData extends Model
{
    public function sql($params)
    {
        $where_timestamp = "";
        if(isset($params["time"])) {
            $time = $params["time"];
            $where_timestamp = " and timestamp < " . $time . " ";
        }
        $resource_id = $params["resource_id"];
        return "select * from metricdata m, ".
            "(select max(timestamp) last_timestamp ,metric_id from metricdata ".
            "where resource_id = ". $resource_id . " " . $where_timestamp .
            "group by metric_id) last ".
            "where m.timestamp = last.last_timestamp and m.metric_id = last.metric_id ".
            "order by timestamp";
    }
}


?>
