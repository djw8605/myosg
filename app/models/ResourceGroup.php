<?

class ResourceGroup extends CachedIndexedModel
{
    public function ds() { return "oim"; }
    public function sql($params)
    {
        //select resource group that is used by at least one resource (TODO.. really?? why?)
        $where = "where rg.id IN (select resource_group_id from resource group by resource_group_id)"; 

        if(isset($params["osg_grid_type_id"])) {
            $where .= " and osg_grid_type_id = ".$params["osg_grid_type_id"];
        }
        if(isset($params["resourcegroup"])) {
            $where .= " and id = ".$params["resourcegroup"];
        }
        if(isset($params["site_id"])) {
            $where .= " and site_id = ".$params["site_id"];
        }
        $sql = "SELECT rg.*, t.name as grid_type, active FROM resource_group rg JOIN osg_grid_type t ON rg.osg_grid_type_id = t.id $where order by name";
        return $sql;
    }
    public function key() { return "id"; }
}
