<?

class DowntimeService extends CachedModel
{
    public function sql($params)
    {
        $where = "";
        if(isset($params["downtime_id"])) {
            $where = "where dowmtime_id = ".$params["downtime_id"];
        }

        $sql = "select * from oim.resource_downtime_service $where";
        return $sql;
    }
}

?>
