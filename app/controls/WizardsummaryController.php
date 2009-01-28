<?
class WizardsummaryController extends WizardController
{
    public function breads() { return array("rsv", "wizard"); }
    public static function default_title() { return "Resource Summary"; }
    public static function default_url($query) { return ""; }

    public function indexAction()
    {
        $this->load();

        //pull needed info
        $gridtype_model = new GridTypes();
        $this->view->gridtypes = $gridtype_model->getindex();
        $servicetype_model = new ServiceTypes();
        $this->view->servicetypes = $servicetype_model->getindex();
        $resourceservice_model = new ServiceByResourceID();
        $this->view->resource_services = $resourceservice_model->getindex();
        $model = new ResourceGroup();
        $this->view->resourcegroups = $model->getgroupby("resource_group_id");
        $model = new ResourceByGroupID();
        $resourcegroups = $model->get();
        $model = new Resource();
        $resources = $model->getindex();
        $model = new LatestResourceStatus();
        $this->view->resource_status = $model->getgroupby("resource_id");
        $downtime_model = new Downtime();
        $this->view->downtime = $downtime_model->getindex(array("start_time"=>time(), "end_time"=>time()));

        //pull other optional stuff
        //vo membership
        if(isset($_REQUEST["summary_attrs_showvomembership"])) {
            $cache_filename = config()->vomatrix_xml_cache;
            $cache_xml = file_get_contents($cache_filename);
            $vocache = new SimpleXMLElement($cache_xml);
            $resourcegrouped = $vocache->ResourceGrouped[0];

            $this->view->vos_supported = array();
            foreach($resourcegrouped as $resource) {
                $attr = $resource->attributes();
                $id = (int)$attr->id[0];
                $this->view->vos_supported[$id] = $resource->Members[0];
            }
        }

        //VO ownership 
        if(isset($_REQUEST["summary_attrs_showvoownership"])) {
            $model = new ResourceOwnership();
            $this->view->resource_ownerships = $model->getindex();
        }

        //group resources by resource groups
        $groups = array();
        foreach($this->resource_ids as $resource_id) {
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
        $this->view->resource_groups = $groups;

        $this->setpagetitle(self::default_title());
    }
}
