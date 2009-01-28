<?

class WizardcurrentstatusController extends WizardController
{
    public function breads() { return array("rsv", "wizard"); }
    public static function default_title() { return "Current Status"; }
    public static function default_url($query) { return ""; }

    public function indexAction()
    {
        $this->load();

        $model = new Resource();

        $this->view->cache = array();
        $this->view->resources = array();

        //load current status cache for all requested resource_ids
        foreach($this->resource_ids as $rid) {
            $cache_filename_template = config()->current_resource_status_xml_cache;
            $cache_filename = str_replace("<ResourceID>", $rid, $cache_filename_template); 
            if(file_exists($cache_filename)) {
                $cache_xml = file_get_contents($cache_filename);
                $this->view->cache[$rid] = new SimpleXMLElement($cache_xml);
                $recs = $model->get(array("resource_id"=>$rid));
                $this->view->resources[$rid] = $recs[0];
            }
        }
    }
}
