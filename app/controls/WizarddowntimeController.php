<?

class WizarddowntimeController extends WizardController
{
    public function breads() { return array("rsv", "wizard"); }
    public static function default_title() { return "Downtime Information"; }
    public static function default_url($query) { return ""; }

    public function load()
    {
        parent::load();
        $this->view->resource_ids = $this->resource_ids;

        $model = new Downtime();
        $downtimes = $model->getindex();

        $past = array();
        $current = array();
        $future = array();
        
        foreach($downtimes as $id=>$downtime) {
            if($downtime[0]->unix_end_time < time()) {
                if(isset($_REQUEST["downtime_attrs_showpast"])) {
                    $past[$id] = $downtime;
                } 
            } else if($downtime[0]->unix_start_time > time()) {
                $future[$id] = $downtime;
            } else {
                $current[$id] = $downtime;
            }
        }

        $this->view->past_downtimes = $this->formatInfo($past);
        $this->view->current_downtimes = $this->formatInfo($current);
        $this->view->future_downtimes = $this->formatInfo($future);

        $this->setpagetitle(self::default_title());
    }

    function formatInfo($downtime_recs)
    {
        if($downtime_recs === null) {
            return array();
        }

        $downtimes = array();
        $resource_model = new Resource();
        $resources = $resource_model->getindex();

        $downtime_service_model = new DowntimeService();
        $downtime_services = $downtime_service_model->get();

        $model = new ServiceTypes();
        $service_info = $model->getindex();

        $model = new DowntimeClass();
        $downtime_class = $model->getindex();

        $model = new DowntimeSeverity();
        $downtime_severity = $model->getindex();
    
        $model = new DN();      
        $dns = $model->getindex();

        foreach($downtime_recs as $downtime_a)
        {
            $downtime = $downtime_a[0];
            if(in_array($downtime->resource_id, $this->resource_ids)) {
                //only show event that we have pulled resource for
                $resource = $resources[$downtime->resource_id];
                $resource_name = $resource[0]->name;
                if($resource_name !== null) {

                    $start = date(config()->date_format_full, $downtime->unix_start_time);
                    $end = date(config()->date_format_full, $downtime->unix_end_time);

                    //get affected services
                    $affected_services = array();
                    foreach($downtime_services as $service) {
                        if($service->resource_downtime_id == $downtime->id) {
                            $info = $service_info[$service->service_id][0];
                            $affected_services[] = $info->description;
                        }
                    }

                    $desc = $downtime->downtime_summary;
                    //$desc = str_replace(array("\n", "\r"), "", $desc);

                    $severity = $downtime_severity[$downtime->downtime_severity_id][0]->name;
                    $class = $downtime_class[$downtime->downtime_class_id][0]->name;
                    $dn = $dns[$downtime->dn_id][0]->dn_string;

                    $downtimes[] = array("id"=>$downtime->id, 
                        "name"=>$resource_name,
                        "resource_id"=>$downtime->resource_id,
                        "desc"=>$desc,
                        "severity"=>$severity,
                        "class"=>$class,
                        "services"=>$affected_services,
                        "start_time"=>$start,
                        "dn"=>$dn,
                        "end_time"=>$end
                    );
                }
            }
        }
        return $downtimes;
    }
}
