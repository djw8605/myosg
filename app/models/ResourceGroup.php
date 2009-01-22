<?

class ResourceGroup extends CachedIndexedModel
{
    public function sql($params)
    {
        //$where = "where active = 1 and disable = 0 and resource_group_id in (select resource_group_id from oim.resource_resource_group group by resource_group_id)";
        $where = "where resource_group_id in (select resource_group_id from oim.resource_resource_group group by resource_group_id)";
        if(isset($params["osg_grid_type_id"])) {
            $where .= " and osg_grid_type_id = ".$params["osg_grid_type_id"];
        }
        if(isset($params["resourcegroup"])) {
            $where .= " and resource_group_id = ".$params["resourcegroup"];
        }
        if(isset($params["site_id"])) {
            $where .= " and site_id = ".$params["site_id"];
        }
        $sql = "SELECT rg.*, t.short_name as grid_type, active, disable FROM oim.resource_group rg join oim.osg_grid_type t on rg.osg_grid_type_id = t.grid_type_id $where order by name";
        dlog($sql);
        return $sql;
    }
    public function key() { return "id"; }
}
