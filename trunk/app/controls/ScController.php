<?

class ScController extends ControllerBase
{
    public static function default_title() { return "Support Center"; }
    public static function default_url($query) { return ""; }

    public function load()
    {
        $this->setpagetitle($this->default_title());
        $this->selectmenu("sc");

        if(isset($_REQUEST["datasource"])) {
            $this->sc_ids = $this->process_sclist();
            if(count($this->sc_ids) == 0) {
                $this->view->info = "No Support Center matches your current criteria. Please adjust your criteria in order to display any data.";
            }
        }
        $this->load_daterangequery();
    }

    public function xmlAction()
    {
        $this->setpagetitle($this->default_title());

        //find if xml.phtml exists for this control
        $name = $this->getRequest()->getControllerName();
        $path = $this->view->getScriptPath("").$name."/xml.phtml";
        if(file_exists($path)) {
            //if so, then we support xml
            parent::xmlAction();
        } else {
            $this->render("noxml", null, true);
        }
    }

    //from user query, find the list of scs to display information
    protected function process_sclist()
    {
        $sc_ids = array();

        if(isset($_REQUEST["all_scs"])) {
            $model = new SupportCenters();
            $scs = $model->get();
            foreach($scs as $sc) {
                if(isset($_REQUEST["show_disabled"])) {
                    $sc_ids[] = (int)$sc->id;
                } else {
                    //filter by disable flag
                    if($sc->disable == 0) {
                        $sc_ids[] = (int)$sc->id;
                    }
                }
            }
        } else {
            foreach($_REQUEST as $key=>$value) {
                if(isset($_REQUEST["sc"])) {
                    if(preg_match("/^sc_(\d+)/", $key, $matches)) {
                        $this->process_sclist_addsc($sc_ids, $matches[1]);
                    }
                }
            }
        }
        //filter the sc list based on user query
        $sc_ids = $this->process_sc_filter($sc_ids);

        return $sc_ids;
    }

    private function process_sclist_addsc(&$sc_ids, $sc_id)
    {
        if(!in_array($sc_id, $sc_ids)) {
            $sc_ids[] = (int)$sc_id;
        }
    }

    private function process_sc_filter($scs)
    {
        //setup filter
        if(isset($_REQUEST["active"])) {
            $keep = $this->process_sc_filter_active();
            $scs = array_intersect($scs, $keep);
        }
        return $scs;
    }

    private function process_sc_filter_active()
    {
        $scs_to_keep = array();
        $model = new SupportCenters();
        $scs = $model->getindex();
        $active_value = $_REQUEST["active_value"];
        foreach($scs as $rid=>$r) {
            if($r[0]->active == $active_value) {
                if(!in_array($rid, $scs_to_keep)) {
                    $scs_to_keep[] = (string)$rid;
                }
            }
        }
        return $scs_to_keep;
     }
}
