<?

class Resource extends CachedIndexedModel
{
    public function ds() { return "oim"; }
    public function sql($params)
    {
        $where = "where 1 = 1";
        if(isset($params["resource_id"])) {
            $resource_id = $params["resource_id"];
            $where .= " and r.id = $resource_id";
        }
        $sql = "SELECT r.id id, r.name name, r.fqdn fqdn, r.description description, r.url url, r.active active, ext.interop_bdii interop_bdii FROM resource r left join resource_wlcg ext on r.id = ext.resource_id $where order by name";
        return $sql;
    }
    public function key() { return "id"; }
}
