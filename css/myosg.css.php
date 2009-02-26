<?php
header("Content-Type: text/css");
include("../app/config.php");
function uwa() 
{
    if(isset($_REQUEST["uwa"])) {
        return true;
    }
    return false;
}

//determine image base
/*
if(uwa()) {
    $base = "http://myosg.grid.iu.edu";
} else {
    $base = "..";
}
*/
function image()
{
    return fullbase()."/..";
}

///////////////////////////////////////////////////////////////////////////////////////////////////
//common specific styles
?>
a img
{
border: 0;
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
padding-left:20px;
background:transparent url(<?=image()?>/images/server.png) no-repeat 0 0px;
min-height: 16px;
}

div.resource_group
{
padding-left:50px;
background:transparent url(<?=image()?>/images/resource_group.png) no-repeat 0 0px;
min-height: 20px;
}
div.site
{
padding-left:20px;
background:transparent url(<?=image()?>/images/house.png) no-repeat 0 0px;
min-height: 16px;
}

div.support_center
{
padding-left:20px;
background:transparent url(<?=image()?>/images/medal_bronze_2.png) no-repeat 0 0px;
min-height: 16px;
}

div.facility
{
padding-left:20px;
background:transparent url(<?=image()?>/images/database.png) no-repeat 0 0px;
min-height: 16px;
}
div.contact
{
padding-left:20px;
background:transparent url(<?=image()?>/images/user.png) no-repeat 0 0px;
min-height: 16px;
}
div.vo
{
padding-left:20px;
background:transparent url(<?=image()?>/images/group.png) no-repeat 0 0px;
min-height: 16px;
}
div.metric
{
padding-left:20px;
background:transparent url(<?=image()?>/images/drive_magnify.png) no-repeat 0 0px;
min-height: 16px;
}
div.grid_type
{
padding-left:20px;
background:transparent url(<?=image()?>/images/list-items.gif) no-repeat 0 0px;
min-height: 16px;
}

div.border
{
border: 1px solid #ccc;
margin: 3px;
padding: 3px;
}

div.resource_group_header
{
border-bottom: dotted 1px #aaa;
padding-bottom: 0.3em;
}

div.sidenote
{
float: right;
color: gray;
text-align: right;
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
color: #aaa;
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
    border-top:1px solid #999;
    color: #333;
    margin-bottom: 2px;
}
table.ruler td
{
    padding: 0px !important;
    margin: 0px !important;
    white-space: nowrap;
    text-align: right;
}
.expired
{
    background-color: #bbb;
}
.expired .sidenote
{
    color: #c00;
}
span.fqdn
{
font-family: "Courier New";
font-size: 12px;
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
top: 6px;
left: 10px;
}
p.warning
{
    background-image: url('<?=image()?>/images/error.png');
    background-repeat: no-repeat;
    background-position: top left;
    padding-left: 20px;
}
p.info
{
    background-image: url('<?=image()?>/images/bullet_green.png');
    background-repeat: no-repeat;
    background-position: top left;
    padding-left: 20px;
}
img.screenshot
{
    background-color: white; 
    padding: 3px;
    border: 3px solid black;
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
}

table.summary_table th
{
    text-align: left;
    padding-left: 3px;
    padding-right: 5px;
    background-color: #eee;
    width: 100px;
    border-top: 1px solid #ddd;
}
table.summary_table tr
{
}
table.summary_table td
{
    padding: 3px;
    align: top;
    border-top: 1px solid #ddd;
}
div.downtime
{
    background-color: #ddf;
    padding: 3px;
}
<?
///////////////////////////////////////////////////////////////////////////////////////////////////
//web(non-uwa) specific styles 
if(!uwa()) {
?>
a[target=_blank]
{
padding-left:15px;
background:transparent url(<?=image()?>/images/external_link_icon.gif) no-repeat 0 -1px;
}

.status_, .status_OK, .status_CRITICAL, .status_WARNING, .status_UNKNOWN, .status_DOWNTIME
{
padding: 5px;
padding-left: 50px;

min-height: 25px;
height: auto !important;
height: 25px;
}

.status_CRITICAL
{
background:transparent url(<?=image()?>/images/button_cancel.png) no-repeat 5px 5px;
}

.status_DOWNTIME
{
background:transparent url(<?=image()?>/images/status_downtime.png) no-repeat 5px 5px;
}

.status_OK
{
background:transparent url(<?=image()?>/images/status_ok.png) no-repeat 5px 5px;
}

.status_WARNING
{
background:transparent url(<?=image()?>/images/status_warning.png) no-repeat 5px 5px;
}

.status_UNKNOWN, .status_
{
background:transparent url(<?=image()?>/images/status_unknown.png) no-repeat 5px 5px;
}
<?
///////////////////////////////////////////////////////////////////////////////////////////////////
//uwa specific styles (override above styles with uwa specific)
} else {
?>
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
.expired
{
background-color: #bbb;
}

.bottom_border
{
border: 0px;
}
div.history_graph img.graph
{
    height: 12px; 
}

<?}?>
