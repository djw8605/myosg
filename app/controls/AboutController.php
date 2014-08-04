<?php
/*#################################################################################################

Copyright 2014 The Trustees of Indiana University

Licensed under the Apache License, Version 2.0 (the "License"); you may not use this file except in
compliance with the License. You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software distributed under the License
is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or
implied. See the License for the specific language governing permissions and limitations under the
License.

#################################################################################################*/

class AboutController extends ControllerBase
{
    public static function default_title() { return "MyOSG"; }
    public static function default_url($query) { return ""; }

    public function load()
    {
        $this->parseUCV();
        $this->setpagetitle(self::default_title());
    }

    public function parseUCV()
    {
        $this->view->ucv = array();

        //myosg.ucv.html is generated via cron
        $html = file_get_contents("/tmp/myosg.ucv.html");
        $lines = explode("\n", $html);

        //load User Contributed Links list and pull the table content
        $in = false;
        foreach($lines as $line) {
            if(!$in) {
                //detect beginning
                if(strpos($line, "<textarea")) {
                    $in = true;
                    continue;
                }
            }
            if($in) {
                //detect end
                if(strpos($line, "</textarea")) {
                    $in = false;
                    break;
                }
                $tokens  = explode("|", $line);
                if(count($tokens) == 7) {
                    $this->view->ucv[] = $tokens;
                }
            }
        }
    } 
} 
