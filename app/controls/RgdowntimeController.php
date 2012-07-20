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

class RgdowntimeController extends RgController
{
    public static function default_title() { return "Downtime Information"; }
    public static function default_url($query) { return ""; }

    public function load()
    {
        parent::load();
        $model = new Downtime();
        $downtimes = $model->get();

        $past = array();
        $current = array();
        $future = array();
        
        foreach($downtimes as $downtime) {
            $id = $downtime->id;
            if($downtime->unix_end_time < time()) {
                if(isset($_REQUEST["downtime_attrs_showpast"])) {
                    switch($_REQUEST["downtime_attrs_showpast"]) {
                    case "": break; //none..
                    default:
                        $days = (int)$_REQUEST["downtime_attrs_showpast"];
                        if($downtime->unix_end_time < (time() - 3600*24*$days)) {
                            break; //too old
                        }
                    case "all":
                        $past[$id] = $downtime;
                    }
                } 
            } else if($downtime->unix_start_time > time()) {
                $future[$id] = $downtime;
            } else {
                $current[$id] = $downtime;
            }
        }

        $this->view->past_downtimes = $this->formatInfo($past);
        $this->view->current_downtimes = $this->formatInfo($current);
        $this->view->future_downtimes = $this->formatInfo($future);
        $this->setpagetitle(self::default_title());
    }

    function icalAction()
    {
        $this->load();
    }

    function formatInfo($downtime_recs)
    {
        if($downtime_recs === null) {
            return array();
        }

        $downtimes = array();
        $resource_model = new Resource();
        $resources = $resource_model->getindex();

        $downtime_service_model = new DowntimeService();
        $downtime_services = $downtime_service_model->get();

        $model = new ResourceGroup();
        $rg_info  = $model->getindex();

        $model = new Service();
        $service_info = $model->getindex();

        $model = new DowntimeClass();
        $downtime_class = $model->getindex();

        $model = new DowntimeSeverity();
        $downtime_severity = $model->getindex();

        $model = new DN();
        $dns = $model->getindex();

        $model = new Contact();
        $contacts = $model->getindex();

        //pull all resource ids that we are interested in
        $resource_ids = array();
        foreach($this->rgs as $rgid=>$rg) {
            foreach($rg as $rid=>$resource) {
                $resource_ids[] = $rid;
            }
        }

        foreach($downtime_recs as $downtime)
        {
            if(in_array($downtime->resource_id, $resource_ids)) {
                //only show event that we have pulled resource for
                $resource = $resources[$downtime->resource_id];
                $resource_name = $resource[0]->name;
                $resource_fqdn = $resource[0]->fqdn;
                $rg_id = $resource[0]->resource_group_id;
                $rg_name = $rg_info[$resource[0]->resource_group_id][0]->name;

                if($resource_name !== null) {
                    $start = date(config()->date_format_full, $downtime->unix_start_time);
                    $end = date(config()->date_format_full, $downtime->unix_end_time);

                    //get affected services
                    $affected_services = array();
                    foreach($downtime_services as $service) {
                        if($service->resource_downtime_id == $downtime->id) {
                            $info = $service_info[$service->service_id][0];
                            $affected_services[] = $info;
                        }
                    }

                    $desc = $downtime->downtime_summary;
                    //slog($desc);

                    $severity = $downtime_severity[$downtime->downtime_severity_id][0]->name;
                    $class = $downtime_class[$downtime->downtime_class_id][0]->name;
                    $dn = $dns[$downtime->dn_id][0]->dn_string;
                    $contact_id  = $dns[$downtime->dn_id][0]->contact_id;
                    $contact_name = $contacts[$contact_id][0]->name;

                    $downtimes[] = array("id"=>$downtime->id, 
                        "name"=>$resource_name,
                        "fqdn"=>$resource_fqdn,
                        "rg_name"=>$rg_name,
                        "rg_id"=>$rg_id,
                        "resource_id"=>$downtime->resource_id,
                        "desc"=>$desc,
                        "severity"=>$severity,
                        "class"=>$class,
                        "services"=>$affected_services,
                        "unix_start_time"=>$downtime->unix_start_time,
                        "unix_end_time"=>$downtime->unix_end_time,
                        "start_time"=>$start,
                        "dn"=>$dn,
                        "contact_name"=>$contact_name,
                        "end_time"=>$end
                    );
                }
            }
        }
        return $downtimes;
    }
}
