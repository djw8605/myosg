<?

class ResourceByGroupID extends CachedIndexedModel
{
    public function sql($param)
    {
        return "SELECT resource_id id, resource_name name, resource_fqdn fqdn, resource_group_id group_id
            FROM `View_resourceSiteScPub`
            $param";
    }
    public function key() { return "group_id"; }
}
