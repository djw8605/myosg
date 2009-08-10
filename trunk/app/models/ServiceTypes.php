<?

class ServiceTypes extends CachedIndexedModel
{
    public function ds() { return "oim"; }
    public function sql($params)
    {
        $where = "";
        if(isset($params["service_group_id"])) {
            $service_group_id = $params["service_group_id"];
            $where = "WHERE S.service_group_id=$service_group_id";
        }
        $sql = "SELECT * FROM service S $where";
        return $sql;
    }
    public function key() { return "id"; }
}


?>
