<?
class VoactivationController extends VoController
{
    public static function default_title() { return "Virtual Organization Activation Status"; }
    public static function default_url($query) { return ""; }

    private $active_vos = array();
    private $disabled_vos = array();
    private $current = array();

    public function load()
    {
        parent::load();
        $this->load_daterangequery();

        $model = new VirtualOrganization();
        $vos = $model->getindex();
        $logmodel = new OIMLog();

        //grab current status as initial status
        foreach($vos as $vo_id=>$vo) {
            $vo = $vo[0];
            $this->current[$vo_id] = array((int)$vo->active, (int)$vo->disable);
        }

        //get all logs for VOModel (don't worry.. for now VO doesn't get updated that often)
        $logs = $logmodel->getByModel("edu.iu.grid.oim.model.db.VOModel");

        //apply update upto $end_time
        foreach($logs as $log) {
            $timestamp = (int)$log->unix_timestamp;
            if($timestamp > $this->view->end_time) {
                $this->update($log->xml);
            }
        }

        //now analyze current status
        foreach($this->current as $vo_id=>$cur) {
            $this->analyze($vo_id, $cur);
        }

        //now analyze the log between $start and $end
        foreach($logs as $log) {
            $timestamp = (int)$log->unix_timestamp;
            if($timestamp <= $this->view->end_time && $timestamp >= $this->view->start_time) {
                if($this->update($log->xml)) {
                    //each time update occurs, re-analyze
                    $this->analyze($vo_id, $cur);
                }
            }
        }

        //finally, construct various lists
        $this->view->active_once_vos = array();
        $this->view->never_active_enabled_vos = array();
        $this->view->disabled_once_vos = array();
        foreach($vos as $vo_id=>$vo) {
            $vo = $vo[0];
            if(in_array($vo_id, $this->active_vos)) {
                //has been active at least once
                $this->view->active_once_vos[] = $vo;
            } else {
                if(!in_array($vo_id, $this->disabled_vos)) {
                    //never active, and never disabled
                    $this->view->never_active_enabled_vos[] = $vo;
                } else {
                    //never active, and has been disabled at least once
                    $this->view->disabled_once_vos[] = $vo;
                }
            }
        }

        $this->setpagetitle(self::default_title());
    }

    private function analyze($void, $flags)
    {
        if($flags[0] == 1 and $flags[1] == 0) {
            if(!in_array($void, $this->active_vos)) {
                $this->active_vos[] = $void;
            }
        } else if($flags[1] == 1) {
            if(!in_array($void, $this->disabled_vos)) {
                $this->disabled_vos[] = $void;
            }
        }
    }

    //return true if the flag(s) is actually updated
    private function update($xml) 
    {
        $update = false;

        $xml = new SimpleXMLElement($xml);
        $vo_id = (int)$xml->Keys[0]->Key[0]->Value[0];

        $cur = $this->current[$vo_id];
        $items = $xml->xpath("/Log/Fields/Field/Name[.='active']/../OldValue");
        if($items != null) {
            $cur[0] = ($items[0] == "true" ? 1 : 0);
            $update = true;
        }
        $items = $xml->xpath("/Log/Fields/Field/Name[.='disable']/../OldValue");
        if($items != null) {
            $cur[1] = ($items[0] == "true" ? 1 : 0);
            $update = true;
        }
        $this->current[$vo_id] = $cur;

        return $update;
    }
}
