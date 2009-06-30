<?

class LDIF
{
    public function getValidationSummary()
    {
        //load production gip summary
        if(!file_exists(config()->gip_summary)) {
            throw new exception("Can't find the gip summary xml: ".config()->gip_summary);
        }
        $cache_xml = file_get_contents(config()->gip_summary);
        $gip = new SimpleXMLElement($cache_xml);

        //load ITB gip summary (and merge!)
        if(file_exists(config()->gip_summary_itb)) {
            $cache_xml_itb = file_get_contents(config()->gip_summary_itb);
            $this->merge($gip, new SimpleXMLElement($cache_xml_itb));
        } else {
            elog("Can't find the gip summary xml (ignoring): ".config()->gip_summary_itb);
        }
        return $gip;
    }

    public function getBdii()
    {
        //try only for 1 seconds to pull this data
        $ctx = stream_context_create(array( 'http' => array('timeout' => 2)));

        //cemon raw file listing for production
        slog("loading ".config()->cemonbdii_url);
        $cemonbdii_url = file_get_contents(config()->cemonbdii_url, 0, $ctx);

        $cemonbdii = new SimpleXMLElement($cemonbdii_url);

        //cemon raw file listing for itb
        slog("loading ".config()->cemonbdii_itb_url);
        $cemonbdii_itb_url = file_get_contents(config()->cemonbdii_itb_url, 0, $ctx);

        try {
                $itb = new SimpleXMLElement($cemonbdii_itb_url);
                $this->merge($cemonbdii, $itb);
        } catch(exception $e) {
                elog("failed to parse for some reason... maybe itb is not available?");
        }
        return $cemonbdii;
    }

    function merge(SimpleXMLElement &$xml1, SimpleXMLElement $xml2)
    {
       // convert SimpleXML objects into DOM ones
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
           $dom1->documentElement->appendChild(
               $dom1->importNode($xpathQuery->item($i), true));
       }
       $xml1 = simplexml_import_dom($dom1);
    }
}
