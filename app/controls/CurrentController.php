<?

class CurrentController extends Zend_Controller_Action 
{ 
    //respond to various ajax requests..
    public function proxyAction() 
    { 
        $dirty_feed = $_REQUEST["feed"];

        if($dirty_feed == "resource") {
            $this->output_resource();
        } else if($dirty_feed == "detail") {
            $this->output_detail();
        } else if($dirty_feed == "gmap") {
            $this->output_gmap();
        } else if($dirty_feed == "glue") {
            $this->output_glue();
        } else if($dirty_feed == "vo") {
            $this->output_vo();
        } else if($dirty_feed == "vogroup") {
            $this->output_vogroup();
        }
    }

    ///////////////////////////////////////////////////////////////////////////

    private function output_vogroup()
    {
        $vo_model = new VO();
        $this->view->vos = $vo_model->pullMemberVOs();
        $this->render("vogroup");
    }


    private function output_vo()
    {
        $servicetype = null;
        if(isset($_REQUEST["servicetype"])) {
            $dirty_servicetype = $_REQUEST["servicetype"];
            if(Zend_Validate::is($dirty_servicetype, 'Int')) {
                $servicetype = $dirty_servicetype;
            }
        }

        $vo_model = new VO();
        $this->view->vos = $vo_model->fetchAll();

        $resource_model = new Resource();
        $resources = $resource_model->fetchAll($servicetype);

        //load resource information
        $this->view->rows = array();
        foreach($resources as $resource) {
            $row = array();
            $row["resource_id"] = $resource->id;
            $row["resource_name"] = $resource->name;
            $members = $vo_model->pullMemberVOs($resource->id);
            if(count($members) > 0) {
                foreach($this->view->vos as $vo) {
                    //search for the vo_id
                    $found = false;
                    foreach($members as $member) {
                        if($member->vo_id == $vo->vo_id) {
                            $row[$vo->short_name] = 1; //meaning - do support
                            $found = true;
                            break;
                        }
                    }
                    if(!$found) {
                        $row[$vo->short_name] = 0; //meaning - no support
                    }
                }
            } else {
                foreach($this->view->vos as $vo) {
                    $row[$vo->short_name] = 2;//meaning - N/A
                }
            }
            $this->view->rows[] = $row;
        }

        $this->render("vo");
    }

    private function output_glue()
    {
        if(isset($_REQUEST["name"])) {
            $dirty_name = $_REQUEST["name"];
            //TODO - Do Better validation (alphanumeric, _ and - only)
            $validator = new Zend_Validate_StringLength(3, 30);
            if ($validator->isValid($dirty_name))
            {
                $name = $dirty_name;
                echo "<pre>";
                passthru("curl \"http://is.grid.iu.edu/cgi-bin/show_source_data?which=$name&source=served\"");
                echo "</pre>";
            }
        }
        $this->render("none");
    }

    private function output_gmap()
    {
        header("Content-Type: text/javascript");

        $resource = new Resource();
        $this->view->resource_rowset = $resource->fetchAll();

        $this->view->status = array();
        $probeinfo = new ProbeInfo();
        foreach($this->view->resource_rowset as $resource) {
            $filename = config()->cache_filename_latest_overall.".".$resource->id;
            if(file_exists($filename)) {
                $info = unserialize(file_get_contents($filename));
                $this->view->status[$resource->id] = $info["status"];
            } else {
                $this->view->status[$resource->id] = "UNKNOWN";
            }
        }

        //lookup resource geocodes
        $resource_geo = new ResourceGeo();
        $resource_geo_rowset = $resource_geo->fetchAll();
        $this->view->resource_geo = array();
        foreach($resource_geo_rowset as $resource_geo_row) {
            $this->view->resource_geo[$resource_geo_row->resource_id] = $resource_geo_row;
        }
        
        $this->render("gmap");
    }

    private function output_resource()
    {
        $servicetype = null;
        if(isset($_REQUEST["servicetype"])) {
            $dirty_servicetype = $_REQUEST["servicetype"];
            if(Zend_Validate::is($dirty_servicetype, 'Int')) {
                $servicetype = $dirty_servicetype;
            }
        }

        $this->view->resource_service_types = new ResourceServiceTypes();

        $resource_model = new Resource();
        $resources = $resource_model->fetchAll($servicetype);

        //load resource information
        $this->view->resources = array();
        foreach($resources as $resource) {
            $filename = config()->cache_filename_latest_overall.".".$resource->id;
            if(file_exists($filename)) {
                $info = unserialize(file_get_contents($filename));
                $info["info"] = $resource;
                $this->view->resources[$resource->id] = $info;
            } else {
                //TODO - latest info doesn't exist for this resource yet... should I create a dummy?
            }
        }

        $this->render("resource");
    }

    private function output_detail()
    {
        $dirty_resource_id = $_REQUEST["id"]; 
        if(Zend_Validate::is($dirty_resource_id, 'Int')) {
            $resource_id = $dirty_resource_id;

            $resource_service_types = new ResourceServiceTypes();
            $this->view->service_types = $resource_service_types->getServiceTypes($resource_id);

            //read overall status cache
            $filename = config()->cache_filename_latest_overall.".".$resource_id;
            $info = unserialize(file_get_contents($filename));
            $this->view->overall_status = $info;

            //read latest metric set cache
            $filename = config()->cache_filename_latest_metrics.".".$resource_id;
            $info = unserialize(file_get_contents($filename));
            $metrics = $info;
            $probe_info = new ProbeInfo();
            $this->view->metrics = array();
            foreach($probe_info->getAllProbeInfo() as $info) {
                //look for the latest metric for this probe
                $the_metric = null;
                if(isset($metrics[$info->id])) $the_metric = $metrics[$info->id];

                //create the metric object with the metric info and data
                $mobject = new MetricAndProbeInfo($info, $probe_info->getCriticalServices($info->id));
                $mobject->setMetric($the_metric);
                $this->view->metrics[] = $mobject;
            }

            //$probeinfo = new ProbeInfo();
            //$overall_status = new OverallStatus($resource_id, $probeinfo);
            //$this->view->overall_status = $overall_status;
            //$lastinfo = $overall_status->getLastInfo();
            //$before = $lastinfo->timestamp; //TODO - should I use effective_timestamp instead?
            //$this->view->current_status = new CurrentStatus($resource_id, $before);

            $this->render("detail");
        }
    }
} 
