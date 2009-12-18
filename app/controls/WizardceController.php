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

include("WizardGratiaController.php");

class WizardceController extends WizardGratiaController
{
    public function breads() { return array("rsv", "wizard"); }
    public static function default_title() { return "CE Specific Gratia Accounting"; }
    public static function default_url($query) { return ""; }

    public function map() {
        $dirty_type = @$_REQUEST["ce_account_type"];
        switch($dirty_type) 
        {
        case "gip_vo":
            $urlbase = "http://t2.unl.edu/gratia/gip_graphs/gip_vo";
            $sub_title = "Running Jobs by VO";
            $ylabel = "Jobs";
            break;
        case "gip_vo_waiting":
            $urlbase = "http://t2.unl.edu/gratia/gip_graphs/gip_vo_waiting";
            $sub_title = "Queued Jobs by VO";
            $ylabel = "Jobs";
            break;
        case "gip_site_size":
            $urlbase = "http://t2.unl.edu/gratia/gip_graphs/gip_site_size";
            $sub_title = "Size of Site Over Time";
            $ylabel = "Total CPUs";
            break;
        case "rsv_metric_quality":
            $urlbase = "http://t2.unl.edu/gratia/rsv_graphs/rsv_metric_quality";
            $sub_title = "RSV Data";
            $ylabel = "Probe Names";
            break;
        default:
            elog("unknown account_type - maybe bot?");
        }
        return array($urlbase, $sub_title, $ylabel);
    }
}
