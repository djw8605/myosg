#!/usr/bin/python
import urllib
import libxml2

#download VO activation XML
url = urllib.urlopen("http://myosg.grid.iu.edu/voactivation/xml?datasource=activation&start_type=7daysago&start_date=10%2F09%2F2009&end_type=now&end_date=10%2F09%2F2009&all_vos=on&active_value=1")
vo_activation = url.read()
url.close()

#download VO summary XML
url = urllib.urlopen("http://myosg.grid.iu.edu/vosummary/xml?datasource=summary&summary_attrs_showreporting_group=on&start_type=7daysago&start_date=10%2F09%2F2009&end_type=now&end_date=10%2F09%2F2009&all_vos=on&active_value=1")
vo_summary = url.read()
url.close()

#Parse XMLs
doc_activation = libxml2.parseDoc(vo_activation)
ctxt_activation = doc_activation.xpathNewContext()
doc_summary = libxml2.parseDoc(vo_summary)
ctxt_summary = doc_summary.xpathNewContext()

#pull whichever the activation section that I am interested in..
vos = ctxt_activation.xpathEval("//VOActivation/Active/VO")
#vos = ctxt.xpathEval("//VOActivation/Enabled")
#vos = ctxt.xpathEval("//VOActivation/Disabled")

#iterate through all VOs..
for vo in vos:
    #display some basic information about VO
    voname = vo.xpathEval("Name")[0].content
    void = vo.xpathEval("ID")[0].content
    print "VO: " + voname + " (id=" + void + ")"
    
    #lookup reporting group from the summary
    reporting_groups = ctxt_summary.xpathEval("//VOSummary/VO[ID="+void+"]/ReportingGroups/ReportingGroup")
    for group in reporting_groups:
        name = group.xpathEval("Name")[0].content
        print "\tReporting Group: " + name

        fqans = group.xpathEval("FQANs/FQAN")
        print "\t\tFQANs:"
        for fqan in fqans:
            print "\t\t\t" + fqan.content

        contacts = group.xpathEval("Contacts/Contact/Name")
        print "\t\tContacts:"
        for contact in contacts:
            print "\t\t\t" + contact.content


     
