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

class MiscmetricController extends MiscController
{
    public static function default_title() { return "RSV Metrics"; }
    public static function default_url($query) { return ""; }

    public function load()
    {
        parent::load();

        $model = new Metric();
        $this->view->metrics = $model->getindex();
    
        //additional info
        if(isset($_REQUEST["metric_attrs_showservices"])) {
            $model = new MetricService();
            $this->view->metricservices = $model->getgroupby("metric_id");
            $model = new Service();
            $this->view->services = $model->getindex();
        }

        $this->setpagetitle(self::default_title());
    }
}
