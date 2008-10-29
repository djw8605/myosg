<?

class ServiceTypes extends CachedIndexedModel
{
    public function sql($params)
    {
        dlog(get_class($this)." // sql ".print_r($this->params, true));
        $where = "";
        if(isset($params["service_group_id"])) {
            $service_group_id = $params["service_group_id"];
            $where = "WHERE S.service_id IN (SELECT service_id FROM oim.service_service_group WHERE service_group_id=$service_group_id) AND S.service_id NOT IN (SELECT DISTINCT PS.parent_service_id psid FROM oim.service PS WHERE PS.parent_service_id IS NOT NULL)";
        }
        $sql = "SELECT * FROM oim.service S $where";
        return $sql;
    }
    public function key() { return "service_id"; }
}


?>
