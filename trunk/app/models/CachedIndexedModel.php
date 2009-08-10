<?

//index the retured result based on specified index for easy retreival
abstract class CachedIndexedModel extends CachedModel
{
    private static $indexed_cache = array();
    abstract public function key();

    //returns list of record grouped by key() field.
    public function getindex($params = array()) {
        $params = array_merge($this->params, $params);

        $str_param = print_r($params, true);
        if(!isset(CachedIndexedModel::$indexed_cache[get_class($this)][$str_param])) {
            $records = $this->get($params);

            //index the record set
            $key = $this->key();
            CachedIndexedModel::$indexed_cache[get_class($this)][$str_param] = null; //incase records is empty
            foreach($records as $record) {
                CachedIndexedModel::$indexed_cache[get_class($this)][$str_param][$record->$key][] = $record;
            }
        }
        return CachedIndexedModel::$indexed_cache[get_class($this)][$str_param];
    }
}
