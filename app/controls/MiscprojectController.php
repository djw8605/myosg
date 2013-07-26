<?php
/*#################################################################################################

Copyright 2013 The Trustees of Indiana University

Licensed under the Apache License, Version 2.0 (the "License"); you may not use this file except in
compliance with the License. You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software distributed under the License
is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or
implied. See the License for the specific language governing permissions and limitations under the
License.

#################################################################################################*/

class MiscprojectController extends MiscController
{
    public static function default_title() { return "Projects"; }
    public static function default_url($query) { return ""; }

    public function load()
    {
        parent::load();

        $model = new Project();
        $this->view->projects = $model->get();

        if(isset($_REQUEST["project_attrs_showpublication"])) {
            $model = new ProjectPublication();
            $this->view->publications = $model->getindex();
        }

        $this->setpagetitle(self::default_title());
    }
}
