<?

class ResourceServices extends CachedModel
{
    public function ds() { return "oim"; }
    public function sql($params)
    {
        $where = "1 = 1";
        if(isset($params["resource_id"])) {
            $where .= " and rs.resource_id = ".$params["resource_id"];
        }
        if(isset($params["service_id"])) {
            $where .= " and rs.service_id = ".$params["service_id"];
        }
        $sql = "SELECT rs.*, s.* FROM resource_service rs join service s on rs.service_id = s.id $where";
        return $sql;
    }
}
