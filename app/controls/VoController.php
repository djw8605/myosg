<?

class VoController extends ControllerBase
{ 
    public function pagename() { return "vo"; }
    public function load() 
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

        $this->view->page_title = "Virtual Organizations";
    }
} 
