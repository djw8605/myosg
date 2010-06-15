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

include("RgGratiaController.php");

class RgaccountController extends RgGratiaController
{
    public static function default_title() { return "Gratia Accounting"; }
    public static function default_url($query) { return ""; }

    public function map() {
        $dirty_type = @$_REQUEST["account_type"];
        switch($dirty_type) 
        {
        case "cumulative_hours":
            $urlbase = "http://t2.unl.edu/gratia/cumulative_graphs/vo_success_cumulative_smry";
            //$urlbase = fullbase()."/gratia/cumulative_graphs/vo_success_cumulative_smry";
            $sub_title = "Cumulative Hours";
            $ylabel = "Hours";
            break;
        case "daily_hours_byvo":
            $urlbase = "http://t2.unl.edu/gratia/bar_graphs/vo_hours_bar_smry";
            //$urlbase = fullbase()."/gratia/bar_graphs/vo_hours_bar_smry";
            $sub_title = "Daily Hours (Grouped by VO)";
            $legend = true;
            $ylabel = "Hours";
            break;
        case "daily_hours_byusername":
            $urlbase = "http://t2.unl.edu/gratia/bar_graphs/dn_hours_bar";
            //$urlbase = fullbase()."/gratia/bar_graphs/dn_hours_bar";
            $sub_title = "Daily Hours (Grouped by Username)";
            $legend = true;
            $ylabel = "Hours";
            break;
        case "job_count_byvo":
            $urlbase = "http://t2.unl.edu/gratia/bar_graphs/vo_job_cnt";
            //$urlbase = fullbase()."/gratia/bar_graphs/vo_job_cnt";
            $sub_title = "Job Count (Grouped by VO)";
            $legend = true;
            $ylabel = "Number of Jobs";
            break;
        case "wall_success":
            $urlbase = "http://t2.unl.edu/gratia/bar_graphs/vo_wall_success_rate";
            //$urlbase = fullbase()."/gratia/bar_graphs/vo_wall_success_rate";
            $sub_title = "VO 'wall success' rate";
            $ylabel = "'Wall Sucess' Rate";
            break;
        case "cpu_efficiency":
            $urlbase = "http://t2.unl.edu/gratia/bar_graphs/facility_cpu_efficiency";
            //$urlbase = fullbase()."/gratia/bar_graphs/facility_cpu_efficiency";
            $sub_title = "CPU Efficiency";
            $ylabel = "Efficiency";
            break;
        default:
            slog("unknown account_type - maybe a bot accessing?");
            exit;
        }
        return array($urlbase, $sub_title, $ylabel);
    }
}
