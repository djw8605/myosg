<?
class WizardenvController extends WizardController
{
    public static function default_title() { return "Environment Parameters"; }
    public static function default_url($query) { return ""; }

    public function load()
    {
        parent::load();

        $rmodel = new Resource();
        $this->view->resources = $rmodel->getindex();
        $model = new ResourceEnv();
        $details = $model->getindex(array("metric_id"=>0));
        
        $this->view->envs = array();
        foreach($this->resource_ids as $resource_id) {
            $rec = $details[$resource_id][0];
            if($rec !== null) {
                $env = new SimpleXMLElement($rec->xml);
            } else {
                $env = null;
            }
            $this->view->envs[$resource_id] = $env;
        }
        $this->setpagetitle(self::default_title());
    }

}
