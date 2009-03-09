<?

class WizardgipstatusController extends WizardController
{
    public function breads() { return array("rsv", "wizard"); }
    public static function default_title() { return "Current GIP Validation Status"; }
    public static function default_url($query) { return ""; }

    public function load()
    {
        parent::load();
        
        $resource_model = new Resource();
        $resources = $resource_model->getindex();

        //load gip summary (1)
        $cache_xml = file_get_contents(config()->gip_summary);
        $cache = new SimpleXMLElement($cache_xml);

        //load gip summary (2) -- for ITB
        $cache_xml2 = file_get_contents(config()->gip_summary2);
        $cache2 = new SimpleXMLElement($cache_xml2);

        //cemon raw file listing for production
        $cemonbdii_url = file_get_contents(config()->cemonbdii_url);
        $cemonbdii = new SimpleXMLElement($cemonbdii_url);

        //cemon raw file listing for itb
        $cemonbdii_itb_url = file_get_contents(config()->cemonbdii_itb_url);
        $cemonbdii_itb = new SimpleXMLElement($cemonbdii_itb_url);

        //merge those xmls
        $this->view->resources = array();
        foreach($this->resource_ids as $resource_id) {
            $resource_info = $resources[$resource_id][0];

            $tests = array();

            //search for this resource name
            $found = false;
            //$type = "production";
            foreach($cache->Resource as $resource) {
                if($resource_info->name == $resource->Name) {
                    foreach($resource->TestCase as $test) {
                        $tests[(string)$test->Name] = array("status"=>(string)$test->Status, "reason"=>(string)$test->Reason);
                    }
                    $found = true;
                    $cemon_links = $cemonbdii;
                    $testtime = strtotime((string)$cache->TestRunTime);
                    break;
                }
            }
            if(!$found) {
                //search on second xml (for ITB)
                foreach($cache2->Resource as $resource) {
                    if($resource_info->name == $resource->Name) {
                        foreach($resource->TestCase as $test) {
                            $tests[(string)$test->Name] = array("status"=>(string)$test->Status, "reason"=>(string)$test->Reason);
                        }
                        $found = true;
                        $cemon_links = $cemonbdii_itb;
                        $testtime = strtotime((string)$cache2->TestRunTime);
                        break;
                    }
                }
            }

            //search for cemon raw file links
            $rawdata = array();
            if($found) {
                foreach($cemon_links->resource as $resource) {
                    if($resource->name == $resource_info->name) {
                        $rawdata["processed_osg_data"] = $resource->processed_osg_data;
                        $rawdata["processed_wlcg_interop_data"] = $resource->processed_wlcg_interop_data;
                        $rawdata["cemon_raw_data"] = $resource->cemon_raw_data;
                    }
                } 
            }

            $this->view->resources[$resource_id] = array(
                "testtime"=>$testtime,
                "name"=>$resource_info->name, 
                "fqdn"=>$resource_info->fqdn, 
                "rawdata"=>$rawdata,
                "tests"=>$tests
            );
        }
        $this->setpagetitle(self::default_title());
    }

    public function detailAction()
    {
        $rid = (int)$_REQUEST["rid"];
        $resource_model = new Resource();
        $resources = $resource_model->getindex();
        $resource_info = $resources[$rid][0];

        if($resource_info === null) {
            echo "no such resource";
            $this->render("none", null, true);
        } else {
            $resource_name = $resource_info->name;
            $xmlname = config()->gip_detail;
            $xmlname = str_replace("<resource_name>", $resource_name, $xmlname);
            $cache_xml = file_get_contents($xmlname);
            $cache = new SimpleXMLElement($cache_xml);

            $dirty_name = $_REQUEST["name"];
            //validate dirty_name
            switch($dirty_name) {
            case "Validate_GIP_BDII":
            case "Missing_Sites":
            case "Interop_Reporting_Check":
            case "Validate_GIP_URL":
                $name = $dirty_name;
            }
            $case = $cache->xpath("//TestCase/Name[.='$name']/parent::*");
            $this->view->detail = $case[0];
        }
    }
}
