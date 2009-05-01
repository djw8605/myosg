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

        //pull current downtimes
        $downtime_model = new Downtime();
        $today_start = time()/(3600*24) * (3600*24);
        $today_end = time()/(3600*24) * (3600*24) + 3600*24;
        $this->view->current_downtimes = $this->formatInfo($downtime_model->getCurrentDowntimes($today_start, $today_end));

        //pull future downtimes
        $this->view->future_downtimes = $this->formatInfo($downtime_model->getFutureDowntimes(time()));

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
                    $desc = str_replace(array("\n", "\r"), "", $desc);

                    $downtimes[] = array("id"=>$downtime->resource_id, 
                        "name"=>$resource_name,
                        "desc"=>$desc,
                        "services"=>$affected_services,
                        "start_time"=>$start,
                        "end_time"=>$end
                    );
                }
            }
        }
        return $downtimes;
    }
}
