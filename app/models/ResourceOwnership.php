<?

class ResourceOwnership extends CachedIndexedModel
{
    public function ds() { return "oim"; }
    public function sql($params)
    {
        $where = "";
        if(isset($params["resource_id"])) {
            $id = $params["resource_id"];
            $where = " where resource_id = $id";
        }
        $sql = "SELECT *, v.name FROM oim.vo_resource_ownership o $where join vo v on o.vo_id = v.id";
        return $sql;
    }
    public function key() { return "resource_id"; }
}
