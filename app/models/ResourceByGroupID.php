<?

class ResourceByGroupID extends CachedIndexedModel
{
    public function sql($params)
    {
        $service_condition = "where active = 1 and disable = 0";
        if(isset($params["servicetype"])) {
            $service_condition .= " and service_id = ".$params["servicetype"];
        }

/*
        $sql = "SELECT resource_id id, resource_name name, resource_fqdn fqdn, resource_group_id group_id
            FROM `View_resourceSiteScPub`
            where resource_id in (select resource_id from oim.resource_service $service_condition)";
*/
        $sql = "SELECT r.resource_id id, r.name name, r.fqdn fqdn, rg.resource_group_id group_id
            FROM oim.resource r join oim.resource_resource_group rg on r.resource_id = rg.resource_id
            where r.resource_id in (select resource_id from oim.resource_service $service_condition)";
        elog($sql);
        return $sql;
    }
    public function key() { return "group_id"; }
}
