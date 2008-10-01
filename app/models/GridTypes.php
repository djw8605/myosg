<?

class GridTypes extends CachedIndexedModel
{
    public function sql($param)
    {
        return "select * from oim.osg_grid_type";
    }
    public function key() { return "grid_type_id"; }
}


?>
