<?
class WizardsummaryController extends WizardController
{
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
                if($rg->id == $resource_id) {
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
                elog("Failed to find group id for resource = ".$resource_id);
            }
        }
        $this->view->resource_groups = $groups;

        ///////////////////////////////////////////////////////////////////////////////////////////
        //pull other optional stuff
        if(isset($_REQUEST["summary_attrs_showservice"])) {
            $servicetype_model = new Service();
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
        if(isset($_REQUEST["summary_attrs_showwlcg"])) {
            $model = new ResourceWLCG();
            $this->view->resource_wlcg = $model->getindex();
            //append bdii link
            foreach($this->view->resource_wlcg as $resource_id=>$info) {
                //get resource group name
                $rgroup_id = $resources[$resource_id][0]->resource_group_id;
                $rgroup = $this->view->resourcegroups[$rgroup_id][0];
                $rgname = $rgroup->name;
                if ($rgroup->grid_type == "OSG") {
                  $info[0]->ldap_url = "ldap://is.grid.iu.edu:2180/mds-vo-name=$rgname,o=grid";
                }
                else {
                  $info[0]->ldap_url = "ldap://is-itb.grid.iu.edu:2180/mds-vo-name=$rgname,o=grid";
                }
            }
        }
        if(isset($_REQUEST["summary_attrs_showenv"])) {
            $envmodel = new ResourceEnv();
            $details = $envmodel->getindex(array("metric_id"=>0));
            $this->view->envs = array();
            foreach($this->resource_ids as $resource_id) {
                $rec = @$details[$resource_id][0];
                $env = null;
                if($rec !== null) {
                    try {
                        $env = new SimpleXMLElement($rec->xml);
                    } catch (exception $e) {
                        elog((string)$e);
                        elog($rec->xml);
                    }
                }
                $this->view->envs[$resource_id] = $env;
            }
        }
        if(isset($_REQUEST["summary_attrs_showcontact"])) {
            $this->view->contacts = array();
            $cmodel = new ResourceContact();
            $contacts = $cmodel->getindex();
            //group by contact_type_id
            foreach($this->resource_ids as $resource_id) {
                $types = array();
                if(isset($contacts[$resource_id])) {
                    foreach($contacts[$resource_id] as $contact) {
                        if(!isset($types[$contact->contact_type])) {
                            $types[$contact->contact_type] = array();
                        }
                        $types[$contact->contact_type][] = $contact;
                    }
                    $this->view->contacts[$resource_id] = $types;
                }
            }
        }

        if(isset($_REQUEST["summary_attrs_showfqdn"])) {
            $amodel = new ResourceAlias();
            $this->view->aliases = $amodel->getindex();
        }


        $this->setpagetitle(self::default_title());
    }

    // Added by agopu for VORS type CSV generation - as required by Rob and management
    public function legacyvorscsvAction()
    {
        $resource_ids = $this->process_resourcelist();

        $model = new ResourceGroup();
        $resourcegroups = $model->get();
        $resourcegroup_gridtype = array();
        foreach($resourcegroups as $key => $value) {
          $resourcegroup_gridtype[$resourcegroups[$key]->id] =  $resourcegroups[$key]->grid_type;
        }

        $servicetype_model = new Service();
        $servicetypes = $servicetype_model->getindex();
        $servicetype_name = array();
        $servicetype_port = array();
        foreach ($servicetypes as $key => $value) {
          $pair = $servicetypes[$key][0];
          if ($pair->name == "CE") {
            $servicetype_name[$pair->id] = "compute";
          }
          else {
            $servicetype_name[$pair->id] = "storage";
          }
          $servicetype_port[$pair->id] = $pair->port;
        }
        
        $resourceservice_model = new ServiceByResourceID();
        $resource_services = $resourceservice_model->getindex();
        $resourceservice_map = array ();
        foreach ($resource_services as $key => $value) {
          $pair = $resource_services[$key][0];
          // Don't care about hidden or central  resources - VORS did not
          if (($pair->central == 1) || ($pair->hidden == 1)) {
            next;
          }
          if (isset($pair->endpoint_override)) {
            $resourceservice_map[$pair->resource_id][$pair->service_id]['URI'] = $pair->endpoint_override;
          }
          else {
            $resourceservice_map[$pair->resource_id][$pair->service_id]['URI'] = "";
          }
        }

        $model = new LatestResourceStatus();
        $resource_status = $model->getgroupby("resource_id");
        $resourcestatus_map = array();
        $resourcestatusdate_map = array();
        foreach ($resource_status as $key => $value) {
          $pair = $resource_status[$key][0];
          $resourcestatusdate_map[$pair->resource_id] = $pair->timestamp;
          if (($pair->status_id == 1) || ($pair->status_id == 2)) {
            $resourcestatus_map[$pair->resource_id] = "PASS";
          } else {
            $resourcestatus_map[$pair->resource_id] = "FAIL";
          }
        }
        $downtime_model = new Downtime();
        $downtimes = $downtime_model->getindex(array("start_time"=>time(), "end_time"=>time()));
        $downtime_map = array();
        foreach ($downtimes as $key => $value) {
          $resourcestatus_map[$value[0]->resource_id] = "MAINT";
        }
        
        $model = new Resource();
        $resources = $model->getindex();

        header("Content-type: text/plain");
        echo "#VORS text interface EMULATION on MyOSG (grid = All, VO = all, res = 0)\n".
          "#columns=ID,Name,Gatekeeper,Type,Grid,Status,Last Test Date\n";

        //print_r ($resourceservice_map);
        foreach($resourceservice_map as $resource_id => $service_map) {

          $resource = $resources[$resource_id][0];

          foreach($service_map as $service_id => $service_value) {
            $uri = $service_value->URI;
            if ($uri == "") {
              $uri = $resource->fqdn . ":" . $servicetype_port[$service_id];
            }

            // Dump output finally
            echo $resource->id . ",".
              $resource->name. ",". 
              $uri . ",". 
              $servicetype_name[$service_id] . ",".
              $resourcegroup_gridtype[$resource->resource_group_id] . ",".
              $resourcestatus_map[$resource_id].",".
              date('Y-m-d H:i:s', $resourcestatusdate_map[$resource_id]).
              "\n";
          }
        }
        $this->render("none", null, true);
    }


    /* 
     // Not necessary since EGEE group will use XML instead. -agopu
     public function wlcgldaplistAction()
     {
        $resource_ids = $this->process_resourcelist();
        header("Content-type: text/plain");

        $model = new Resource();
        $resources = $model->getindex();

        foreach($resource_ids as $resource_id) {
            $resource = $resources[$resource_id][0];
            $name = $resource->name;
            echo "$name ldap://is.grid.iu.edu:2180/mds-vo-name=$name,o=grid\n";
        }
        $this->render("none", null, true);
    }
    */
}
