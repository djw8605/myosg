<?php
/*#################################################################################################

Copyright 2013 The Trustees of Indiana University

Licensed under the Apache License, Version 2.0 (the "License"); you may not use this file except in
compliance with the License. You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software distributed under the License
is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or
implied. See the License for the specific language governing permissions and limitations under the
License.

#################################################################################################*/

class VopfmatrixController extends VoController
{
    public static function default_title() { return "Perfsonar VO Matrix"; }
    public static function default_url($query) { return ""; }

    public function indexAction() {
        parent::load();
        message("warning", "This page will soon be deprecated by maddash.");

        $model = new VirtualOrganization();
        $vos = $model->getindex();//TODO - load everthing!?

        $model = new Perfsonar();
        $matrixlist = $model->getMatrixList();

        $this->view->vos = array();
        foreach($this->vo_ids as $vo_id) {
            $vo = $vos[$vo_id][0];

            //find all matrix under this vo
            $vo->matrices = array();
            foreach($matrixlist as $m) {
                $mid = $m->id;
                $needle = "#vo_$vo_id/";
                $pos = strpos($m->name, "#vo_$vo_id/");
                if($pos !== false) {
                    //pull type
                    $mtype = substr($m->name, $pos + strlen($needle));
                    
                    /*
                    //load the matrix
                    $vo->matrices[$mtype] = $model->getMatrixByDatastoreID($mid);
                    */
                    $vo->matrices[$mid] = array("type"=>$mtype, "name"=>substr($m->name, 0, $pos));
                }
            }

            $this->view->vos[$vo_id] = $vo;
        }

        $this->setpagetitle(self::default_title());
    }

    public function matrixAction() {
        if(isset($_REQUEST["id"])) {
            $mid = (int)$_REQUEST["id"];
            $model = new Perfsonar();
            $this->view->data  = $model->getMatrixByDatastoreID($mid);
        } else {
            header("HTTP/1.0 404 Not Found");
        }
        $this->render("json");
    }
}
