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

class VoController extends ControllerBase
{
    public static function default_title() { return "Virtual Organization"; }
    public static function default_url($query) { return ""; }

    public function load()
    {
        $this->setpagetitle($this->default_title());
        $this->selectmenu("vo");

        $this->vo_ids = array();
        if(isset($_REQUEST["datasource"])) {
            $this->vo_ids = $this->process_volist();
            if(count($this->vo_ids) == 0) {
                $this->view->info = "No Virtual Organization matches your current criteria. Please adjust your criteria in order to display any data.";
            }
        }
        $this->load_daterangequery();
    }

    public function xmlAction()
    {
        $this->setpagetitle($this->default_title());

        //find if xml.phtml exists for this control
        $name = $this->getRequest()->getControllerName();
        $path = $this->view->getScriptPath("").$name."/xml.phtml";
        if(file_exists($path)) {
            //if so, then we support xml
            parent::xmlAction();
        } else {
            $this->render("noxml", null, true);
        }
    }

    //from user query, find the list of vos to display information
    protected function process_volist()
    {
        $vo_ids = array();

        if(isset($_REQUEST["all_vos"])) {
            $model = new VirtualOrganization();
            $vos = $model->get();
            foreach($vos as $vo) {
                if(isset($_REQUEST["show_disabled"])) {
                    $vo_ids[] = (int)$vo->id;
                } else {
                    //filter by disable flag
                    if($vo->disable == 0) {
                        $vo_ids[] = (int)$vo->id;
                    }
                }
            }
        } else {
            foreach($_REQUEST as $key=>$value) {
                if(isset($_REQUEST["vo"])) {
                    if(preg_match("/^vo_(\d+)/", $key, $matches)) {
                        $this->process_volist_addvo($vo_ids, $matches[1]);
                    }
                }
            }
        }
        //filter the vo list based on user query
        $vo_ids = $this->process_vo_filter($vo_ids);

        return $vo_ids;
    }

    private function process_volist_addvo(&$vo_ids, $vo_id)
    {
        if(!in_array($vo_id, $vo_ids)) {
            $vo_ids[] = (int)$vo_id;
        }
    }

    private function process_vo_filter($vos)
    {
        //setup filter
        if(isset($_REQUEST["active"])) {
            $keep = $this->process_vo_filter_active();
            $vos = array_intersect($vos, $keep);
        }
        if(isset($_REQUEST["sc"])) {
            $keep = $this->process_vo_filter_sc();
            $vos = array_intersect($vos, $keep);
        }
        return $vos;
    }

    private function process_vo_filter_active()
    {
        $vos_to_keep = array();
        $model = new VirtualOrganization();
        $vos = $model->getindex();
        $active_value = $_REQUEST["active_value"];
        foreach($vos as $rid=>$r) {
            if($r[0]->active == $active_value) {
                if(!in_array($rid, $vos_to_keep)) {
                    $vos_to_keep[] = (string)$rid;
                }
            }
        }
        return $vos_to_keep;
     }
    private function process_vo_filter_sc()
    {
        $vos_to_keep = array();
        $model = new SupportCenters();
        $list = $model->get();

        foreach($list as $sc_id=>$item) {
            if(isset($_REQUEST["sc_".$sc_id])) {
                $model = new VirtualOrganization();
                $vos = $model->get(array("sc_id"=>$sc_id));
                foreach($vos as $vo) {
                    if(!in_array($vo->id, $vos_to_keep)) {
                        $vos_to_keep[] = $vo->id;
                    }
                }
            }
        }
        return $vos_to_keep;
    }
}
