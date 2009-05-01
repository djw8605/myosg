<?

class VirtualOrganization extends CachedModel
{
    public function ds() { return "oim"; }
    public function sql($params)
    {
        $where = "";
        if(isset($params["sc_id"])) {
            $where = " and sc_id = ".$params["sc_id"];
        }
        $sql = "SELECT * FROM vo where active = 1 and disable = 0 $where order by name";
        return $sql;
    }
}
