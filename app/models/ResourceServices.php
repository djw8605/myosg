<?

class ResourceServices extends CachedModel
{
    public function sql($params)
    {
        $where = "where rs.active = 1 and rs.disable = 0";
        if(isset($params["resource_id"])) {
            $resource_id = $params["resource_id"];
            $where .= " and rs.resource_id = $resource_id"; 
        }
        if(isset($params["service_id"])) {
            $service_id = $params["service_id"];
            $where .= " and rs.service_id = $service_id"; 
        }
        $sql = "SELECT rs.resource_id, rs.service_id, s.description FROM oim.resource_service rs join oim.service s on rs.service_id = s.service_id $where";
        //elog($sql);
        return $sql;
    }
}
