<?php
/**************************************************************************************************

Copyright 2009 The Trustees of Indiana University

Licensed under the Apache License, Version 2.0 (the "License"); you may not use this file except in
compliance with the License. You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software distributed under the License
is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or
implied. See the License for the specific language governing permissions and limitations under the
License.

**************************************************************************************************/

class CampusGrid
{
    public function getIcons() {
        return array(
            array("name"=>"Fermilab", "lat"=>41.85143, "lon"=>-88.242767, "icon"=>"images/campus/fermilab.gif"),
            array("name"=>"Virginia Tech", "lat"=>37.221196, "lon"=>-80.426488, "icon"=>"images/campus/vt.png"),
            array("name"=>"Nebraska", "lat"=>40.819818, "lon"=>-96.705651, "icon"=>"images/campus/n.png"),
            array("name"=>"GLOW", "lat"=>43.071568, "lon"=>-89.406931, "icon"=>"images/campus/uw.gif")
        );
    }
}

?>
