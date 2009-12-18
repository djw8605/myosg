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

require_once("app/timerange.php");

class WizardarmetricController extends WizardController
{
    public function breads() { return array("rsv", "wizard"); }
    public static function default_title() { return "Availability Metrics"; }
    public static function default_url($query) { return ""; }

    public function load()
    {
        parent::load();
        //$this->load_daterangequery();

        //load resource list
        $resource_model = new Resource();
        $this->view->resources = $resource_model->getindex();

        //load resource services
        $service_type_model = new Service();
        $this->view->services = $service_type_model->getindex();

        //load AR history
        $model = new ServiceAR();
        $params = array();
        $params["start_time"] = $this->view->start_time;
        $params["end_time"] = $this->view->end_time;
        $ar = $model->get($params);

        //group by resource/service_id
        $ar_resource_service = array();
        foreach($ar as $a) {
            $r_id = (int)$a->resource_id;
            if(!isset($ar_resource_service[$r_id])) {
                $ar_resource_service[$r_id] = array();
            }
            $service_id = (int)$a->service_id;
            if(!isset($ar_resource_service[$r_id][$service_id])) {
                $ar_resource_service[$r_id][$service_id] = array();
            }
            $ar_resource_service[$r_id][$service_id][] = $a;
        }

        $data = array();
        foreach($ar_resource_service as $rid => $resource) {
            //filter by resource_id
            if(!in_array($rid, $this->resource_ids)) continue;
            $data[$rid] = array();
            foreach($resource as $service_id=>$service) {
                $count = 0;
                $a_total = 0;
                $r_total = 0;
                foreach($service as $rec) {
                    $count++;
                    $a_total += (double)$rec->availability;
                    $r_total += (double)$rec->reliability;
                }
                //store data
                if($count != 0) {
                    $data[$rid][$service_id] = array(
                        "availability"=>($a_total/$count),
                        "reliability"=>($r_total/$count)
                    );
                }
            }
        }

        //sort data
        if(isset($_REQUEST["sort"])) {
            $dirty_sort = $_REQUEST["sort"];
            switch($dirty_sort) {
            case "resource_name":
                break;
            case "a":
                uasort($data, "cmp_availability");
                break;
            case "r":
                uasort($data, "cmp_reliability");
                break;
            }
        }

        $this->view->data = $data;
        $this->setpagetitle(self::default_title());
    }

}

function cmp_availability($a, $b)
{
    $a_sum = 0;
    foreach($a as $service) {
        $val = $service["availability"];
        $a_sum += $val;
    }
    $a_av = $a_sum / count($a);

    $b_sum = 0;
    foreach($b as $service) {
        $val = $service["availability"];
        $b_sum += $val;
    }
    $b_av = $b_sum / count($b);

    if($a_av == $b_av) return 0;
    return ($a_av > $b_av) ? -1 : 1;
}

function cmp_reliability($a, $b)
{
    $a_sum = 0;
    foreach($a as $service) {
        $val = $service["reliability"];
        $a_sum += $val;
    }
    $a_av = $a_sum / count($a);

    $b_sum = 0;
    foreach($b as $service) {
        $val = $service["reliability"];
        $b_sum += $val;
    }
    $b_av = $b_sum / count($b);

    if($a_av == $b_av) return 0;
    return ($a_av > $b_av) ? -1 : 1;
}

