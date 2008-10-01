<?

//simply cache the entire content for each parameter
abstract class CachedModel
{
    private static $cache = array();
    public function __construct()
    {
        if(!Zend_Registry::isRegistered("db")) {
            $this->db = connectdb();
        } else {
            $this->db = Zend_Registry::get("db");
        }
    }

    public function get($param = "")
    {
        if(!isset(CachedModel::$cache[get_class($this)][$param])) {
            CachedModel::$cache[get_class($this)][$param] = $this->load($param);
        }
        return CachedModel::$cache[get_class($this)][$param];
    }

    protected function load($param)
    {
        $sql = $this->sql($param);
        return $this->db->fetchAll($sql);
    }
    public abstract function sql($param);
}
