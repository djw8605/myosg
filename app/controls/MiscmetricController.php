<?
class MiscmetricController extends MiscController
{
    public static function default_title() { return "RSV Metrics"; }
    public static function default_url($query) { return ""; }

    public function load()
    {
        parent::load();

        $model = new Metric();
        $this->view->metrics = $model->getindex();
    
        //additional info
        if(isset($_REQUEST["metric_attrs_showservices"])) {
            $model = new MetricService();
            $this->view->metricservices = $model->getgroupby("metric_id");
            $model = new Service();
            $this->view->services = $model->getindex();
        }

        $this->setpagetitle(self::default_title());
    }
}
