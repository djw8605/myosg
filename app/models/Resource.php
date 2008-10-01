<?

class Resource extends CachedModel
{
    public function sql($param)
    {
        return "SELECT resource_id id, resource_name name, resource_fqdn fqdn FROM `View_resourceSiteScPub` $param";
    }
}
