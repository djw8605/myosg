<?
class ScsummaryController extends ScController
{
    public static function default_title() { return "Support Center"; }
    public static function default_url($query) { return ""; }

    public function load()
    {
        parent::load();

        $model = new SupportCenters();
        $scs = $model->getindex();

        $this->view->scs = array();
        foreach($this->sc_ids as $sc_id) {
            $this->view->scs[$sc_id] = $scs[$sc_id][0];
        }

        $this->setpagetitle(self::default_title());
    }

}
