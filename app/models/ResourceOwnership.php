<?

class ResourceOwnership extends CachedIndexedModel
{
    public function sql($params)
    {
        $where = "";
        if(isset($params["resource_id"])) {
            $id = $params["resource_id"];
            $where = " where resource_id = $id";
        }
        $sql = "SELECT *, v.short_name FROM oim.vo_resource_ownership o $where join oim.virtualorganization v on o.vo_id = v.vo_id";
        return $sql;
    }
    public function key() { return "resource_id"; }
}
