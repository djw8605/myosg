<?

class Resource extends CachedModel
{
    public function sql($param)
    {
        return "SELECT resource_id id, name, fqdn FROM oim.resource $param";
    }
}
