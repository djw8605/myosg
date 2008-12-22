<?

class FacilityContact extends CachedModel
{
    public function sql($params)
    {
        $where = "";
        if(isset($params["facility_id"])) {
            $where = " and facility_id = ".$params["facility_id"];
        }
        return "SELECT * from oim.facility_contact where active = 1 $where";
    }
}
