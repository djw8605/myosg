<?

class ResourceAlias extends CachedIndexedModel
{
    public function ds() { return "oim"; }
    public function sql($params)
    {
        $sql = "SELECT * FROM resource_alias";
        return $sql;
    }
    public function key() { return "resource_id"; }
}
