<?

class WizardaccountController extends WizardController
{
    public function breads() { return array("rsv", "wizard"); }
    public static function default_title() { return "Accounting"; }
    public static function default_url($query) { return ""; }

    public function load()
    {
        parent::load();
        
        $resource_model = new Resource();
        $resources = $resource_model->getindex();

        $dirty_type = $_REQUEST["account_type"];
        $urlbase = "";
        $legend = false;
        switch($dirty_type) 
        {
        case "cumulative_hours":
            $urlbase = "http://t2.unl.edu/gratia/cumulative_graphs/vo_success_cumulative_smry";
            $sub_title = "Cumulative Hours";
            $ylabel = "Hours";
            break;
        case "daily_hours_byvo":
            $urlbase = "http://t2.unl.edu/gratia/bar_graphs/vo_hours_bar_smry";
            $sub_title = "Daily Hours (Grouped by VO)";
            $legend = true;
            $ylabel = "Hours";
            break;
        case "daily_hours_byusername":
            $urlbase = "http://t2.unl.edu/gratia/bar_graphs/dn_hours_bar";
            $sub_title = "Daily Hours (Grouped by Username)";
            $legend = true;
            $ylabel = "Hours";
            break;
        case "job_count_byvo":
            $urlbase = "http://t2.unl.edu/gratia/bar_graphs/vo_job_cnt";
            $sub_title = "Job Count (Grouped by VO)";
            $legend = true;
            $ylabel = "Number of Jobs";
            break;
        case "wall_success":
            $urlbase = "http://t2.unl.edu/gratia/bar_graphs/vo_wall_success_rate";
            $sub_title = "VO 'wall success' rate";
            $ylabel = "'Wall Sucess' Rate";
            break;
        case "cpu_efficiency":
            $urlbase = "http://t2.unl.edu/gratia/bar_graphs/facility_cpu_efficiency";
            $sub_title = "CPU Efficiency";
            $ylabel = "Efficiency";
            break;
        default:
            throw new exception("unknown account_type");
        }
        $this->view->sub_title = $sub_title;        

        $this->load_daterangequery();
        $start_time = date("Y-m-d h:i:s", $this->view->start_time);
        $end_time = date("Y-m-d h:i:s", $this->view->end_time);
    
        $this->view->graph_urls = array();
        foreach($this->resource_ids as $resource_id) {
            $resource_info = $resources[$resource_id][0];
            $resource_name = $resource_info->name;

            $url = $urlbase."?facility=$resource_name&title=&ylabel=$ylabel&starttime=$start_time&endtime=$end_time";
            if(!$legend) {
                $url .= "&legend=False";
            }
            $this->view->graph_urls[$resource_name] = $url;
        }

        //$urlbase = "http://t2.unl.edu/gratia/cumulative_graphs/vo_success_cumulative_smry?facility=AGLT2&starttime=2009-01-28%2000:00:00&endtime=2009-02-11%2000:00:00"; 

/*
        $cache_xml = file_get_contents("http://is-dev.grid.iu.edu/gip-validator/index.xml");
        $this->view->rawxml = $cache_xml; //for xml view
        $cache = new SimpleXMLElement($cache_xml);

        $this->view->resources = array();

        foreach($this->resource_ids as $resource_id) {
            $resource_info = $resources[$resource_id][0];
            $resource_name = $resource_info->name;

            //search for this resource name
            foreach($cache->Site as $site) {
                $attrs = $site->attributes();
                if($resource_name == $attrs["name"]) {
                    $rec = array(
                        "name"=>$resource_name,
                        "test"=>$attrs["test"], 
                        "result"=>$attrs["result"], 
                        "path"=>$attr["path"]."#".$resource_name);
                    $this->view->resources[$resource_id] = $rec;
                    break;
                }
            }
        }
*/
        $this->setpagetitle(self::default_title());
    }
}
