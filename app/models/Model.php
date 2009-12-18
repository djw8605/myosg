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

abstract class Model
{
    public function __construct($params = array())
    {
        $this->params = $params;
    }
    
    protected function load($params)
    {
        $params_m = array_merge($this->params, $params);
        $sql = $this->sql($params_m);
        return db($this->ds())->fetchAll($sql);
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
        return db($this->ds())->fetchOne($sql);
    }

    public abstract function sql($params);

    //returns which db connection to use
    public abstract function ds();
}
