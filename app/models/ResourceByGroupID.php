<?

class ResourceByGroupID extends CachedIndexedModel
{
    public function ds() { return "oim"; }
    public function sql($params)
    {
        $service_condition = "where 1 = 1";
        if(isset($params["servicetype"])) {
            $service_condition .= " and service_id = ".$params["servicetype"];
        }
        
        $where = "";
        if(isset($params["resource_group_id"])) {
            $where .= " and r.resource_group_id = ".$params["resource_group_id"];
        }

        //return resource that has at least one resource_service
        $sql = "SELECT * FROM resource r where 1 = 1 $where and r.id in (select resource_id from resource_service $service_condition)";
        return $sql;
    }
    public function key() { return "resource_group_id"; }
}
