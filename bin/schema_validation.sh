#these script only checks the schema against HTTP version of the XML.. not HTTPS

HOST="http://dev.grid.iu.edu/~hayashis/myosg"
#HOST="http://myosg-itb.grid.iu.edu"
#HOST="http://myosg.grid.iu.edu"

SCHEMA="../schema"

echo "RG BDII"
url="$HOST/rgbdii/xml?datasource=bdii&summary_attrs_showgipstatus=on&summary_attrs_showwlcg=on&summary_attrs_showservice=on&summary_attrs_showrsvstatus=on&summary_attrs_showfqdn=on&summary_attrs_showenv=on&summary_attrs_showcontact=on&gip_status_attrs_showtestresults=on&downtime_attrs_showpast=&account_type=cumulative_hours&ce_account_type=gip_vo&se_account_type=vo_transfer_volume&bdiitree_type=total_jobs&bdii_attrs_showversion=on&bdii_attrs_showstat=on&start_type=7daysago&start_date=07/30/2010&end_type=now&end_date=07/30/2010&facility=on&facility_10009=on&gridtype=on&gridtype_1=on&service_1=on&service_5=on&service_2=on&service_3=on&service_central_value=0&service_hidden_value=0&active=on&active_value=1&disable_value=1"
wget -O /tmp/rgbdii.raw.xml $url 2> /dev/null
xmllint --format /tmp/rgbdii.raw.xml > /tmp/rgbdii.xml
xmllint --noout --schema $SCHEMA/rgbdii.xsd /tmp/rgbdii.xml

echo "Testing MISC User"
url="$HOST/miscuser/xml?datasource=user"
wget -O /tmp/miscuser.raw.xml $url 2> /dev/null
xmllint --format /tmp/miscuser.raw.xml > /tmp/miscuser.xml
xmllint --noout --schema $SCHEMA/miscuser.xsd /tmp/miscuser.xml

echo "Testing MISC Status"
url="$HOST/miscstatus/xml?datasource=status"
wget -O /tmp/miscstatus.raw.xml $url 2> /dev/null
xmllint --format /tmp/miscstatus.raw.xml > /tmp/miscstatus.xml
xmllint --noout --schema $SCHEMA/miscstatus.xsd /tmp/miscstatus.xml

echo "Testing rgsummary"
url="$HOST/rgsummary/xml?datasource=summary&summary_attrs_showdesc=on&summary_attrs_showgipstatus=on&summary_attrs_showhierarchy=on&summary_attrs_showwlcg=on&summary_attrs_showservice=on&summary_attrs_showrsvstatus=on&summary_attrs_showfqdn=on&summary_attrs_showvomembership=on&summary_attrs_showvoownership=on&summary_attrs_showenv=on&summary_attrs_showcontact=on&gip_status_attrs_showtestresults=on&gip_status_attrs_showfqdn=on&account_type=cumulative_hours&ce_account_type=gip_vo&se_account_type=vo_transfer_volume&start_type=7daysago&start_date=08%2F20%2F2009&end_type=now&end_date=08%2F20%2F2009&all_resources=on&gridtype=on&gridtype_1=on&gridtype_2=on&gipstatus=on&gipstatus_OK=on&gipstatus_FAIL=on&gipstatus_UNKNOWN=on&status=on&status_1=on&status_2=on&status_3=on&status_4=on&status_99=on&service=on&service_4=on&service_1=on&service_5=on&service_2=on&service_3=on&service_109=on&vosup=on&vosup_25=on&voown=on&voown_35=on&voown_1=on&has_status=on&active=on&active_value=1&disable=on&disable_value=0&has_wlcg=on"
wget -O /tmp/rgsummary.raw.xml $url 2> /dev/null
xmllint --format /tmp/rgsummary.raw.xml > /tmp/rgsummary.xml
xmllint --noout --schema $SCHEMA/rgsummary.xsd /tmp/rgsummary.xml

echo "Testing rgcurrentstatus"
url="$HOST/rgcurrentstatus/xml?datasource=currentstatus&summary_attrs_showservice=on&summary_attrs_showrsvstatus=on&summary_attrs_showfqdn=on&current_status_attrs_shownc=on&gip_status_attrs_showtestresults=on&gip_status_attrs_showfqdn=on&account_type=cumulative_hours&ce_account_type=gip_vo&se_account_type=vo_transfer_volume&start_type=7daysago&start_date=08%2F21%2F2009&end_type=now&end_date=08%2F21%2F2009&all_resources=on&gridtype=on&gridtype_1=on&active_value=1&disable_value=1"
wget -O /tmp/rgcurrentstatus.raw.xml $url 2> /dev/null
xmllint --format /tmp/rgcurrentstatus.raw.xml > /tmp/rgcurrentstatus.xml
xmllint --noout --schema $SCHEMA/rgcurrentstatus.xsd /tmp/rgcurrentstatus.xml

echo "Testing GIP validation Status"
url="$HOST/rggipstatus/xml?datasource=gipstatus&summary_attrs_showservice=on&summary_attrs_showrsvstatus=on&summary_attrs_showfqdn=on&gip_status_attrs_showtestresults=on&gip_status_attrs_showwlcgstatus=on&gip_status_attrs_showresource=on&gip_status_attrs_showcemondata=on&downtime_attrs_showpast=&account_type=cumulative_hours&ce_account_type=gip_vo&se_account_type=vo_transfer_volume&bdiitree_type=total_jobs&start_type=7daysago&start_date=07%2F14%2F2010&end_type=now&end_date=07%2F14%2F2010&facility=on&facility_10009=on&gridtype=on&gridtype_1=on&service_central_value=0&service_hidden_value=0&active_value=1&disable_value=1"
wget -O /tmp/rggipstatus.raw.xml $url 2> /dev/null
xmllint --format /tmp/rggipstatus.raw.xml > /tmp/rggipstatus.xml
xmllint --noout --schema $SCHEMA/rggipstatus.xsd /tmp/rggipstatus.xml

echo "Testing RSV Status History"
url="$HOST/rgstatushistory/xml?datasource=statushistory&summary_attrs_showservice=on&summary_attrs_showrsvstatus=on&summary_attrs_showfqdn=on&gip_status_attrs_showtestresults=on&gip_status_attrs_showfqdn=on&account_type=cumulative_hours&ce_account_type=gip_vo&se_account_type=vo_transfer_volume&start_type=7daysago&start_date=08%2F23%2F2009&end_type=now&end_date=08%2F23%2F2009&all_resources=on&gridtype=on&gridtype_1=on&active_value=1&disable_value=1"
wget -O /tmp/rgstatushistory.raw.xml $url 2> /dev/null
xmllint --format /tmp/rgstatushistory.raw.xml > /tmp/rgstatushistory.xml
xmllint --noout --schema $SCHEMA/rgstatushistory.xsd /tmp/rgstatushistory.xml

echo "Testing Availability History"
url="$HOST/rgarhistory/xml?datasource=arhistory&summary_attrs_showservice=on&summary_attrs_showrsvstatus=on&summary_attrs_showfqdn=on&gip_status_attrs_showtestresults=on&gip_status_attrs_showfqdn=on&account_type=cumulative_hours&ce_account_type=gip_vo&se_account_type=vo_transfer_volume&start_type=7daysago&start_date=08%2F23%2F2009&end_type=now&end_date=08%2F23%2F2009&all_resources=on&gridtype=on&gridtype_1=on&active_value=1&disable_value=1"
wget -O /tmp/rgarhistory.raw.xml $url 2> /dev/null
xmllint --format /tmp/rgarhistory.raw.xml > /tmp/rgarhistory.xml
xmllint --noout --schema $SCHEMA/rgarhistory.xsd /tmp/rgarhistory.xml

echo "Testing Availability Metrics"
url="$HOST/rgarmetric/xml?datasource=armetric&summary_attrs_showservice=on&summary_attrs_showrsvstatus=on&summary_attrs_showfqdn=on&current_status_attrs_shownc=on&gip_status_attrs_showtestresults=on&gip_status_attrs_showfqdn=on&account_type=cumulative_hours&ce_account_type=gip_vo&se_account_type=vo_transfer_volume&start_type=7daysago&start_date=04%2F08%2F2009&end_type=now&end_date=04%2F15%2F2009&all_resources=on&gridtype_1=on&service_4=on&service_1=on&service_5=on&service_2=on&service_3=on&active_value=1&disable_value=1"
wget -O /tmp/rgarmetric.raw.xml $url 2> /dev/null
xmllint --format /tmp/rgarmetric.raw.xml > /tmp/rgarmetric.xml
xmllint --noout --schema $SCHEMA/rgarmetric.xsd /tmp/rgarmetric.xml

echo "Testing Downtime Schedule"
url="$HOST/rgdowntime/xml?datasource=downtime&summary_attrs_showservice=on&summary_attrs_showrsvstatus=on&summary_attrs_showfqdn=on&current_status_attrs_shownc=on&gip_status_attrs_showtestresults=on&gip_status_attrs_showfqdn=on&account_type=cumulative_hours&ce_account_type=gip_vo&se_account_type=vo_transfer_volume&start_type=7daysago&start_date=04%2F08%2F2009&end_type=now&end_date=04%2F15%2F2009&all_resources=on&gridtype_1=on&service_4=on&service_1=on&service_5=on&service_2=on&service_3=on&active_value=1&disable_value=1"
wget -O /tmp/rgdowntime.raw.xml $url 2> /dev/null
xmllint --format /tmp/rgdowntime.raw.xml > /tmp/rgdowntime.xml
xmllint --noout --schema $SCHEMA/rgdowntime.xsd /tmp/rgdowntime.xml

echo "Testing Support Center"
url="$HOST/scsummary/xml?datasource=summary&summary_attrs_showdesc=on&summary_attrs_showcontact=on&summary_attrs_showsites=on&all_scs=on&active=on&active_value=1"
wget -O /tmp/scsummary.raw.xml $url 2> /dev/null
xmllint --format /tmp/scsummary.raw.xml > /tmp/scsummary.xml
xmllint --noout --schema $SCHEMA/scsummary.xsd /tmp/scsummary.xml

echo "Testing VO Summary"
url="$HOST/vosummary/xml?datasource=summary&summary_attrs_showdesc=on&summary_attrs_showmember_resource=on&summary_attrs_showfield_of_science=on&summary_attrs_showreporting_group=on&summary_attrs_showparent_vo=on&summary_attrs_showcontact=on&all_vos=on&active_value=1"
wget -O /tmp/vosummary.raw.xml $url 2> /dev/null
xmllint --format /tmp/vosummary.raw.xml > /tmp/vosummary.xml
xmllint --noout --schema $SCHEMA/vosummary.xsd /tmp/vosummary.xml

echo "Testing VO Activation"
url="$HOST/voactivation/xml?datasource=activation&start_type=7daysago&start_date=09%2F30%2F2009&end_type=now&end_date=09%2F30%2F2009&all_vos=on&active_value=1"
wget -O /tmp/voactivation.raw.xml $url 2> /dev/null
xmllint --format /tmp/voactivation.raw.xml > /tmp/voactivation.xml
xmllint --noout --schema $SCHEMA/voactivation.xsd /tmp/voactivation.xml

echo "Testing VO VOMSES Status"
url="$HOST/vovomsstatus/xml?datasource=vovomsstatus&start_type=7daysago&start_date=09%2F30%2F2009&end_type=now&end_date=09%2F30%2F2009&all_vos=on&active_value=1"
wget -O /tmp/vovomsstatus.raw.xml $url 2> /dev/null
xmllint --format /tmp/vovomsstatus.raw.xml > /tmp/vovomsstatus.xml
xmllint --noout --schema $SCHEMA/vovomsstatus.xsd /tmp/vovomsstatus.xml

echo "Testing MISC CPU Info"
url="$HOST/misccpuinfo/xml?datasource=cpuinfo&count_sg_1=on&count_active=on&count_enabled=on"
wget -O /tmp/misccpuinfo.raw.xml $url 2> /dev/null
xmllint --format /tmp/misccpuinfo.raw.xml > /tmp/misccpuinfo.xml
xmllint --noout --schema $SCHEMA/misccpuinfo.xsd /tmp/misccpuinfo.xml

