<?

class ServiceByResourceID extends CachedIndexedModel
{
    public function sql($param)
    {
        return "SELECT * from oim.resource_service where active = 1 and disable = 0";
    }
    public function key() { return "resource_id"; }
}
