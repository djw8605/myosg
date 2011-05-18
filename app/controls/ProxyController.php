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

class ProxyController extends Zend_Controller_Action 
{ 
    public function tinyurlAction() 
    { 
        echo "<b>Tiny URL</b><br>";
        echo "<input type=\"text\" value=\"";
        echo file_get_contents("http://tinyurl.com/api-create.php?url=".$_REQUEST["url"]);
        echo "\">";
        $this->render("none", null, true);
    }
} 
