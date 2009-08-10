<?

class VOOwnedResources extends CachedIndexedModel
{
    public function ds() { return "oim"; }
    public function sql($params)
    {
        $where = "";
        if(isset($params["vo_id"])) {
            $vo_id = $params["vo_id"];
            $where = " where vo_id = $vo_id";
        }
        $sql = "SELECT * FROM vo_resource_ownership $where";
        return $sql;
    }
    public function key() { return "vo_id"; }
}
