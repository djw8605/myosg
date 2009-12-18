<?php
/**************************************************************************************************

Copyright 2009 The Trustees of Indiana University

Licensed under the Apache License, Version 2.0 (the "License"); you may not use this file except in
compliance with the License. You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software distributed under the License
is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or
implied. See the License for the specific language governing permissions and limitations under the
License.

**************************************************************************************************/

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
