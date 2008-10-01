<?

class ServiceByResourceID extends CachedIndexedModel
{
    public function sql($param)
    {
        return "SELECT * from oim.resource_service $param";
    }
    public function key() { return "resource_id"; }
}
