<?php

class Perfsonar
{
    public function getMatrixlist() {
        //try only for N seconds to pull this data
        $ctx = stream_context_create(array('http' => array('timeout' => 8)));

        //load list of all matrices
        $c = new Cache("/tmp/myosg.personar.matrices");
        if($c->isFresh(60*10)) {//seconds
            $matrices = $c->get();
        } else {
            $url = config()->perfsonar_matrix_url;
            $json = file_get_contents($url, 0, $ctx);
            if($json !== false) {
                $matrices = json_decode($json);
                $c->set($matrices);
            } else {
                error_log("failed to download xml from $url -- using previous cache");
                error_log(print_r($ctx, true));
                $matrices = $c->get();
            }
        }
        return $matrices;
    }

    //get by matrix "name" like "vo_all/perfsonarbuoy/owamp"
    //if you know the datastore matrix id, use getMatrixByDatastoreID instead
    public function getMatrix($matrix_id) {
        $matrices = $this->getMatrixList();

        //convert matrix id to datastore matrix id
        $id = null;
        foreach($matrices as $matrix) {
            if(strpos($matrix->name, "#".$matrix_id) !== false) {
                $id = $matrix->id;
                break;
            }
        }
        if(is_null($id)) {
            error_log("failed to find matrix with id: $matrix_id");
            return null;
        }
        return $this->getMatrixByDatastoreID($id);
    }

    public function getMatrixByDatastoreID($id) {
        //try only for N seconds to pull this data
        $ctx = stream_context_create(array('http' => array('timeout' => 8)));
        
        //now load the matrix using datastore id
        //error_log("using cache : /tmp/myosg.personar.matrix.$id");
        $c = new Cache("/tmp/myosg.personar.matrix.$id");
        if($c->isFresh(60*10)) {//seconds
            return $c->get();
        } else {
            $url = config()->perfsonar_matrix_url."/$id";
            slog("refreshing cache for $url");
            $json = file_get_contents($url, 0, $ctx);
            slog("done");
            if($json !== false) {
                $matrix = json_decode($json);
                $matrix->id = $id;//inject the matrix id part of matrix itself for later reference
                $c->set($matrix);
                return $matrix;
            } else {
                error_log("failed to download xml from $url -- using previous cache");
                error_log(print_r($ctx, true));
                //use previous cache
                return $c->get();
            }
        }
    }

    public function getHosts() {
        $c = new Cache("/tmp/myosg.personar.hosts");
        if($c->isFresh(60*10)) {//seconds
            return $c->get();//use cache
        } else {
            //try only for N seconds to pull this data
            $ctx = stream_context_create(array('http' => array('timeout' => 8)));

            $url = config()->perfsonar_host_url;
            slog("refreshing cache for $url");
            $json = file_get_contents($url, 0, $ctx);
            slog("done");
            if($json !== false) {
                $hosts = json_decode($json);
                $hosts = $this->indexHost($hosts);
                $c->set($hosts);
                return $hosts;
            } else {
                //failed to load then, use stale cache
                error_log("failed to download xml from $url -- using previous cache");
                error_log(print_r($ctx, true));
                return $c->get();
            }
        }
    }

    function indexHost($obj) {
        $indexed = array();
        foreach($obj as $rec) {
            $indexed[$rec->hostname] = $rec->id;
        }
        return $indexed;
    }

    public function getHost($id) {
        $c = new Cache("/tmp/myosg.personar.hosts.$id");
        if($c->isFresh(60*10)) {//seconds
            return $c->get();//use cache
        } else {
            //try only for N seconds to pull this data
            $ctx = stream_context_create(array('http' => array('timeout' => 8)));

            $url = config()->perfsonar_host_url."/$id";
            slog("refreshing cache for $url");
            $json = file_get_contents($url, 0, $ctx);
            slog("done");
            if($json !== false) {
                $hosts = json_decode($json);
                $c->set($hosts);
                return $hosts;
            } else {
                //failed to load then, use stale cache
                error_log("failed to download xml from $url -- using previous cache");
                error_log(print_r($ctx, true));
                return $c->get();
            }
        }
    }
}
