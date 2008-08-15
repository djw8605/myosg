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
        //protected $_name = config()->db_oim_schema.'.service';
        $schema = config()->db_oim_schema;
        $sql = "select * from $schema.service s where service_id in (select service_id from $schema.service_service_group g where g.service_group_id = 1)";
        return $this->db->fetchAll($sql);
    }
}


?>
