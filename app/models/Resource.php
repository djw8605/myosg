<?

class Resource extends CachedIndexedModel
{
    public function sql($params)
    {
        $where = "where disable = 0";
        if(isset($params["resource_id"])) {
            $resource_id = $params["resource_id"];
            $where .= " and r.resource_id = $resource_id";
        }
        $sql = "SELECT r.resource_id id, r.name name, r.fqdn fqdn, r.description description, r.url url, ext.interop_bdii interop_bdii FROM oim.resource r left join oim.resource_ext_attributes ext on r.resource_id = ext.resource_id $where order by name";
        return $sql;
    }
    public function key() { return "id"; }
}
