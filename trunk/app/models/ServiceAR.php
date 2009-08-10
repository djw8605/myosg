<?

class ServiceAR extends CachedModel
{
    public function ds() { return "rsv"; }
    public function sql($params)
    {
        $where = "";
        if(isset($params["resource_id"])) {
            $where .=  " and resource_id = ".$params["resource_id"];
        }
        if(isset($params["service_id"])) {
            $where .=  " and service_id = ".$params["service_id"];
        }
        if(isset($params["start_time"])) {
            $where .=  " and timestamp >= ".$params["start_time"];
        }
        if(isset($params["end_time"])) {
            $where .=  " and timestamp <= ".$params["end_time"];
        }
        $sql = "select * from service_ar where 1 = 1 $where order by timestamp";
        return $sql;
    }
}

?>
