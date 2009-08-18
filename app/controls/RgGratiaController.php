<?

abstract class RgGratiaController extends RgController
{
    //maps between graph type and actual Gratia graph source
    abstract public function map();

    public function load()
    {
        parent::load();
        
        $model = new ResourceGroup();
        $resource_groups = $model->getindex();

        $legend = false;
        list($urlbase, $sub_title, $ylabel) = $this->map();

        //$this->load_daterangequery();

        $start_time = date("Y-m-d h:i:s", $this->view->start_time);
        $end_time = date("Y-m-d h:i:s", $this->view->end_time);

        $resource_names = array();
        foreach($this->rgs as $rgid=>$rg) {
            $resource_group = $resource_groups[$rgid][0];
            foreach($rg as $rid=>$resource) {
                $resource_names[] = $resource->name;
            }
        }
        $this->view->url = $urlbase."?facility=".implode("|",$resource_names)."&title=&ylabel=$ylabel&starttime=$start_time&endtime=$end_time";
        if(!$legend) {
            $this->view->url .= "&legend=False";
        }
        $this->view->resource_names = implode(" / ", $resource_names);
        $this->setpagetitle($this->default_title()." - ".$sub_title);
    }
}

