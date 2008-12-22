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

    public function getgroupby($key, $params = array()) {
        $recs = $this->get($params);
        $groups = array();
        foreach($recs as $rec) {
            $value = $rec->$key;
            if(!isset($groups[$value])) {
                $groups[$value] = array();
            }
            $groups[$value][] = $rec;
        }
        return $groups;
    }

    public function getcount($params = array())
    {
        $params_m = array_merge($this->params, $params);
        $sql = "select count(*) from (".$this->sql($params_m).") c";
        dlog($sql);
        return $this->db->fetchOne($sql);
    }

    public abstract function sql($params);
}
