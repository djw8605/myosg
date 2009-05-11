<?

class Site extends CachedModel
{
    public function ds() { return "oim"; }
    public function sql($params)
    {
        $where = "where 1 = 1";
        if(isset($params["facility_id"])) {
            $where = " and facility_id = ".$params["facility_id"];
        }
        if(isset($params["sc_id"])) {
            $where = " and sc_id = ".$params["sc_id"];
        }
        return "SELECT * FROM site $where order by name";
    }
}
