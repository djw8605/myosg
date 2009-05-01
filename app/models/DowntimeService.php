<?

class DowntimeService extends CachedModel
{
    public function ds() { return "oim"; }
    public function sql($params)
    {
        $where = "";
        if(isset($params["downtime_id"])) {
            $where = "where resource_dowmtime_id = ".$params["downtime_id"];
        }

        $sql = "select * from resource_downtime_service $where";
        return $sql;
    }
}

?>
