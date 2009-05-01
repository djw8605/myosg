<?

class Resource extends CachedIndexedModel
{
    public function ds() { return "oim"; }
    public function sql($params)
    {
        $where = "";
        if(isset($params["resource_id"])) {
            $where = "where id = ".$params["resource_id"];
        }
        $sql = "SELECT * FROM resource $where order by name";
        return $sql;
    }
    public function key() { return "id"; }
}
