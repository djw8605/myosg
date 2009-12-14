<?

class VirtualOrganization extends CachedIndexedModel
{
    public function ds() { return "oim"; }
    public function sql($params)
    {
        $where = "where 1=1";
        if(isset($params["sc_id"])) {
            $where .= " and sc_id = ".$params["sc_id"];
        }
        $sql = "SELECT * FROM vo $where order by name";
        return $sql;
    }
    public function key() { return "id"; }
}
