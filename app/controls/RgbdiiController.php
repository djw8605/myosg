<?php
/*#################################################################################################

Copyright 2009 The Trustees of Indiana University

Licensed under the Apache License, Version 2.0 (the "License"); you may not use this file except in
compliance with the License. You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software distributed under the License
is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or
implied. See the License for the specific language governing permissions and limitations under the
License.

#################################################################################################*/


class RgbdiiController extends RgController
{
    public static function default_title() { return "BDII Information"; }
    public static function default_url($query) { return ""; }

    public function load()
    {
        parent::load();
        
        $model = new BDII();
        $bdii = $model->get();

        var_dump($bdii);

        $sub_title = "TreeMap";
        $this->setpagetitle($this->default_title()." - ".$sub_title);
    }
}
