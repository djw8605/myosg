<?

class Site extends CachedModel
{
    public function sql($param)
    {
        return "SELECT site_id, name, longitude, latitude FROM oim.site ".
            "where active = 1 and disable = 0 and ".
            "longitude is not null and longitude <> '' and ".
            "latitude is not null and latitude <> ''";
    }
}
