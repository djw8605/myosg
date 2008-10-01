<?

class ResourceGroup extends CachedIndexedModel
{
    public function sql($param)
    {
        $sql = "SELECT resource_group_id id, name, osg_grid_type_id, description
            FROM oim.resource_group $param
            order by name";
        return $sql;
    }
    public function key() { return "id"; }
}
