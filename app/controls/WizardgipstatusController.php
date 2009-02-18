<?

class WizardgipstatusController extends WizardController
{
    public function breads() { return array("rsv", "wizard"); }
    public static function default_title() { return "GIP Validation Status"; }
    public static function default_url($query) { return ""; }

    public function load()
    {
        parent::load();
        
        $resource_model = new Resource();
        $resources = $resource_model->getindex();

        $cache_xml = file_get_contents("http://is-dev.grid.iu.edu/gip-validator/xml/oim.xml");
        $this->view->rawxml = $cache_xml; //for xml view
        $cache = new SimpleXMLElement($cache_xml);

        $this->view->resources = array();

        foreach($this->resource_ids as $resource_id) {
            $resource_info = $resources[$resource_id][0];
            $resource_name = $resource_info->name;

            $tests = array();

            //search for this resource name
            $found = false;
            foreach($cache->Resource as $resource) {
                if($resource_name == $resource->Name) {
                    foreach($resource->TestCase as $test) {
                        $tests[(string)$test->Name] = array("status"=>(string)$test->Status, "reason"=>(string)$test->Reason);
                    }
                    $found = true;
                    break;
                }
            }

            if(!$found) {
                $tests = array();
            }
            $this->view->resources[$resource_id] = array("tests"=>$tests,"name"=>$resource_name);
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

            $cache_xml = file_get_contents("http://is-dev.grid.iu.edu/gip-validator/xml/gipvalidate_${resource_name}_detail.xml");
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
