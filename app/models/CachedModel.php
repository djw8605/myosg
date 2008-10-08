<?

//simply cache the entire content for each parameter
abstract class CachedModel extends Model
{
    private static $cache = array();
    public function get($param = array())
    {
        $str_param = print_r($param, true);
        if(!isset(CachedModel::$cache[get_class($this)][$str_param])) {
            CachedModel::$cache[get_class($this)][$str_param] = $this->load($param);
        }
        return CachedModel::$cache[get_class($this)][$str_param];
    }

}
