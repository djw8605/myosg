<?

class RgcurrentstatusController extends RgController
{
    public static function default_title() { return "Current RSV Status"; }
    public static function default_url($query) { return ""; }

    public function load()
    {
        parent::load();
        $this->view->rgs = $this->rgs;

        //load current status cache for all requested resources
        $this->view->cache = array();
        foreach($this->view->rgs as $rgid=>$rg) {
            foreach($rg as $rid=>$resource) {
                $cache_filename_template = config()->current_resource_status_xml_cache;
                $cache_filename = str_replace("<ResourceID>", $rid, $cache_filename_template); 
                if(file_exists($cache_filename)) {
                    $cache_xml = file_get_contents($cache_filename);
                    $this->view->cache[$rid] = new SimpleXMLElement($cache_xml);
                }
            }
        }
    }
}
