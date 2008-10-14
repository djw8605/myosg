<?

class Downtime extends CachedIndexedModel
{
    public function sql($params)
    {
        $resource_id = $params["resource_id"];
        $start_time = $params["start_time"];
        $end_time = $params["end_time"];
        $sql = "select *, UNIX_Timestamp(start_time) as unix_start_time, UNIX_Timestamp(end_time) as unix_end_time ".
                " from oim.resource_downtime ".
                " where resource_id = $resource_id and ".
                " ( (UNIX_TIMESTAMP(end_time) > $start_time and UNIX_TIMESTAMP(end_time) < $end_time) or". //end overwrap
                "   (UNIX_TIMESTAMP(start_time) > $start_time and UNIX_TIMESTAMP(start_time) < $end_time) or". //start overwarp
                "   (UNIX_TIMESTAMP(start_time) > $start_time and UNIX_TIMESTAMP(end_time) < $end_time) or". //contained
                "   (UNIX_TIMESTAMP(start_time) < $start_time and UNIX_TIMESTAMP(end_time) > $end_time) )". //outside
                " and disable = 0";
        //elog($sql);
        return $sql;
    }
    public function key() { return "downtime_severity_id"; }
}

?>
