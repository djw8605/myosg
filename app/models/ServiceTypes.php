<?

class ServiceTypes extends CachedIndexedModel
{
    public function sql($param)
    {
        return "SELECT * FROM oim.service";
        //return "SELECT * FROM oim.service S WHERE S.service_id IN (SELECT service_id FROM oim.service_service_group WHERE service_group_id=1) AND S.service_id NOT IN (SELECT DISTINCT PS.parent_service_id psid FROM oim.service PS WHERE PS.parent_service_id IS NOT NULL)";
    }
    public function key() { return "service_id"; }
}


?>
