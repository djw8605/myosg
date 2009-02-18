<?

class Resource extends CachedIndexedModel
{
    public function sql($params)
    {
        //$where = "where active = 1 and disable = 0";
        $where = "where 1 = 1";
        if(isset($params["resource_id"])) {
            $resource_id = $params["resource_id"];
            $where .= " and r.resource_id = $resource_id";
        }
        $sql = "SELECT r.resource_id id, r.name name , r.fqdn fqdn ,r.description description, ext.interop_bdii interop_bdii FROM oim.resource r join oim.resource_ext_attributes ext on r.resource_id = ext.resource_id $where order by name";
        return $sql;
    }
    public function key() { return "id"; }
}
