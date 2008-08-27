<?

require_once("model_base.php");

class ServiceTypes
{
    public function __construct()
    {
        if(!Zend_Registry::isRegistered("db")) {
            $this->db = connectdb();
        } else {
            $this->db = Zend_Registry::get("db");
        }
    }

    public function fetchAll()
    {
        $schema = config()->db_oim_schema;
        //$sql = "select * from $schema.service s where service_id in (select service_id from $schema.service_service_group g where g.service_group_id = 1)";
        $sql = "SELECT * FROM $schema.service S WHERE S.service_id IN (SELECT service_id FROM $schema.service_service_group WHERE service_group_id=1) AND S.service_id NOT IN (SELECT DISTINCT PS.parent_service_id psid FROM $schema.service PS WHERE PS.parent_service_id IS NOT NULL)";
        return $this->db->fetchAll($sql);
    }
}


?>
