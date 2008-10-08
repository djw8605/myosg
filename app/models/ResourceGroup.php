<?

class ResourceGroup extends CachedIndexedModel
{
    public function sql($params)
    {
        $where = "where active = 1 and disable = 0";
        if(isset($params["osg_grid_type_id"])) {
            $where .= " and osg_grid_type_id = ".$params["osg_grid_type_id"];
        }
        if(isset($params["resourcegroup"])) {
            $where .= " and resource_group_id = ".$params["resourcegroup"];
        }
        $sql = "SELECT resource_group_id id, name, osg_grid_type_id, description, site_id
            FROM oim.resource_group $where
            order by name";
        return $sql;
    }
    public function key() { return "id"; }
}
