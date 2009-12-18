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

class AdminController extends ControllerBase
{ 
    //public function breads() { return array("admin"); }
    public static function default_title() { return "Administration"; }
    public static function default_url($query) { return ""; }

    public function load()
    {
        //make sure the request originated from localhost
        if($_SERVER["REMOTE_ADDR"] != $_SERVER["SERVER_ADDR"]) {
            //pretend that this page doesn't exist
            $this->getResponse()->setRawHeader('HTTP/1.1 404 Not Found');
            echo "404";
            exit;
        }
        $this->setpagetitle(self::default_title());
    }

    public function logrotateAction()
    {
        $this->load();
        $root = getcwd()."/";
        $statepath = "/tmp/viewer.rotate.state";
        $config = "compress \n".
            $root.config()->logfile. " ". 
            $root.config()->error_logfile. " ". 
            $root.config()->audit_logfile." {\n".
            "   rotate 5\n".
            "   size=50M\n".
            "}";
        $confpath = "/tmp/viewer.rotate.conf";
        $fp = fopen($confpath, "w");
        fwrite($fp, $config);
        
        passthru("/usr/sbin/logrotate -s $statepath $confpath");

        $this->render("none", null, true);
    }

    public function optimizeAction()
    {
        $model = new Admin();
        $model->optimize();
        $this->render("none", null, true);
    }

/*
    public function dedupdetailAction()
    {
        $model = new DuplicateDetail();
        $duplicates = $model->get();

        //dedup..
        foreach($duplicates as $dup) {
            echo $dup->detail;
            echo "<blockquote>";
            $ids = split(" ", trim($dup->ids));
            $model->dedup($ids);
            echo "</blockquote>";
        }

        $this->render("none", null, true);
    }
*/
} 
