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
            array("name"=>"UF", "lat"=>29.681312, "lon"=>-82.5, "icon"=>"images/campus/uf.png", 
                "desc"=>"The SSERCA (Sunshine State Education and Research Computing Alliance) Grid is a proposed state-wide distributed computing environment targeted to share resources at different campuses to meet the scientific computing needs of the public and private member universities in Florida."),

            array("name"=>"UCSD", "lat"=>33.016928, "lon"=>-116.846046, "icon"=>"images/campus/ucsd.png", 
                "desc"=>"The particle physics group enables scientists in Astronomy, Biology, Chemistry, Climate Science, Engineering, Oceanography, Pharmacology, and Physics to use computing resources across campus and beyond."),

            array("name"=>"PU", "lat"=>40.424923, "lon"=>-87.0, "icon"=>"images/campus/pu.png", 
                "desc"=>"Researchers and educators at Purdue University-West Lafayette (Purdue), Purdue University-Calumet (Calumet), and the University of Notre Dame."),

            array("name"=>"Fermilab", "lat"=>41.85143, "lon"=>-88.242767, "icon"=>"images/campus/fermilab.gif",
                "desc"=>"FermiGrid (The Fermilab Campus Grid) integrates clusters at FNAL in support of the Energy, Cosmic and Intensity Frontier Experiments."),

            array("name"=>"Virginia Tech", "lat"=>37.221196, "lon"=>-80.426488, "icon"=>"images/campus/vt.png",
                "desc"=>"The Virginia Tech (VT) Campus Grid is housed at the Virginia Bioinformatics Institute (VBI) and supports VBI laboratory groups as well as the broader life sciences research community at VT."),

            array("name"=>"Nebraska", "lat"=>40.819818, "lon"=>-96.705651, "icon"=>"images/campus/n.png",
                "desc"=>"HCC supports NU faculty and staff from a variety of fields and 4 campuses located at Lincoln, Omaha and Kearney."),

            array("name"=>"GLOW", "lat"=>43.071568, "lon"=>-89.406931, "icon"=>"images/campus/w.png",
                "desc"=>"The Grid Laboratory of Wisconsin is a campus-wide distributed computing environment designed to meet the scientific computing needs of the University of Wisconsin, Madison.")
        );
    }
}

?>
