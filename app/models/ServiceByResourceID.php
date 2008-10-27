<?

class ServiceByResourceID extends CachedIndexedModel
{
    public function sql($param)
    {
        return "SELECT * from oim.resource_service ".
            "where ".
            "   service_id in (SELECT service_id FROM oim.service_service_group WHERE service_group_id=1) AND ".
            "   service_id NOT IN (SELECT DISTINCT PS.parent_service_id psid FROM oim.service PS WHERE PS.parent_service_id IS NOT NULL)";
    }
    public function key() { return "resource_id"; }
}
