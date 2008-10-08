<?

class VirtualOrganization extends CachedModel
{
    public function sql($param)
    {
        return "SELECT * FROM oim.virtualorganization where active = 1 and disable = 0 order by short_name ";
    }
}
