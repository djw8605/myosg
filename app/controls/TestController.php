<?php
/*#################################################################################################

Copyright 2009 The Trustees of Indiana University

Licensed under the Apache License, Version 2.0 (the "License"); you may not use this file except in
compliance with the License. You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software distributed under the License
is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or
implied. See the License for the specific language governing permissions and limitations under the
License.

#################################################################################################*/

class TestController extends Zend_Controller_Action
{ 
    public static function default_title() { return "Test"; }
    public static function default_url($query) { return ""; }

/*
    function searchAction()
    {
        //search for resources that are not reported on gratia
        $model = new Resource(array("active"=>1, "disable"=>0));
        $resources = $model->getindex();

        $model = new ResourceGroup(array("osg_grid_type_id"=>1));
        $groups = $model->getindex();

        $model = new ResourceAlias();
        $alias = $model->getindex();

        $model = new ResourceServices();
        $services = $model->get();

        $reported_s = array(
'arraytibi.sbgrid.org',
'antaeus.hpcc.ttu.edu',
'athena.rit.albany.edu',
'atlas.bu.edu',
'atlas.dpcc.uta.edu',
'atlas07.cs.wisc.edu',
'ce01.cmsaf.mit.edu',
'cit-gatekeeper.ultralight.org',
'cit-gatekeeper2.ultralight.org',
'cit-se.ultralight.org',
'cit-se2.ultralight.org',
'cms-xen19.fnal.gov',
'cmsgrid01.hep.wisc.edu',
'cmsgrid02.hep.wisc.edu',
'cmsosgce.fnal.gov',
'cmsosgce2.fnal.gov',
'cmsosgce4.fnal.gov',
'cmssrm.fnal.gov',
'cmssrm.hep.wisc.edu',
'd0cabosg1.fnal.gov',
'd0cabosg2.fnal.gov',
'dcache.rcac.purdue.edu',
'dcache07.unl.edu',
'dcsrm.usatlas.bnl.gov',
'fcdfosg1.fnal.gov',
'fcdfosg2.fnal.gov',
'fcdfosg3.fnal.gov',
'fcdfosg4.fnal.gov',
'fermigridosg1.fnal.gov',
'ff-grid.pki.nebraska.edu',
'ff-grid.unl.edu',
'ff-srm.unl.edu:8443',
'fndca1.fnal.gov',
'fnpcfg1.fnal.gov',
'fnpcfg2.fnal.gov',
'fnpcosg1.fnal.gov',
'gaia.physics.mcgill.ca',
'gate01.aglt2.org',
'gate02.grid.umich.edu',
'gk.ihepa.ufl.edu',
'gk01.atlas-swt2.org',
'gk03.atlas-swt2.org',
'gk04.swt2.uta.edu',
'gk05.swt2.uta.edu',
'gpn-husker.unl.edu',
'grid.rc.rit.edu',
'grid1.oscer.ou.edu',
'gridftp.hepgrid.uerj.br',
'gridgk01.racf.bnl.gov',
'gridgk02.racf.bnl.gov',
'head01.aglt2.org',
'hepcms-0.umd.edu',
'hepgums.colorado.edu',
'heposg01.colorado.edu',
'hepse01.colorado.edu',
'higgs08.cs.wisc.edu',
'iogw1.hpc.ufl.edu',
'iut2-dc1.iu.edu',
'iut2-grid6.iu.edu',
'lepton.rcac.purdue.edu',
'm3.alliance.unm.edu',
'm3.quarry.teragrid.iu.edu',
'magic.cse.buffalo.edu',
'mstr1.cluster.phy.uic.edu',
'nys1.cac.cornell.edu',
'oseen.uprm.edu',
'oseen.uprm.edu.',
'osg-ce.grid.uj.ac.za',
'osg-ce.sprace.org.br',
'osg-east.hms.harvard.edu',
'osg-edu.cs.wisc.edu',
'osg-gate.rice.edu',
'osg-gums.clemson.edu',
'osg-gw-2.t2.ucsd.edu',
'osg-gw-4.t2.ucsd.edu',
'osg-gw.clemson.edu',
'osg-nemo-ce.phys.uwm.edu',
'osg-se.sprace.org.br',
'osg.rcac.purdue.edu',
'osgce.cs.clemson.edu',
'osgce.hepgrid.uerj.br',
'osgce64.hepgrid.uerj.br',
'osgserv01.slac.stanford.edu',
'osgserv04.slac.stanford.edu',
'osgx0.hep.uiuc.edu',
'osgx1.hep.uiuc.edu',
'ouhep0.nhn.ou.edu',
'ouhep00.nhn.ou.edu',
'ouhep1.nhn.ou.edu',
'pdsfgrid.nersc.gov',
'pdsfsrm.nersc.gov',
'pf-grid.unl.edu',
'pg.ihepa.ufl.edu',
'quiver.mrl.ucsb.edu',
'red-srm1.unl.edu',
'red.unl.edu',
'rocks.ds.geneseo.edu',
'ruhex-osgce.rutgers.edu',
'saxon.hosted.ats.ucla.edu',
'se-dcache.hepgrid.uerj.br',
'se01.cmsaf.mit.edu',
'se1.accre.vanderbilt.edu',
'se1.accre.vanderbilt.edu:6288',
'sigmorgh.hpcc.ttu.edu:49443',
'smufarm.physics.smu.edu',
'srm-3.t2.ucsd.edu',
'srm.ihepa.ufl.edu',
'srm.unl.edu',
'stargrid02.rcf.bnl.gov',
'tier2-01.ochep.ou.edu',
'tier2-osg.uchicago.edu',
'top.ucr.edu',
'tuscany.med.harvard.edu',
'u2-grid.ccr.buffalo.edu',
'uct2-dc1.uchicago.edu',
'uct2-grid6.uchicago.edu',
'uscms1.fltech-grid3.fit.edu',
'vampire.accre.vanderbilt.edu',
'vdgateway.vcell.uchc.edu'
);
        echo "<h1>Resources that are not reporting RSV status (or alias/override is not correct)</h1>";
        echo "filtered by active = 1, disable = 0, and in prod grid type<br/>";

        $count = 0;
        $total = 0;

        foreach($resources as $id=>$resource) {
            $resource = $resource[0];

            //make sure it's in prod resource group
            if(!isset($groups[$resource->resource_group_id])) continue;
            $total++;

            //search by resource name
            $found = false;
            foreach($reported_s as $reported) {
                if($resource->fqdn == $reported) {
                    $found = true;
                    break;
                }
            }

            //search by alias
            if(!$found) {
                //iterate through this resource's alias
                if(isset($alias[$resource->id])) {
                    foreach($alias[$resource->id] as $a) {
                        foreach($reported_s as $reported) {
                            if($a->resource_alias == $reported) {
                                echo "(alias match)";
                                $found = true;
                                break;
                            }
                        }
                    }
                }
            }

            //search by service override
            if(!$found) {
                //iterate through service override
                foreach($services as $service) {
                    if($service->resource_id == $resource->id) {
                        foreach($reported_s as $reported) {
                            if($service->endpoint_override == $reported) {
                                echo "(override match)";
                                $found = true;
                                break;
                            }
                        }
                    }
                }
            }


            if(!$found) {
                echo "$resource->name($resource->fqdn)<br/>";
                ++$count;
            }
        }
        
        echo "<h2>Total Number $count out of $total<h2>";
        echo "<h2>".count($resources)."<h2>";

        $this->render("none", null, true);
    }
*/
} 
