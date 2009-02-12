<?

abstract class WizardGratiaController extends WizardController
{
    //maps between graph type and actual Gratia graph source
    abstract public function map();

    public function load()
    {
        parent::load();
        
        $resource_model = new Resource();
        $resources = $resource_model->getindex();

        $legend = false;
        list($urlbase, $sub_title, $ylabel) = $this->map();
        $this->view->sub_title = $sub_title;

        $this->load_daterangequery();

        $start_time = date("Y-m-d h:i:s", $this->view->start_time);
        $end_time = date("Y-m-d h:i:s", $this->view->end_time);

        $resource_names = array();
        foreach($this->resource_ids as $resource_id) {
            $resource_info = $resources[$resource_id][0];
            $resource_names[] = $resource_info->name;
        }
        $this->view->url = $urlbase."?facility=".implode("|",$resource_names)."&title=&ylabel=$ylabel&starttime=$start_time&endtime=$end_time";
        if(!$legend) {
            $this->view->url .= "&legend=False";
        }
        $this->view->resource_names = implode(" / ", $resource_names);
        $this->setpagetitle(self::default_title());
    }
}

