<?

class ServiceByResourceID extends CachedIndexedModel
{
    public function ds() { return "oim"; }
    public function sql($param)
    {
        //return "SELECT rs.*, s.* FROM resource_service rs join service s on rs.service_id = s.id WHERE service_id IN (SELECT id FROM service WHERE service_group_id=1)";
        return "SELECT rs.*, s.* FROM resource_service rs join service s on rs.service_id = s.id WHERE service_id IN (SELECT id FROM service)";
    }
    public function key() { return "resource_id"; }
}
