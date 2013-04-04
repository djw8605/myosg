<?php

class Perfsonar
{
    public function getMatrix($id) {
        $c = new Cache("/tmp/myosg.personar.matrix.$id");
        if($c->isFresh(60*10)) {//seconds
            return $c->get();
        } else {
            //try only for N seconds to pull this data
            $ctx = stream_context_create(array('http' => array('timeout' => 8)));

            $url = config()->perfsonar_matrix_url."/$id";
            slog("refreshing cache for $url");
            $json = file_get_contents($url, 0, $ctx);
            slog("done");
            if($json !== false) {
                $matrix = json_decode($json);
                $c->set($matrix);
                return $matrix;
            } else {
                error_log("failed to download xml from ".config()->cemonbdii_url. " -- using previous cache");
                error_log(print_r($ctx, true));
                //use previous cache
                return $c->get();
            }
        }
    }
}
