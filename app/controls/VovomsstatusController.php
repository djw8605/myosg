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

class VovomsstatusController extends VoController
{
    public static function default_title() { return "VOMS Status"; }
    public static function default_url($query) { return ""; }

    public function load()
    {
        parent::load();

        $model = new VirtualOrganization();
        $vos = $model->getindex();

        $model = new VOMS();
        $voms = $model->get();
        $this->view->timestamp = $model->getTimestamp();
        $this->view->voms_status = array();
        foreach($this->vo_ids as $vo_id) {
            $info = $vos[$vo_id][0]; 

            //capitaliza to increase chance of VO name match (until we use OIM based vomses)
            $name = strtoupper($info->name);

            $this->view->voms_status[$vo_id] = array(
                "info"=>$info,
                "voms"=>@$voms[$name]
            );
        }

/*
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
            $fsmodel = new FieldOfScience();
            $fss = $fsmodel->getindex();
            
            $vofsmodel = new VOFieldOfScience();
            $vofss = $vofsmodel->getindex();

            $this->view->field_of_science = array();
            foreach($this->vo_ids as $vo_id) {
                $list = array();
                $fs_for_vo = @$vofss[$vo_id];
                if($fs_for_vo !== null) {
                    foreach($fs_for_vo as $fs) {
                        $list[] = $fss[$fs->field_of_science_id][0];
                    }
                }
                $this->view->field_of_science[$vo_id] = $list;
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

        $this->view->vos = array();
        foreach($this->vo_ids as $vo_id) {
            $vo = $vos[$vo_id][0];

            //lookup support center
            $vo->sc = $scs[$vo->sc_id][0];
            $this->view->vos[$vo_id] = $vo;
        }
*/

        $this->setpagetitle(self::default_title());
    }
}
