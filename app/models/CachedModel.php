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

//simply cache the entire content for each parameter
abstract class CachedModel extends Model
{
    private static $cache = array();
    public function get($params = array())
    {
        $params = array_merge($this->params, $params);
        $str_param = print_r($params, true);
        if(!isset(CachedModel::$cache[get_class($this)][$str_param])) {
            //dlog(get_class($this)." // callin load with ".print_r($this->params, true));
            CachedModel::$cache[get_class($this)][$str_param] = $this->load($params);
        }
        return CachedModel::$cache[get_class($this)][$str_param];
    }

}
