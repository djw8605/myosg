<?
class VosummaryController extends VoController
{
    public static function default_title() { return "Virtual Organization Summary"; }
    public static function default_url($query) { return ""; }

    public function load()
    {
        parent::load();

        $model = new VirtualOrganization();
        $vos = $model->getindex();

        $scmodel = new SupportCenters();
        $scs = $scmodel->getindex();

        if(isset($_REQUEST["summary_attrs_showmember_resource"])) {
            $this->view->resource_ownerships = array();

            $ownmodel = new VOOwnedResources();
            $resource_ownerships =  $ownmodel->getindex();
            $rmodel = new Resource();
            $rs = $rmodel->getindex();
            foreach($this->vo_ids as $vo_id) {
                $resource_list = array();
                if(isset($resource_ownerships[$vo_id])) {
                    foreach($resource_ownerships[$vo_id] as $resource_ownership) {
                        $resource = $rs[$resource_ownership->resource_id][0];
                        $resource_list[$resource->id] = $resource;
                    }
                }
                $this->view->resource_ownerships[$vo_id] = $resource_list;
            }
        }

        $this->view->vos = array();
        foreach($this->vo_ids as $vo_id) {
            $vo = $vos[$vo_id][0];

            //lookup support center
            $vo->sc = $scs[$vo->sc_id][0];
            $this->view->vos[$vo_id] = $vo;
        }

        $this->setpagetitle(self::default_title());
    }

}
