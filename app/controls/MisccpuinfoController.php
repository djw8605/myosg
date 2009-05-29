<?
class MisccpuinfoController extends MiscController
{
    public static function default_title() { return "CPU Information"; }
    public static function default_url($query) { return ""; }

    public function load()
    {
        parent::load();

        $model = new CPUInfo();
        $this->view->cpuinfos = $model->get();
    
/*
        //additional info
        if(isset($_REQUEST["summary_attrs_showsomething"])) {
            //LOAD information for something..
        }
*/

        $this->setpagetitle(self::default_title());
    }
}
