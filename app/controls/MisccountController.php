<?
class MisccountController extends MiscController
{
    public static function default_title() { return "Resource Service Counts"; }
    public static function default_url($query) { return ""; }

    public function load()
    {
        parent::load();

        $model = new ResourceGroup();
        $this->view->resource_groups_by_gridtype = $model->getgroupby("osg_grid_type_id");

        $model = new Resource();
        $this->view->resources_by_resource_group = $model->getgroupby("resource_group_id");

        $model = new GridTypes();
        $this->view->grid_types = $model->getindex();

        $model = new Service();
        $this->view->services = $model->getindex();

        $model = new ResourceServices();
        $this->view->services_by_resource = $model->getgroupby("resource_id");

        $this->view->counts = array();

        //filter service groups
        $this->view->service_groups = array();
        $model = new ServiceGroup();
        $service_groups = $model->getindex();
        foreach($service_groups as $id => $service_group) {
            if(isset($_REQUEST["count_sg_".$id])) {
                $this->view->service_groups[$id] = $service_group;
            }
        }

        $this->view->resource_counts = array();

        //for each grid type
        foreach($this->view->resource_groups_by_gridtype as $grid_type_id => $resource_groups) {
            $resource_counts = 0;

            //for each resource groups,
            foreach($resource_groups as $resource_group)  {
                $resources = $this->view->resources_by_resource_group[$resource_group->id];

                //for each resource
                foreach($resources as $resource)  {
                
                    //apply filter
                    if(isset($_REQUEST["count_active"])) {
                        if($resource->active == 0) continue;
                    }
                    if(isset($_REQUEST["count_enabled"])) {
                        if($resource->disable == 1) continue;
                    }
                    $resource_counts++;

                    //pull counter for current grid type
                    $services = $this->services_by_resource[$resource->id];
                    if(!isset($this->view->counts[$grid_type_id])) {
                        $this->view->counts[$grid_type_id] = array();
                    }
                    $count_service = $this->view->counts[$grid_type_id];
                    
                    //for each services
                    foreach($this->view->services_by_resource[$resource->id] as $service) {
                        if(!isset($count_service[$service->id])) {
                            $count_service[$service->id] = 0;
                        }
                        $count_service[$service->id] = $count_service[$service->id] + 1;
                    }
                    $this->view->counts[$grid_type_id] = $count_service;
                }
            }
            $this->view->resource_counts[$grid_type_id] = $resource_counts;
        }

        $this->setpagetitle(self::default_title());
    }
}
