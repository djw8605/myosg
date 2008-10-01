<?

//index the retured result based on specified index for easy retreival
abstract class CachedIndexedModel extends CachedModel
{
    private static $indexed_cache = array();
    abstract public function key();

    public function getindex($param = "") {
        if(!isset(CachedIndexedModel::$indexed_cache[get_class($this)][$param])) {
            $records = parent::get($param);

            //index the record set
            $key = $this->key();
            foreach($records as $record) {
                CachedIndexedModel::$indexed_cache[get_class($this)][$param][$record->$key][] = $record;
            }
        }
        return CachedIndexedModel::$indexed_cache[get_class($this)][$param];
    }
}
