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

class VosummaryController extends VoController
{
    public static function default_title() { return "Virtual Organization Summary"; }
    public static function default_url($query) { return ""; }

    public function load()
    {
        parent::load();

        $model = new VirtualOrganization();
        $vos = $model->getindex();

        $scmodel = new SupportCenters();
        $scs = $scmodel->getindex();

        if(isset($_REQUEST["summary_attrs_showmember_resource"])) {
            $this->view->resource_ownerships = array();

            $ownmodel = new VOOwnedResources();
            $resource_ownerships =  $ownmodel->getindex();
            $rmodel = new Resource();
            $rs = $rmodel->getindex();
            foreach($this->vo_ids as $vo_id) {
                $resource_list = array();
                if(isset($resource_ownerships[$vo_id])) {
                    foreach($resource_ownerships[$vo_id] as $resource_ownership) {
                        $resource = $rs[$resource_ownership->resource_id][0];
                        $resource_list[$resource->id] = $resource;
                    }
                }
                $this->view->resource_ownerships[$vo_id] = $resource_list;
            }
        }
    
        if(isset($_REQUEST["summary_attrs_showfield_of_science"])) {
            $vofsmodel = new VOFieldOfScience();
            $vofss = $vofsmodel->getindex();
            $this->view->field_of_science = array();
            foreach($this->vo_ids as $vo_id) {
                $fs_for_vo = @$vofss[$vo_id];
                if($fs_for_vo !== null) {
                    $ranks = array();
                    foreach($fs_for_vo as $fs) {
                        $name = $fs->name;
                        $rank_id = $fs->rank_id;
                        if(!isset($ranks[$rank_id])) {
                            $ranks[$rank_id] = array();
                        }
                        $ranks[$rank_id][$name] = $fs;
                    }
                    ksort($ranks);
                    $this->view->field_of_science[$vo_id] = $ranks;
                }
            }
        }

        if(isset($_REQUEST["summary_attrs_showreporting_group"])) {
            $reportmodel = new VOReport();
            $reports = $reportmodel->getindex();
            
            $fqanmodel = new VOReportFQAN();
            $fqans = $fqanmodel->getindex();

            $contactmodel = new VOReportContact();
            $contacts = $contactmodel->getindex();

            $this->view->reports = array();
            foreach($this->vo_ids as $vo_id) {
                $this->view->reports[$vo_id] = array();
                if(isset($reports[$vo_id])) {
                    foreach($reports[$vo_id] as $report) {
                        $report->fqans = @$fqans[$report->id];
                        $report->contacts = @$contacts[$report->id];
                        $this->view->reports[$vo_id][] = $report;
                    }
                }
            }
        }

        if(isset($_REQUEST["summary_attrs_showparent_vo"])) {
            $model = new VOVO();
            $this->view->vovo = $model->getindex();
        }

        if(isset($_REQUEST["summary_attrs_showcontact"])) {
            $this->view->contacts = array();
            $cmodel = new VOContact();
            $contacts = $cmodel->getindex();
            //group by contact_type_id
            foreach($this->vo_ids as $vo_id) {
                $types = array();
                if(isset($contacts[$vo_id])) {
                    foreach($contacts[$vo_id] as $contact) {
                        if(!isset($types[$contact->contact_type])) {
                            $types[$contact->contact_type] = array();
                        }
                        $types[$contact->contact_type][] = $contact;
                    }
                    $this->view->contacts[$vo_id] = $types;
                }
            }
        }

        if(isset($_REQUEST["summary_attrs_showoasis"])) {
            $model = new VOOasisUser();
            $oasis_managers = $model->getindex($this->vo_ids);
            $grouped = array();
            foreach($oasis_managers as $vo_id=>$managers) {
                if(!isset($grouped[$vo_id])) {
                    $grouped[$vo_id] = array();
                }
                //group by contact_id
                foreach($managers as $manager) {
                    $manager=(array)$manager;
                    $contactid = $manager["contact_id"];
                    if(!isset($grouped[$vo_id][$contactid])) {
                        $manager["dns"] = array($manager["dn"]);
                        $grouped[$vo_id][$contactid] = $manager;
                    } else {
                        $grouped[$vo_id][$contactid]["dns"][] = $manager["dn"];
                    }
                }
            }
            $this->view->oasis_managers = $grouped;
        }

        $this->view->vos = array();
        foreach($this->vo_ids as $vo_id) {
            $vo = $vos[$vo_id][0];

            //lookup support center
            $vo->sc = $scs[$vo->sc_id][0];
            $this->view->vos[$vo_id] = $vo;

            //parse oasis_repo_urls
            if(!is_null($vo->oasis_repo_urls)) {
                $vo->oasis_repo_urls = explode("|", $vo->oasis_repo_urls, -1);
            }
        }

        $this->setpagetitle(self::default_title());
    }

    // View for http://www.opensciencegrid.org/VO_List
    public function legacyosgwebsiteviewAction()
    {
      $vo_ids = $this->process_volist();
      header("Content-type: text/html");
      echo "<html>\n<head></head>\n<body>\n\n<h3>Virtual Organizations</h3>\n\n<table width=\'100%\'>\n <tr><th align=left>VO Name</th><th align=left>Primary URL</th></tr>\n";

      $model = new VirtualOrganization();
      $vos = $model->getindex();
      
      $scmodel = new SupportCenters();
      $scs = $scmodel->getindex();
      
      foreach($vo_ids as $vo_id) {
        $vo = $vos[$vo_id][0];
        $long_name = $vo->long_name;
        $name = $vo->name;
        $primary_url = $vo->primary_url;
        echo " <tr><td>$long_name ($name)</td><td><a href=\"$primary_url\">$primary_url</a></td></tr>\n";
      }
      echo "</table>\n\n</body>";

      $this->render("none", null, true);
    }
}
