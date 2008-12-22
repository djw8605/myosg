<?

class VirtualOrganization extends CachedModel
{
    public function sql($params)
    {
        $where = "";
        if(isset($params["sc_id"])) {
            $where = " and sc_id = ".$params["sc_id"];
        }
        $sql = "SELECT * FROM oim.virtualorganization where active = 1 and disable = 0 $where order by short_name ";
        return $sql;
    }
}
