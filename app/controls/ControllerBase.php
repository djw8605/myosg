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

//*pagename* is just a name of controller. so pageid might be a better naming..
$g_pagename = "please_reset_me";
function pagename() { global $g_pagename; return $g_pagename; }
function setpagename($name) { 
    global $g_pagename; 
    $g_pagename = $name; 
}

abstract class ControllerBase extends Zend_Controller_Action
{
    public function init()
    {
        setpagename($this->pagename());
        $this->selectmenu($this->pagename());
        $this->setpagetitle("Untitled Page");
    }

    public function setpagetitle($title)
    {
        $this->view->page_title = $title;
    }

    public function indexAction()
    {
        $this->load();
    }
    public function selectmenu($menu)
    {
        $this->view->menu_selected = $menu;
    }

    public function htmlAction()
    {
        $this->load();
    }

    public function uwaAction()
    {
        $this->load();
        $this->render("uwa", null, true);
    }
    public function igoogleAction()
    {
        $this->load();
        $this->render("igoogle", null, true);
    }
    public function mobileAction()
    {
        $this->load();
        $this->render("mobile", null, true);
    }

    public function adduwaAction()
    {
        $url = fullbase()."/".pagename()."/uwa?".$_SERVER["QUERY_STRING"];
        header("Location: http://www.netvibes.com/subscribe.php?module=UWA&moduleUrl=".urlencode($url));
        exit;
    }

    public function addigoogleAction()
    {
/*
        $url = urlencode(fullbase()."/".pagename()."/uwa?".$_SERVER["QUERY_STRING"]);
        $url = urlencode("www.netvibes.com/api/uwa/compile/google.php?moduleUrl=".$url);
*/
        $url = fullbase()."/".pagename()."/igoogle?".$_SERVER["QUERY_STRING"];
        header("Location: http://www.google.com/ig/add?moduleurl=".urlencode($url));
        exit;
    }

    public function xmlAction()
    {
        $this->load();
        header("Content-type: text/xml");
        $this->view->header = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
        $this->view->header .= "<!--\n";
        $this->view->header .= "If you are going to use this XML for your production service, please register this URL at\n";
        $this->view->header .= "GOC in order for us to ensure that any changes to this XML will not break your service.\n";
        $this->view->header .= "https://spreadsheets.google.com/viewform?hl=en&formkey=dE8xYjUtMk9zZlQ3WktMa2lTWV9MOHc6MA\n\n";
        $this->view->header .= "This XML was generated via following MyOSG page:\n";
        $this->view->header .= fullbase()."/".pagename()."/?".$_SERVER["QUERY_STRING"]."\n";
        $this->view->header .= "-->";
    }

    public function waveAction()
    {
        $this->load();
        header("Content-type: text/xml");
        echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
    ?>
<Module>
  <ModulePrefs title="Hello Wave">
    <Require feature="wave" /> 
    <Require feature="dynamic-height" /> 
  </ModulePrefs>
  <Content type="html">
<![CDATA[     
<style type="text/css">
    <?include("css/mobile.css.php");?>
</style>
<div style="height: 200px; overflow-y: scroll">
    <?echo file_get_contents(fullbase()."/".pagename()."/html?uwa=true&".$_SERVER["QUERY_STRING"]);?>
</div>
]]>
  </Content>
</Module> 
<?
        $this->render("none", null, true);
    }

    public function csvAction()
    {
        $this->load();
        $this->render("none", null, true);
        header("Content-type: text/csv; charset=UTF-8");
        header("Content-Disposition: attachment; filename=\"".pagename().".csv\"");

        //output bom for utf-8 (so that excel will open this as UTF-8 file! UTF-8 should not need BOM but Excel doesn't know that..
        echo chr(239).chr(187).chr(191);

        $xmlpath = $this->view->getScriptPath("").pagename()."/xml.phtml";
        if(file_exists($xmlpath)) {
            $xml_content = $this->view->render(pagename()."/xml.phtml");
            require_once("app/xml2csv.php");
            xml2csv($xml_content);
        } else {
            echo "No CSV content is available for this page.";
        }
    }

    public function pagename() {
        //use controller name
        return Zend_Controller_Front::getInstance()->getRequest()->getControllerName();
    } 

    protected function load_daterangequery()
    {
        $today_begin = (int)(time() / (3600*24));
        $today_begin *= 3600*24;

        //set some defaults
        if(!isset($_REQUEST["start_type"])) {
            $_REQUEST["start_type"] = "7daysago";
        }
        if(!isset($_REQUEST["end_type"])) {
            $_REQUEST["end_type"] = "now";
        }

        switch($_REQUEST["start_type"]) {
        case "yesterday":
            $this->view->start_time = $today_begin - 3600*24;
            break;
        case "7daysago":
            $this->view->start_time = $today_begin - 3600*24*7;
            break;
        case "30daysago":
            $this->view->start_time = $today_begin - 3600*24*30;
            break;
        case "specific":
            if(isset($_REQUEST["start_date"])) {
                $str = $_REQUEST["start_date"];
                $this->view->start_time = strtotime($str);
            } else {
                throw new exception("User didn't specify start_date");
            }
            break;
        }

        switch($_REQUEST["end_type"]) {
        case "today":
            $this->view->end_time = $today_begin;
            break;
        case "now":
            $this->view->end_time = time();
            break;
        case "specific":
            if(isset($_REQUEST["end_date"])) {
                $str = $_REQUEST["end_date"];
                $this->view->end_time = strtotime($str);
            } else {
                throw new exception("User didn't specify end_date");
            }
            break;
        }
    }


    //abstract public function breads(); //return array containing pagename leading to this page
    abstract public function load();

}
