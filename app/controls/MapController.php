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

class MapController extends ControllerBase
{
    public static function default_title() { return "Status Map"; }
    public static function default_url($query) { return ""; }

    public function load()
    {
        //pull sites
        $site_model = new Site();
        $sites = $site_model->get();
        $this->view->sites = $sites;

        //pull site groups
        $rgroup_model = new ResourceGroup();
        $rgroups = $rgroup_model->get();
        $this->view->rgs = array();

        $site_ids = $this->process_sitelist();
        foreach($site_ids as $site_id) {
            $rgs = array();
            foreach($rgroups as $rgroup) {
                //filter by gridtype
                if(isset($_REQUEST["gridtype"])) {
                    $gridtype = $rgroup->osg_grid_type_id;
                    if(!isset($_REQUEST["gridtype_".$gridtype])) {
                        continue;
                    }
                }
                //only pass non-disable resource group
                if($rgroup->disable == 1) {
                    continue;
                }
                if($rgroup->site_id == $site_id) {
                    $rgs[] = $rgroup;
                }
            }
            $this->view->rgs[$site_id] = $rgs;
        }

        //pull sites (grouped by site group id)
        $rgrouped_model = new ResourceByGroupID();
        $this->view->resources_bygid = $rgrouped_model->getindex();

        //get resource status
        $model = new LatestResourceStatus();
        $this->view->resource_status = $model->getgroupby("resource_id");
        $downtime_model = new Downtime();
        $this->view->downtime = $downtime_model->getindex(array("start_time"=>time(), "end_time"=>time()));

        $this->setpagetitle(self::default_title());
    }

    //override these to use page instance of uwa / mobile.phtml
    public function uwaAction()
    {
        $this->load();
    }
    public function igoogleAction()
    {
        $this->load();
    }
     public function mobileAction()
    {
        $this->load();
    }

    public function kmlAction()
    {
        $this->load();

        $filename = "osg_site_map.kml";

        header("Content-type: application/vnd.google-earth.kml+xml; charset=utf8");
        header("Content-Disposition: attachment; filename=$filename");
        //recreate the original non-xml url
?><!-- This KML was generated with a query in following MyOSG page
<?=fullbase()."/".pagename()."/?".$_SERVER["QUERY_STRING"]?>
--><?
    }

    public function iframeAction()
    {
        $this->load();
    }

    public function promoAction()
    {
        $this->view->page_title = "OSG Sites in the U.S.";
    }

    public function promoOldAction()
    {
        $model = new Facilities();
        $smodel = new Site();
        $sites = $smodel->getgroupby("facility_id");
        $this->view->markers = array();
        foreach($model->get() as $facility) {
            $site = $sites[$facility->id][0];//pick the first site
            //create acronym
            $name = $facility->name;
            $name2 = str_replace("_", " ", $name);
            $tokens = split(" ", $name2);
            $acro = "";
            foreach($tokens as $token) {
                if($token == "The") continue;
                if($token == "of") continue;
                if($token == "at") continue;
                $acro .= $token[0];
            }
            $lat = $site->latitude;
            $long = $site->longitude;

            //merge to a site nearby
            $merged = false;
            foreach($this->view->markers as &$m) {
                if(abs($m["longitude"] - $long) < 1 and abs($m["latitude"] - $lat) < 0.4) {
                    $m["name"] .= "/".$name;
                    $m["acronym"] .= " ".$acro;
                    $m["longitude"] = ($m["longitude"] + $long) / 2;
                    $m["latitude"] = ($m["latitude"] + $lat)/ 2;
                    $merged = true;
                    break;
                }
            }

            if(!$merged) {
                $marker = array("name"=>$name, "acronym"=>$acro, "longitude"=>$long, "latitude"=>$lat, "sites"=>$sites[$facility->id]);
                //$this->view->markers[] = $marker;
            }
        }

        $this->view->page_title = "OSG Promotional View";
    }

    public function promoiconAction()
    {
        header("Content-type: image/png");

        $text = $_REQUEST["text"];
        $image_cache = "/tmp/myosg.imagecache.$text";
        if(file_exists($image_cache) and filectime($image_cache) > time() - 60) {
        } else {
            //$image = imagecreatefrompng("./images/rss.png");
            // Create the image

            $src = imagecreatefrompng('images/small_green_ball.png');
            $im = imagecreatetruecolor(200, 15);
            imagesavealpha($im, true);
            imagealphablending($im, false);
            // Create some colors
            $textcolor = imagecolorallocate($im, 0, 0, 0);
            $shadowcolor = imagecolorallocate($im, 255, 255, 255);
            $trans = imagecolorallocatealpha($im, 200, 200, 200, 127);
            imagefill($im, 0, 0, $trans);
            //drop the anchor image
            imagecopy($im, $src, 0,0,0,0,15,15);

            // The text to draw
            // Replace path by your own font path
            $font = "images/verdanab.ttf";

            $font_size = 10;
            $xpos = 20;
            $ypos = 12;

            // Add the text
            //imagettftext($im, $font_size, 0, $xpos+2, $ypos+2, $shadowcolor, $font, $text);
            imagettftext($im, $font_size, 0, $xpos, $ypos, $textcolor, $font, $text);

            // Using imagepng() results in clearer text compared with imagejpeg()
            imagepng($im, $image_cache);
            imagedestroy($im);
        }
        readfile($image_cache);

        $this->render("none", null, true);
    }

    public function promo2Action()
    {
        header("Content-type: image/png");

        $im = imagecreatefromjpeg('images/continental.jpg');
        imagealphablending($im, true);
        imagesavealpha($im, true);

        $textcolor = imagecolorallocatealpha($im, 0, 0, 0, 0);
        $shadowcolor = imagecolorallocatealpha($im, 255, 255, 255, 64);
        $icon = imagecreatefrompng('images/small_green_ball.png');
        $icon_width = 10;
        $icon_height = 10;

        $model = new Facilities();
        $smodel = new Site();
        $sites = $smodel->getgroupby("facility_id");
        $markers = array();
        foreach($model->get() as $facility) {
            $site = $sites[$facility->id][0];//pick the first site
            //create acronym
            $name = $facility->name;
            $name2 = str_replace("_", " ", $name);
            $tokens = split(" ", $name2);
            $acro = "";
            foreach($tokens as $token) {
                if($token == "The") continue;
                if($token == "of") continue;
                if($token == "at") continue;
                $acro .= $token[0];
            }
            $lat = $site->latitude;
            $long = $site->longitude;

            //merge to a site nearby
            $merged = false;
            if(isset($_REQUEST["acro"])) {
                foreach($markers as &$m) {
                    if(abs($m["longitude"] - $long) < 2.5 and abs($m["latitude"] - $lat) < 0.5) {
                        $m["name"] .= "/".$name;
                        $m["acronym"] .= "/".$acro;
                        $m["longitude"] = ($m["longitude"] + $long) / 2;
                        $m["latitude"] = ($m["latitude"] + $lat)/ 2;
                        $merged = true;
                        break;
                    }
                }
            } else {
                $acro = "";
            }

            //draw mark
            if(!$merged) {
                $marker = array("name"=>$name, "acronym"=>$acro, "longitude"=>$long, "latitude"=>$lat, "sites"=>$sites[$facility->id]);
                $markers[] = $marker;
            }
        }

        //draw all markers
        $font = "images/verdanab.ttf";
        $font_size = 9;
        foreach($markers as $marker) {
            list($x, $y) = $this->ll2xy($marker["longitude"], $marker["latitude"]);
            imagecopy($im, $icon, $x,$y,0,0,$icon_width,$icon_height);
            imagettftext($im, $font_size, 0, $x+$icon_width+1, $y+$icon_height+1, $shadowcolor, $font, $marker["acronym"]);
            imagettftext($im, $font_size, 0, $x+$icon_width, $y+$icon_height, $textcolor, $font, $marker["acronym"]);
        }

        imagepng($im);
        imagedestroy($im);

        $this->render("none", null, true);
    }

    public function ruthAction()
    {
        $this->load();
        $this->setpagetitle("Status Map (Ruth's View)");
    }
 
    private function ll2xy($long, $lat) 
    {
        $width = 1024.0;
        $height = 530.0;

        $top = 50.0;
        $bottom = 25.0;
        $left = -124.0;
        $right = -64.5;

        $longspan_d = $width/($right - $left);
        $latspan_d = $height/($bottom - $top);

        $x = $longspan_d * ($long - $left);
        $y = $latspan_d * ($lat - $top);
        slog("$long/$lat = $x/$y");
        return array($x, $y);
    }
    

    protected function process_sitelist()
    {
        $site_ids = array();

        if(isset($_REQUEST["all_sites"])) {
            $model = new Site();
            $sites = $model->get();
            foreach($sites as $site) {
                $site_ids[] = $site->id;
            }
        } else {
            foreach($_REQUEST as $key=>$value) {
                if(isset($_REQUEST["sc"])) {
                    if(preg_match("/^sc_(\d+)/", $key, $matches)) {
                        $this->process_sitelist_addsc($site_ids, $matches[1]);
                    }
                }
                if(isset($_REQUEST["facility"])) {
                    if(preg_match("/^facility_(\d+)/", $key, $matches)) {
                        $this->process_sitelist_addfacility($site_ids, $matches[1]);
                    }
                }
            }
        }

        //filter the site list based on user query
        $site_ids = $this->process_site_filter($site_ids);
        return $site_ids;
    }

    private function process_sitelist_addsc(&$site_ids, $sc_id)
    {
        //load all site under the requested site_group_id
        $model = new Site();
        $sites = $model->get(array("sc_id"=>$sc_id));
        foreach($sites as $site) {
            if(!in_array($site->id, $site_ids)) {
                $site_ids[] = $site->id;
            }
        }
    }

    private function process_sitelist_addfacility(&$site_ids, $facility_id)
    {
        //load all site under the requested site_group_id
        $model = new Site();
        $sites = $model->get(array("facility_id"=>$facility_id));
        foreach($sites as $site) {
            if(!in_array($site->id, $site_ids)) {
                $site_ids[] = $site->id;
            }
        }
    }

    private function process_site_filter($sites)
    {
        if(isset($_REQUEST["active"])) {
            $keep = $this->process_site_filter_active();
            $sites = array_intersect($sites, $keep);
        }
        if(isset($_REQUEST["disable"])) {
            $keep = $this->process_site_filter_disable();
            $sites = array_intersect($sites, $keep);
        }
        return $sites;
    }

    private function process_site_filter_active()
    {
        $sites_to_keep = array();
        $model = new Site();
        $sites = $model->get();
        $active_value = $_REQUEST["active_value"];
        foreach($sites as $site) {
            if($site->active == $active_value) {
                if(!in_array($site->id, $sites_to_keep)) {
                    $sites_to_keep[] = $site->id;
                }
            }
        }
        return $sites_to_keep;
    }

    private function process_site_filter_disable()
    {
        $sites_to_keep = array();
        $model = new Site();
        $sites = $model->get();
        $disable_value = $_REQUEST["disable_value"];
        foreach($sites as $site) {
            if($site->disable == $disable_value) {
                if(!in_array($site->id, $sites_to_keep)) {
                    $sites_to_keep[] = $site->id;
                }
            }
        }
        return $sites_to_keep;
    }
}
