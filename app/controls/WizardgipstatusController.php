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

        $model = new LDIF();
        $gip = $model->getValidationSummary();
        $cemonbdii = $model->getBdii();

        //merge those xmls
        $this->view->resources = array();
        foreach($this->resource_ids as $resource_id) {
            $resource_info = $resources[$resource_id][0];
            $tests = array();

            //if not found use following defaults
            $testtime = null;
            $overallstatus = "NA";

            //search for this resource name
            $found = false;
            foreach($gip->Resource as $resource) {
                if($resource_info->name == $resource->Name) {
                    foreach($resource->TestCase as $test) {
                        $tests[(string)$test->Name] = array("status"=>(string)$test->Status, "reason"=>(string)$test->Reason);
                    }
                    $found = true;
                    $testtime = strtotime((string)$gip->TestRunTime);
                    $overallstatus = $resource->OverAllStatus;
                    break;
                }
            }

            //search for cemon raw file links
            $rawdata = array();
            $cemon = null;
            //search prod..
            foreach($cemonbdii->resource as $resource) {
                if($resource->name == $resource_info->name) {
                    $cemon = $resource;
                    break;
                }
            } 
            //if we have data, pull it out
            if($cemon !== null) {
                $rawdata["processed_osg_data"] = $cemon->processed_osg_data;
                $rawdata["processed_wlcg_interop_data"] = $cemon->processed_wlcg_interop_data;
                $rawdata["cemon_raw_data"] = $cemon->cemon_raw_data;
            }

            $this->view->resources[$resource_id] = array(
                "testtime"=>$testtime,
                "name"=>$resource_info->name, 
                "fqdn"=>$resource_info->fqdn, 
                "rawdata"=>$rawdata,
                "overallstatus"=>$overallstatus,
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
        
            //try to find the detail in prod directory first
            $xmlname = config()->gip_detail;
            $xmlname = str_replace("<resource_name>", $resource_name, $xmlname);
            if(!file_exists($xmlname)) {
                //try the itb directory
                $xmlname = config()->gip_detail_itb;
                $xmlname = str_replace("<resource_name>", $resource_name, $xmlname);
            }
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
