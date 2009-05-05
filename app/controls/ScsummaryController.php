<?
class ScsummaryController extends ScController
{
    public static function default_title() { return "Support Center Summary"; }
    public static function default_url($query) { return ""; }

    public function load()
    {
        parent::load();

        $model = new SupportCenters();
        $scs = $model->getindex();
        $cmodel = new SupportCenterContact();
        $sccontacts = $cmodel->getindex(array("contact_type_id"=>4, "contact_rank_id"=>1));

        $this->view->scs = array();
        foreach($this->sc_ids as $sc_id) {
            $info = $scs[$sc_id][0];
            $info->contact = @$sccontacts[$sc_id];
            $this->view->scs[$sc_id] = $info;
        }

        $this->setpagetitle(self::default_title());
    }

}
