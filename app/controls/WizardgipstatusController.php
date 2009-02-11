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

        $cache_xml = file_get_contents("http://is-dev.grid.iu.edu/gip-validator/index.xml");
        $this->view->rawxml = $cache_xml; //for xml view
        $cache = new SimpleXMLElement($cache_xml);

        $this->view->resources = array();

        foreach($this->resource_ids as $resource_id) {
            $resource_info = $resources[$resource_id][0];
            $resource_name = $resource_info->name;

            //search for this resource name
            foreach($cache->Site as $site) {
                $attrs = $site->attributes();
                if($resource_name == $attrs["name"]) {
                    $rec = array(
                        "name"=>$resource_name,
                        "test"=>$attrs["test"], 
                        "result"=>$attrs["result"], 
                        "path"=>$attr["path"]."#".$resource_name);
                    $this->view->resources[$resource_id] = $rec;
                    break;
                }
            }
        }

        $this->setpagetitle(self::default_title());
    }
}
