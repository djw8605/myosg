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
        if(!file_exists(config()->gip_summary_itb)) {
            throw new exception("Can't find the gip summary xml: ".config()->gip_summary_itb);
        }
        $cache_xml_itb = file_get_contents(config()->gip_summary_itb);
        $this->merge($gip, new SimpleXMLElement($cache_xml_itb));
        return $gip;
    }

    public function getBdii()
    {
        //cemon raw file listing for production
        $cemonbdii_url = file_get_contents(config()->cemonbdii_url);
        $cemonbdii = new SimpleXMLElement($cemonbdii_url);

        //cemon raw file listing for itb
        $cemonbdii_itb_url = file_get_contents(config()->cemonbdii_itb_url);
        $this->merge($cemonbdii, new SimpleXMLElement($cemonbdii_itb_url));
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
