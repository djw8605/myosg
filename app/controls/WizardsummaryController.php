<?
class WizardsummaryController extends WizardController
{
    //public function breads() { return array("wizard"); }
    public static function default_title() { return "Resource Summary"; }
    public static function default_url($query) { return ""; }

    public function load()
    {
        parent::load();
        
        //pull common info
        $gridtype_model = new GridTypes();
        $this->view->gridtypes = $gridtype_model->getindex();
        $model = new ResourceGroup();
        $this->view->resourcegroups = $model->getgroupby("id");
        $model = new ResourceByGroupID();
        $resourcegroups = $model->get();
        $model = new Resource();
        $resources = $model->getindex();

        //group resources by resource groups
        $groups = array();
        foreach($this->resource_ids as $resource_id) {
            $resource = $resources[$resource_id][0];
            //find resource group_id
            $found = false;
            foreach($resourcegroups as $rg) {
                if($rg->resource_id == $resource_id) {
                    $group_id = $rg->resource_group_id;
                    $found = true;
                    break;
                }
            }
            if($found) {
                if(!isset($groups[$group_id])) {
                    $groups[$group_id] = array();
                }
                $groups[$group_id][$resource->id] = $resource;
            } else {
                dlog("Failed to find group id for resource = ".$resource_id);
            }
        }
        $this->view->resource_groups = $groups;

        ///////////////////////////////////////////////////////////////////////////////////////////
        //pull other optional stuff
        if(isset($_REQUEST["summary_attrs_showservice"])) {
            $servicetype_model = new ServiceTypes();
            $this->view->servicetypes = $servicetype_model->getindex();
            $resourceservice_model = new ServiceByResourceID();
            $this->view->resource_services = $resourceservice_model->getindex();
        }
        if(isset($_REQUEST["summary_attrs_showrsvstatus"])) {
            $model = new LatestResourceStatus();
            $this->view->resource_status = $model->getgroupby("resource_id");
            $downtime_model = new Downtime();
            $this->view->downtime = $downtime_model->getindex(array("start_time"=>time(), "end_time"=>time()));
        }
        if(isset($_REQUEST["summary_attrs_showgipstatus"])) {
            $model = new LDIF();
            $this->view->gipstatus = $model->getValidationSummary();
        }
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
        if(isset($_REQUEST["summary_attrs_showvoownership"])) {
            $model = new ResourceOwnership();
            $this->view->resource_ownerships = $model->getindex();
        }

        $this->setpagetitle(self::default_title());
    }

}
