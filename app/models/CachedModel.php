<?

//simply cache the entire content for each parameter
abstract class CachedModel extends Model
{
    private static $cache = array();
    public function get($params = array())
    {
        $params = array_merge($this->params, $params);

        $str_param = print_r($params, true);
        if(!isset(CachedModel::$cache[get_class($this)][$str_param])) {
            dlog(get_class($this)." // callin load with ".print_r($this->params, true));
            CachedModel::$cache[get_class($this)][$str_param] = $this->load($params);
        }
        return CachedModel::$cache[get_class($this)][$str_param];
    }

}
