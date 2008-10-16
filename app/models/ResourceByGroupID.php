<?

class ResourceByGroupID extends CachedIndexedModel
{
    public function sql($params)
    {
        $service_condition = "where 1 = 1";
        if(isset($params["servicetype"])) {
            $service_condition .= " and service_id = ".$params["servicetype"];
        }

        $sql = "SELECT r.resource_id id, r.name name, r.fqdn fqdn, rg.resource_group_id group_id
            FROM oim.resource r join oim.resource_resource_group rg on r.resource_id = rg.resource_id
            where r.resource_id in (select resource_id from oim.resource_service $service_condition)";
        return $sql;
    }
    public function key() { return "group_id"; }
}
