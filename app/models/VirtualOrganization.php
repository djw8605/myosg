<?

class VirtualOrganization extends CachedModel
{
    public function sql($param)
    {
        return "SELECT * FROM oim.virtualorganization order by short_name";
    }
}
