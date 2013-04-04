<?php
/**************************************************************************************************

Copyright 2009 The Trustees of Indiana University

Licensed under the Apache License, Version 2.0 (the "License"); you may not use this file except in
compliance with the License. You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software distributed under the License
is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or
implied. See the License for the specific language governing permissions and limitations under the
License.

**************************************************************************************************/

class LDIF
{
    public function getValidationSummary()
    {
        //load production gip summary
        if(!file_exists(config()->gip_summary)) {
            throw new exception("Can't find the gip summary xml: ".config()->gip_summary);
        }
        if(file_exists(config()->gip_summary) && filesize(config()->gip_summary) > 0) {
            $cache_xml = file_get_contents(config()->gip_summary);
            $gip = new SimpleXMLElement($cache_xml);
        } else {
            elog("Can't find the valid gip summary xml: ".config()->gip_summary);
        }

/*
        //load ITB gip summary (and merge!)
        if(file_exists(config()->gip_summary_itb)) {
            $cache_xml_itb = file_get_contents(config()->gip_summary_itb);
            $this->merge($gip, new SimpleXMLElement($cache_xml_itb));
        } else {
            elog("Can't find the gip summary xml (ignoring): ".config()->gip_summary_itb);
        }
*/
        $ret = array();
        
        //parse it out
        foreach($gip->ResourceGroup as $rg) {
            $name = (string)$rg->Name;
            $attrs = $rg->attributes();

            $grid_type = $attrs["type"];
            //TODO - what should I do with grid type?

            $ret[$name] = $rg;
        }
        return $ret;
    }

    public function getWLCGStatus()
    {
        $ret = array(); 

        $xml_str = file_get_contents(config()->gip_wlcg_status);
        $xml = new SimpleXMLElement($xml_str);
        $rgs = $xml->ResourceGroups->ResourceGroup;
        foreach($rgs as $rg) {
            $rgid = (int)$rg->GroupID;
            if(!isset($ret[$rgid])) {
                $ret[$rgid] = array();
            }
            foreach($rg->WLCGBDIIs[0]->WLCGBDII as $status) {
                $hostname = (string)$status->HostName;
                if(!isset($ret[$rgid][$hostname])) {
                    $ret[$rgid][$hostname] = array();
                }
                $ret[$rgid][$hostname][] = $status;
            }
        }
    
        $updatetime = filemtime(config()->gip_wlcg_status);
        $ret["updatetime"] = $updatetime;
        
        return $ret;
    }

    public function getBdii()
    {
        //try only for N seconds to pull this data
        $ctx = stream_context_create(array('http' => array('timeout' => 8)));

        //cache these xml for little bit
        $seconds = 60;

        $c = new Cache("/tmp/myosg.bdii");
        if($c->isFresh($seconds)) { 
            $cemonbdii_content = $c->get();
        } else {
            //cemon raw file listing for production
            slog("loading ".config()->cemonbdii_url);
            $cemonbdii_content = file_get_contents(config()->cemonbdii_url, 0, $ctx);
            if($cemonbdii_content !== false) {
                $c->set($cemonbdii_content);
            } else {
                error_log("failed to download xml from ".config()->cemonbdii_url. " -- using previous cache");
                error_log(print_r($ctx, true));
                //use previous cache
                $cemonbdii_content = $c->get();
            }
        }
        $cemonbdii = new SimpleXMLElement($cemonbdii_content);

/*
        $c = new Cache("/tmp/myosg.bdii-itb");
        if($c->isFresh($seconds)) { 
            $cemonbdii_itb_content = $c->get();
        } else {
            //cemon raw file listing for itb
            slog("loading ".config()->cemonbdii_itb_url);
            $cemonbdii_itb_content = file_get_contents(config()->cemonbdii_itb_url, 0, $ctx);
            $c->set($cemonbdii_itb_content);
        }

        //merge itb content to prod content
        try {
            $itb = new SimpleXMLElement($cemonbdii_itb_content);
            $this->merge($cemonbdii, $itb);
        } catch(exception $e) {
            elog("failed to parse for some reason... maybe itb is not available?");
        }
*/
        return $cemonbdii;
    }

    function merge(SimpleXMLElement &$xml1, SimpleXMLElement $xml2)
    {
       // convert SimpleXML objects into DOM
       $dom1 = new DomDocument();
       $dom2 = new DomDocument();
       $dom1->loadXML($xml1->asXML());
       $dom2->loadXML($xml2->asXML());

       // pull all child elements of second XML
       $xpath = new domXPath($dom2);
       $xpathQuery = $xpath->query('/*/*');
       for ($i = 0; $i < $xpathQuery->length; $i++)
       {
           // and pump them into first one
           $dom1->documentElement->appendChild($dom1->importNode($xpathQuery->item($i), true));
       }
       $xml1 = simplexml_import_dom($dom1);
    }
}
