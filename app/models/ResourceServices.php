<?

class ResourceServices extends CachedModel
{
    public function sql($params)
    {
        $where = "where 1 = 1";
        if(isset($params["resource_id"])) {
            $where .= " and rs.resource_id = ".$params["resource_id"];
        }
        if(isset($params["service_id"])) {
            $where .= " and rs.service_id = ".$params["service_id"];
        }
        $sql = "SELECT rs.resource_id, rs.service_id, s.description FROM oim.resource_service rs join oim.service s on rs.service_id = s.service_id $where";
        return $sql;
    }
}
