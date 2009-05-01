<?

class ResourceWLCG extends CachedIndexedModel
{
    public function ds() { return "oim"; }
    public function sql($params)
    {
        $where = "";
        if(isset($params["resource_id"])) {
            $where = "where resource_id = ".$params["resource_id"];
        }
        $sql = "SELECT * FROM resource_wlcg $where";
        return $sql;
    }
    public function key() { return "resource_id"; }
}
