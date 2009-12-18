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

class WizardseController extends WizardGratiaController
{
    public function breads() { return array("rsv", "wizard"); }
    public static function default_title() { return "SE Specific Gratia Accounting"; }
    public static function default_url($query) { return ""; }

    public function map() {
        $dirty_type = @$_REQUEST["se_account_type"];
        switch($dirty_type) 
        {
        case "vo_transfer_volume":
            $urlbase = "http://t2.unl.edu/gratia/gip_graphs/gip_vo";
            $sub_title = "Transfer volume (Grouped by VO)";
            $ylabel = "Transfer Volume (GB)";
            break;
        case "user_transfer_volume":
            $urlbase = "http://t2.unl.edu/gratia/transfer_graphs/user_transfer_volume";
            $sub_title = "Transfer Volumn (Grouped by Username)";
            $ylabel = "Transfer Volume (GB)";
            break;
        case "se_space":
            $urlbase = "http://t2.unl.edu/gratia/gip_graphs/se_space";
            $sub_title = "Total Space";
            $ylabel = "GB";
            break;
        case "se_space_free":
            $urlbase = "http://t2.unl.edu/gratia/gip_graphs/se_space_free";
            $sub_title = "Total Free Space";
            $ylabel = "GB";
            break;
        default:
            elog("unknown account_type - maybe a bot");
        }
        return array($urlbase, $sub_title, $ylabel);
    }
}
