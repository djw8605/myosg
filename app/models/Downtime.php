<?

class Downtime extends CachedIndexedModel
{
    public function sql($params)
    {
        $resource_id = $params["resource_id"];
        $at = $params["at"];
        $sql = "select *, UNIX_Timestamp(start_time) as start_time, UNIX_Timestamp(end_time) as end_time ".
                " from oim.resource_downtime ".
                " where resource_id = $resource_id and UNIX_Timestamp(start_time) < $at and UNIX_Timestamp(end_time) > $at".
                " and disable = 0";
        //elog($sql);
        return $sql;
    }
    public function key() { return "downtime_severity_id"; }
}

?>
