<?php
//header("Content-Type: text/css");
require_once("app/config.php");
$imagebase = fullbase();

///////////////////////////////////////////////////////////////////////////////////////////////////
//common specific styles
?>
body
{
font-family: Arial;
}
a img
{
border: 0;
}

a.mailto
{
padding-left:20px;
background:transparent url(<?=$imagebase?>/images/email.png) no-repeat 0px 0px;
}

.vo_report h4
{
padding: 3px;
background-color: #ccc;
}
.vo_report_fqan
{
padding: 10px;
margin: 3px;
background-color: #eee;
}

div.error_message
{
padding: 5px;
padding-left: 50px;
background-color: #fee;
color: red;
border: 1px dotted red;
margin-bottom: 10px;
}

div.resource
{
padding: 2px;
padding-left:20px;
background:#ccc url(<?=$imagebase?>/images/server.png) no-repeat 2px 3px;
min-height: 16px;
margin-bottom: 5px;
}
div.h4
{
background-color: #ddd;
font-weight: bold;
font-size: 12px;
}

div.resource_group
{
padding-left:5px;
padding-right:5px;
}
div.site
{
padding-left:20px;
background:transparent url(<?=$imagebase?>/images/house.png) no-repeat 0 0px;
min-height: 16px;
}

div.support_center
{
padding: 2px;
padding-left:5px;
padding-right:5px;
background-color: #ccc;
margin-bottom: 5px;
}

div.facility
{
padding-left:20px;
background:transparent url(<?=$imagebase?>/images/database.png) no-repeat 0 0px;
min-height: 16px;
}
div.contact
{
padding-left:20px;
background:transparent url(<?=$imagebase?>/images/user.png) no-repeat 0 0px;
min-height: 16px;
}
div.vo
{
padding: 2px;
padding-left:3px;
padding-right:7px;
background-color:#ccc;
}
div.metric
{
padding-left:20px;
background:transparent url(<?=$imagebase?>/images/drive_magnify.png) no-repeat 0 0px;
min-height: 16px;
}
div.metricinfo_header
{
padding:2px;
padding-left:22px;
background:#ccc url(<?=$imagebase?>/images/drive_magnify.png) no-repeat 3px 3px;
min-height: 16px;
}

div.cpuinfo_header
{
padding:2px;
padding-left:22px;
background:#ccc url(<?=$imagebase?>/images/process.png) no-repeat 3px 3px;
min-height: 16px;
}

div.gridtype_header
{
padding:2px;
padding-left:22px;
background:#ccc url(<?=$imagebase?>/images/process.png) no-repeat 3px 3px;
min-height: 16px;
}
div.grid_type
{
padding-left:20px;
background:transparent url(<?=$imagebase?>/images/list-items.gif) no-repeat 0 0px;
min-height: 16px;
}

div.border
{
border: 1px solid #ccc;
margin: 3px;
padding: 3px;
}


div.resource_group_header, div.downtime_group
{
background-color: gray;
color: white;
margin-bottom: 0.3em;
padding: 4px;
}

.contact_info
{
    background-color: #eee;
    padding: 10px;
    margin: 3px;
}

.service_info
{
    background-color: #eee;
    padding: 10px;
    margin: 3px;
}

.round, .vo_report h4, .vo_report_fqan, .contact_info, .service_info
{
-moz-border-radius-topleft: 4px;
-moz-border-radius-topright: 4px;
-moz-border-radius-bottomright: 4px;
-moz-border-radius-bottomleft: 4px;
-webkit-border-top-left-radius: 4px;
-webkit-border-top-right-radius: 4px;
-webkit-border-bottom-left-radius: 4px;
-webkit-border-bottom-right-radius: 4px;
}

div.sidenote
{
float: right;
text-align: right;
margin-right: 5px;
}


.bottom_border
{
border-bottom: 1px dotted #ddd;
padding-bottom: 5px;
margin-bottom: 5px;
}


div.indent
{
margin-left: 20px;
}
#sideContent div.indent
{
margin-left:10px;
}
pre
{
white-space: pre-wrap;       /* css-3 */
white-space: -moz-pre-wrap;  /* Mozilla, since 1999 */
white-space: -pre-wrap;      /* Opera 4-6 */
white-space: -o-pre-wrap;    /* Opera 7 */
word-wrap: break-word;       /* Internet Explorer 5.5+ */
background-color: #ddd;
color: black;
}

span.note
{
color: #888;
}
.hidden
{
display: none;
}
.button
{
cursor: pointer;
}

span.h3
{
margin-bottom: 3px;
font-weight: bold;
font-size: 13px;
}
span.h4
{
margin-bottom: 3px;
font-weight: bold;
font-size: 12px;
}

div.history_graph
{
    width: 100%;
    line-height: 100%;
}
div.history_graph img.graph
{
    width: 100%;
    height: 24px; 
}
table.graph
{
    border-collapse: collapse; 
}
table.ruler
{
    border-collapse: collapse; 
    font-size: 10px;
    color: #333;
    margin-bottom: 3px;
}
table.ruler td
{
    padding: 0px !important;
    margin: 0px !important;
    white-space: nowrap;
    text-align: right;
}
.expired .sidenote
{
    color: #c00;
}
.expired .h4, .notreported .h4
{
    color: gray;
}
.expired, .notreported
{
    background-color: #ddd !important;
}

span.fqdn
{
font-family: "Courier New";
font-size: 12px;
font-weight: normal;
}

#banner
{
    color: gray;
    padding-bottom: 5px;
}

p.note
{
    color: gray;
}

#logo
{
position: absolute;
top: 2px;
left: 5px;
}
p.warning
{
    background-image: url('<?=$imagebase?>/images/error.png');
    background-repeat: no-repeat;
    background-position: top left;
    padding-left: 20px;
}
p.info
{
    background-image: url('<?=$imagebase?>/images/bullet_green.png');
    background-repeat: no-repeat;
    background-position: top left;
    padding-left: 20px;
}
img.screenshot
{
    background-color: white; 
    padding: 3px;
    border: 3px solid gray;
    margin-right: 10px;
}
.ui-datepicker
{
font-size: 10px !important;
}

table.summary_table
{
    width: 100%;
    margin: 0px;
    border-collapse: collapse; 
    margin-bottom: 3px;
}

table.summary_table th
{
    text-align: left;
    padding: 3px;
    background-color: #eee;
    border-top: 1px solid #ddd;
}
table.summary_table td
{
    padding: 3px;
    align: top;
    border-top: 1px solid #ddd;
}
table.summary_subtable
{
    width: 100%;
    margin: 0px;
    border-collapse: collapse; 
}
table.summary_subtable td, table.summary_subtable th
{
    background-color: transparent;
    border-top: none;
    border-bottom: 1px solid #ddd;
}
table.summary_subtable th
{
    width: 130px;
}
.disabled
{
    background-color: #eee;
    color: #999;
}
div.downtime
{
    margin-top: 5px;
    background-color: #dde;
    padding: 10px;
}
div.downtime_detail
{
    padding: 3px;
}

h1, span.h1
{
font-size: 13px;
}
h2, span.h2
{
font-size: 12px;
}
h3, span.h3
{
font-weight: bold;
font-size: 11px;
}
h4, span.h4
{
font-weight: bold;
font-size: 11px;
}
h2,h3
{
padding-bottom: 2px;
border-bottom: solid 1px #bbb;
}
table.summary_table th
{
width: 130px;
}
.status_CRITICAL, .status_OK, .status_WARNING, .status_UNKNOWN, .status_DOWNTIME, .status_
{
    margin: 1px;
    padding: 2px;
    padding-left: 20px;
    min-height: 0px;
}
.status_CRITICAL
{
background:transparent url(http://myosg.grid.iu.edu/images/button_cancel_small.png) no-repeat 2px 3px;
}
.status_DOWNTIME
{
background:transparent url(http://myosg.grid.iu.edu/images/status_downtime_small.png) no-repeat 2px 3px;
}
.status_OK
{
background:transparent url(http://myosg.grid.iu.edu/images/status_ok_small.png) no-repeat 2px 3px;
}
.status_WARNING
{
background:transparent url(http://myosg.grid.iu.edu/images/status_warning_small.png) no-repeat 2px 3px;
}
.status_UNKNOWN, .status_
{
background:transparent url(http://myosg.grid.iu.edu/images/status_unknown_small.png) no-repeat 2px 3px;
}

.bottom_border
{
border: 0px;
}
div.history_graph img.graph
{
height: 12px; 
}
.hierarchy
{
font-weight: normal;
font-size: 10px;
color: #ccc;
}

