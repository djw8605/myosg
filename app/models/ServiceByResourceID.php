<?

class ServiceByResourceID extends CachedIndexedModel
{
    public function sql($param)
    {
        return "SELECT * from oim.resource_service";
    }
    public function key() { return "resource_id"; }
}
