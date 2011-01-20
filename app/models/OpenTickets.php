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

class OpenTickets
{
    public function get() {
        //shiould take less than 5 seconds to pull this data
        $ctx = stream_context_create(array('http' => array('timeout' => 5)));

        //load OpenTickets XML
        $c = new Cache(config()->gocticket_open_cache);
        if(!$c->isFresh(60)) { 
            try {
                slog("loading ".config()->gocticket_open_url);
                $xml = file_get_contents(config()->gocticket_open_url, 0, $ctx);
                //try parsing it..
                $obj = new SimpleXMLElement($xml);

                //store good xml
                $c->set($xml);
                return $obj;
            } catch(exception $e) {
                elog($e->getMessage()." -- using cache.");
            }
        }

        slog("Using cache");
        return new SimpleXMLElement($c->get());
    }
    
    public function getGroupByRID() {
        $tickets = $this->get();
        $tickets_grouped = array();
        foreach($tickets as $ticket) {
            //$rgid = @$ticket->Metadata[0]->ASSOCIATED_RG_ID[0];
            $rid = @$ticket->Metadata[0]->ASSOCIATED_R_ID[0];
            if(!empty($rid)) {
                //$rgid = (int)$rgid;
                $rid = (int)$rid;
                if(!isset($tickets_grouped[$rid])) {
                    $tickets_grouped[$rid] = array();
                }
                $tickets_grouped[$rid][] = $ticket;
            }
        }
        return $tickets_grouped;
    }
}

