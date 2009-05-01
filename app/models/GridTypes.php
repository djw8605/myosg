<?

class GridTypes extends CachedIndexedModel
{
    public function ds() { return "oim"; }
    public function sql($param)
    {
        return "select * from osg_grid_type";
    }
    public function key() { return "id"; }
}


?>
