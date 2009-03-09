<?

class SiteController extends ControllerBase
{
    public function breads() { return array("site"); }
    public static function default_title() { return "Site Specific Reports"; }
    public static function default_url($query) { return ""; }

    public function load()
    {
        $this->view->graph_url = "http://www.mwt2.org/sys/rev-gatekeeper_logscale.png";
        $this->setpagetitle(self::default_title());
    }
}
