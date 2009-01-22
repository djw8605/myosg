<?
class wizard_summary implements wizard_controller
{
    public function pagetitle()
    {
        return "Summary";
    }

    public function __construct($view, $resource_ids)
    {
        //pull needed info
        $gridtype_model = new GridTypes();
        $view->gridtypes = $gridtype_model->getindex();
        $servicetype_model = new ServiceTypes();
        $view->servicetypes = $servicetype_model->getindex();
        $resourceservice_model = new ServiceByResourceID();
        $view->resource_services = $resourceservice_model->getindex();
        $model = new ResourceGroup();
        $view->resourcegroups = $model->getgroupby("resource_group_id");
        $model = new ResourceByGroupID();
        $resourcegroups = $model->get();
        $model = new Resource();
        $resources = $model->getindex();
        $model = new LatestResourceStatus();
        $view->resource_status = $model->getgroupby("resource_id");

        //group resources by resource groups
        $groups = array();
        foreach($resource_ids as $resource_id) {
            $resource = $resources[$resource_id][0];
            //find resource group_id
            foreach($resourcegroups as $rg) {
                if($rg->resource_id == $resource_id) {
                    $group_id = $rg->resource_group_id;
                    break;
                }
            }
            if(!isset($groups[$group_id])) {
                $groups[$group_id] = array();
            }
            $groups[$group_id][$resource->id] = $resource;
        }
        $view->resource_groups = $groups;
    }
}
