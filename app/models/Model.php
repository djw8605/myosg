<?

abstract class Model
{
    public function __construct($params = array())
    {
        if(!Zend_Registry::isRegistered("db")) {
            $this->db = connectdb();
        } else {
            $this->db = Zend_Registry::get("db");
        }
        $this->params = $params;
    }
    
    protected function load($params)
    {
        $params_m = array_merge($this->params, $params);
        $sql = $this->sql($params_m);
        return $this->db->fetchAll($sql);
    }

    public function get($params = array())
    {
        return $this->load($params);
    }

    public abstract function sql($params);
}
