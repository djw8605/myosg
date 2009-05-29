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
    
/*
        //additional info
        if(isset($_REQUEST["summary_attrs_showsomething"])) {
            //LOAD information for something..
        }
*/

        $this->setpagetitle(self::default_title());
    }
}
