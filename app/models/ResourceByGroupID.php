<?

class ResourceByGroupID extends CachedIndexedModel
{
    public function sql($params)
    {
        $service_condition = "where 1 = 1";
        if(isset($params["servicetype"])) {
            $service_condition .= " and service_id = ".$params["servicetype"];
        }
        
        $where = "";
        if(isset($params["resource_group_id"])) {
            $where .= " and rg.resource_group_id = ".$params["resource_group_id"];
        }

        $sql = "SELECT r.resource_id resource_id, r.name name, r.fqdn fqdn, rg.resource_group_id resource_group_id
            FROM oim.resource r join oim.resource_resource_group rg on r.resource_id = rg.resource_id
            where r.active = 1 and r.disable = 0 $where and r.resource_id in (select resource_id from oim.resource_service $service_condition)";
        return $sql;
    }
    public function key() { return "resource_group_id"; }
}
