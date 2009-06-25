<?

class Resource extends CachedIndexedModel
{
    public function ds() { return "oim"; }
    public function sql($params)
    {
        $where = "where 1=1 ";
        if(isset($params["resource_id"])) {
            $where .= " and id = ".$params["resource_id"];
        }
        if(isset($params["active"])) {
            $where .= " and active = ".$params["active"];
        }
        if(isset($params["disable"])) {
            $where .= " and disable = ".$params["disable"];
        }
        
        $sql = "SELECT * FROM resource $where order by name";
        return $sql;
    }
    public function key() { return "id"; }
}
