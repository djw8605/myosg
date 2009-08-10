<?

class VOVO extends CachedIndexedModel
{
    public function ds() { return "oim"; }
    public function sql($params)
    {
        $sql = "SELECT * FROM vo_vo";
        return $sql;
    }
    public function key() { return "child_vo_id"; }
}
