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

class SearchController extends ControllerBase
{ 
    public static function default_title() { return "MyOSG"; }
    public static function default_url($query) { return ""; }

    public function load() {
        $this->setpagetitle(self::default_title());
    }
    public function clean($dirty) {
        return trim(preg_replace('/[^a-zA-Z0-9_ +-\.]/', '', $dirty));
    } 

    public function indexAction() { 
        $q = $this->clean($_REQUEST["q"]);
        $this->view->query = $q; 

        $recs = array();
        if(isset($_REQUEST["type"])) {
            $type = $_REQUEST["type"];
        } else {
            $type = "all";
        }
        if(isset($_REQUEST["q"]) && $q != "") {
            $recs = $this->dosearch($type, 999, $q, false);
            $this->view->recs = $recs;
        }
    }

    //set basic to true to load only basic info.
    public function dosearch($type, $max, $q, $basic) {
        //search in oim
        $model = new OIMSearch();
        $recs = array();
        if($type == "all" || $type == "resource") {
            foreach($model->search_resource($q) as $rec) {
                $rec->type = "resource";

                if(!$basic) {
                    //pull resource for each resource (and group by contact type)
                    $rcmodel = new ResourceContact();
                    $contacts = $rcmodel->get(array("resource_id"=>$rec->id));
                    $rec->contacts = array();
                    foreach($contacts as $contact) {
                        if(!isset($rec->contacts[$contact->contact_type])) {
                            $rec->contacts[$contact->contact_type] = array();
                        }
                        $rec->contacts[$contact->contact_type][] = $contact;
                    }
                }
                $recs[] = $rec;
            }
        }
        if(count($recs) > $max) return $recs;
        if($type == "all" || $type == "resource_group") {
            foreach($model->search_resourcegroup($q) as $rec) {
                $rec->type = "resource_group";
                $recs[] = $rec;
            }
        }
        if(count($recs) > $max) return $recs;
        if($type == "all" || $type == "site") {
            foreach($model->search_site($q) as $rec) {
                $rec->type = "site";
                $recs[] = $rec;
            }
        }
        if(count($recs) > $max) return $recs;
        if($type == "all" || $type == "facility") {
            foreach($model->search_facility($q) as $rec) {
                $rec->type = "facility";
                $recs[] = $rec;
            }
        }
        if(count($recs) > $max) return $recs;
        if($type == "all" || $type == "vo") {
            foreach($model->search_vo($q) as $rec) {
                $rec->type = "vo";

                if(!$basic) {
                    $vomodel = new VOContact();
                    $contacts = $vomodel->get(array("vo_id"=>$rec->id));
                    $rec->contacts = array();
                    foreach($contacts as $contact) {
                        if(!isset($rec->contacts[$contact->contact_type])) {
                            $rec->contacts[$contact->contact_type] = array();
                        }
                        $rec->contacts[$contact->contact_type][] = $contact;
                    }
                }
                $recs[] = $rec;
            }
        }
        if(count($recs) > $max) return $recs;
        if($type == "all" || $type == "sc") {
            foreach($model->search_sc($q) as $rec) {
                $rec->type = "sc";
                if(!$basic) {
                    $scmodel = new SupportCenterContact();
                    $contacts = $scmodel->get(array("sc_id"=>$rec->id));
                    $rec->contacts = array();
                    foreach($contacts as $contact) {
                        if(!isset($rec->contacts[$contact->contact_type])) {
                            $rec->contacts[$contact->contact_type] = array();
                        }
                        $rec->contacts[$contact->contact_type][] = $contact;
                    }
                }
                $recs[] = $rec;
            }
        }
        if(count($recs) > $max) return $recs;

        //contact information is only for non-guest
        if(!user()->isGuest()) {
            if($type == "all" || $type == "contact") {
                foreach($model->search_contact($q) as $rec) {
                    $rec->type = "contact";
                    $recs[] = $rec;
                }
            }
        }

        //search goc ticket
        if(!$basic) {
            if($type == "all" || $type == "gocticket") {
                $xml = new SimpleXMLElement(file_get_contents(config()->gocticket_url."/rest/search?q=".urlencode($q)));
                $sorted = array();
                foreach($xml->Tickets->Ticket as $ticket) {
                    $ticket->type = "gocticket";
                    $ticket_id = (int)$ticket->ID;
                    $sorted[$ticket_id] = $ticket;
                }
                krsort($sorted);
                foreach($sorted as $ticket) {
                    $recs[] = $ticket;
                }
            }
        }

        return $recs;
   }

    public function autocompleteAction() {
        $q = $this->clean($_REQUEST["q"]);
        $limit = (int)$_REQUEST["limit"];
        $timestamp = $_REQUEST["timestamp"];

        $recs = $this->dosearch("all", $limit, $q, true);
        function cmp($a, $b) {
            return strcmp($a->v1, $b->v1);
        }
        usort($recs, "cmp");
        foreach($recs as $rec) {
            if(!isset($rec->v2)) $rec->v2 = "";
            echo $rec->v1."\t".$rec->v2."\t".$rec->type."\n";
        }
        $this->render("none", null, true);
    }
} 
