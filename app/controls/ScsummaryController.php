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

class ScsummaryController extends ScController
{
    public static function default_title() { return "Support Center Summary"; }
    public static function default_url($query) { return ""; }

    public function load()
    {
        parent::load();

        $model = new SupportCenters();
        $scs = $model->getindex();
        $this->view->scs = array();
        foreach($this->sc_ids as $sc_id) {
            $this->view->scs[$sc_id] = $scs[$sc_id][0];
        }

        if(isset($_REQUEST["summary_attrs_showcontact"])) {
            $this->view->contacts = array();
            $cmodel = new SupportCenterContact();
            $contacts = $cmodel->getindex();
            //group by contact_type_id
            foreach($this->sc_ids as $sc_id) {
                $types = array();
                if(isset($contacts[$sc_id])) {
                    foreach($contacts[$sc_id] as $contact) {
                        if(!isset($types[$contact->contact_type])) {
                            $types[$contact->contact_type] = array();
                        }
                        $types[$contact->contact_type][] = $contact;
                    }
                    $this->view->contacts[$sc_id] = $types;
                }
            }
        }

        if(isset($_REQUEST["summary_attrs_showsites"])) {
            $model = new Site();
            $this->view->sites = $model->getgroupby("sc_id");
        }
    }

    /* this page no longer exist. therefore, we are deprecating page
    // View for http://www.opensciencegrid.org/Support_Centers
    public function legacyosgwebsiteviewAction()
    {
        $sc_ids = $this->process_sclist();
        header("Content-type: text/html");
        echo "<html>\n<head></head>\n<body>\n\n<h3>Support Centers</h3>\n\n<table border='1' width=\'100%\'>\n <tr><th rowspan=2 align=left>Support Center</th><th align=left>Primary Operations Contact</th><th align=left>Email</th><th align=left>Phone</th></tr><tr><th colspan=3 align=left>Community</th></tr>\n";

        $model = new SupportCenters();
        $scs = $model->getindex();
        $cmodel = new SupportCenterContact();
        $sccontacts = $cmodel->getindex(array("contact_type_id"=>4, "contact_rank_id"=>1));

        foreach($sc_ids as $sc_id) {
            $sc = $scs[$sc_id][0];
            $name = $sc->name;
            $long_name = $sc->long_name;
            $community = $sc->community;

            echo "<tr><td rowspan=2>$long_name ($name) </td>";

            $contact = @$sccontacts[$sc_id];
            if(isset($contact[0])) {
                $contact_name = $contact[0]->name . "<br>";
                $contact_email = $contact[0]->primary_email . "<br>";
                $contact_phone = $contact[0]->primary_phone. "<br>";
                if ($contact[0]->primary_phone_ext != "") {
                  $contact_phone = $contact_phone . " (" . $contact[0]->primary_phone_ext . ")";
                }
                echo "<td>$contact_name </td><td>$contact_email </td><td>$contact_phone </td>";
            } else {
                echo "<td colspan=3>(No contact information available)</td>";
            }

            echo "</tr>";
            echo "<tr><td colspan=3>$community </td></tr>\n";
        }

        echo "</table>\n\n</body>";
        $this->render("none", null, true);
    }
    */
}
