<?

class VoController extends ControllerBase
{ 
    public function breads() { return array("rsv"); }
    public static function default_title() { return "Virtual Organization"; }
    public static function default_url($query) { return ""; }

    public function load() 
    { 
        if(isset($_REQUEST["group"]) && $_REQUEST["group"] == "resource") {
            $this->load_resourcegrouped();
        }  else {
            $this->load_vogrouped();
        }
    }

    public function load_resourcegrouped()
    {
        $model = new Resource();
        $this->view->resources = $model->get();

        $model = new ResourceOwnership();
        $this->view->resource_ownerships = $model->getindex();

        ///////////////////////////////////////////////////////////////////////
        //Filter
        if(isset($_REQUEST["resource"])) {
            if(trim($_REQUEST["resource"]) != "") {
                $id = (int)$_REQUEST["resource"];
                $newlist = array();
                $newlist[$id] = $this->view->resource_ownerships[$id];
                $this->view->resource_ownerships = $newlist;
            }
        }
        $this->setpagetitle(VoController::default_title(). " - Grouped by Resource");
    }

    public function load_vogrouped()
    {
        ///////////////////////////////////////////////////////////////////////
        //load vo cache
        $cache_filename = config()->vomatrix_xml_cache;
        $cache_xml = file_get_contents($cache_filename);
        $this->view->xml = $cache_xml;
        $vos = new SimpleXMLElement($cache_xml);
        $this->view->vos = array();
        foreach($vos->VOGrouped[0] as $vos) {
            $attributes = $vos->attributes();
            $this->view->vos[(string)$vos->Name[0]] = $vos;
        }        

        $model = new VOOwnedResources();
        $this->view->voownership = $model->getindex();
        $model = new Resource();
        $this->view->resources = $model->get();

        ///////////////////////////////////////////////////////////////////////
        //Filter
        if(isset($_REQUEST["vo"])) {
            if(trim($_REQUEST["vo"]) != "") {
                $void = $_REQUEST["vo"];

                $newlist = array();
                foreach($this->view->vos as $vo) {
                    $attrs = $vo->attributes();
                    if($attrs->id[0] == $void) {
                        $newlist[(string)$vo->Name[0]] = $vo;
                    }
                }
                $this->view->vos = $newlist;
            }
        }
 
        //this doesn't sort case-insensitively... maybe this should be done via rsv-process
        ksort($this->view->vos, SORT_STRING);

        $this->setpagetitle(VoController::default_title());
    }
} 
