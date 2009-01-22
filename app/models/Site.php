<?

class Site extends CachedModel
{
    public function sql($params)
    {
        $where = "";
        if(isset($params["facility_id"])) {
            $where = " and facility_id = ".$params["facility_id"];
        }
        if(isset($params["sc_id"])) {
            $where = " and sc_id = ".$params["sc_id"];
        }
        return "SELECT site_id, name, longitude, latitude FROM oim.site ".
            "where active = 1 and disable = 0 and ".
            "longitude is not null and longitude <> '' and ".
            "latitude is not null and latitude <> '' $where order by name";
    }
}
