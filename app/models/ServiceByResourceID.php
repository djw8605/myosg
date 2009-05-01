<?

class ServiceByResourceID extends CachedIndexedModel
{
    public function ds() { return "oim"; }
    public function sql($param)
    {
        return "SELECT * FROM resource_service WHERE service_id IN (SELECT id FROM service WHERE service_group_id=1)";
    }
    public function key() { return "resource_id"; }
}
