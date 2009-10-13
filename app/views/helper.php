<?

//global variable to store list of filter variables
$g_filters = array();

function uwa()
{
    if(isset($_REQUEST["uwa"])) {
        return true;
    }
    return false;
}

//returns a unique id number for div element (only valid for each session - don't store!)
function getuid()
{
    if(isset($_SESSION['next_uid'])) {
        $next_uid = $_SESSION['next_uid'];
        $_SESSION["next_uid"] = $next_uid + 1;
        return "id".($next_uid+rand()); //add random number to avoid case when 2 different sessions are used
    } else {
        $_SESSION["next_uid"] = 1000; //let's start from 1000
        return $_SESSION["next_uid"];
    }
}

function outputAjaxToggle($title, $url)
{
    $out = "";
    $divid = getuid();
    $hide_script = "$('#$divid').slideUp();$('#${divid}_hide').hide();$('#${divid}_show').show();";
    $show_script = "$('#$divid').slideDown();$('#${divid}_hide').show();$('#${divid}_show').hide();";

    //load button
    $out .= "<div id=\"${divid}_load\" class=\"button\" onclick=\"\$('#${divid}_loading').show(); \$('#$divid').load('$url', function() {\$('#${divid}_load').hide();\$('#${divid}_hide').show();\$('#${divid}').slideDown();});\"><img src='".fullbase()."/images/plusbutton.gif'/> $title";
    $out .= " <span id=\"${divid}_loading\" class=\"hidden\"><img src='".fullbase()."/images/loading_animation_small.gif' height='10'/></span>";
    $out .= "</div>";

    //hide button
    $out .= "<div id=\"${divid}_hide\" class=\"button hidden\" onclick=\"$hide_script\"><img src='".fullbase()."/images/minusbutton.gif'/> $title</div>";

    //show button
    $out .= "<div id=\"${divid}_show\" class=\"button hidden\" onclick=\"$show_script\"><img src='".fullbase()."/images/plusbutton.gif'/> $title</div>";

    //content area
    $out .= "<div id=\"${divid}\" class=\"hidden\"></div>";
    return $out;
}

function outputToggle($show, $hide, $content, $open_by_default = false)
{
    $divid = getuid();
    ob_start();

    if(true) {
        $showbutton_style = "button";
        $hidebutton_style = "button";
        $detail_style = "detail";
        if($open_by_default) {
            $showbutton_style .= " hidden";
        } else {
            $hidebutton_style .= " hidden";
            $detail_style .= " hidden";
        }
        ?>
        <div id='show_<?=$divid?>' class='<?=$showbutton_style?>'><img src='<?=fullbase()?>/images/plusbutton.gif'/> <?=$show?></div>
        <div id='hide_<?=$divid?>' class='<?=$hidebutton_style?>'><img src='<?=fullbase()?>/images/minusbutton.gif'/> <?=$hide?></div>
        <div class='<?=$detail_style?>' id='detail_<?=$divid?>'><?=$content?></div>
        <script type='text/javascript'>
        $('#show_<?=$divid?>').click(function() {
            $('#detail_<?=$divid?>').slideDown("normal");
            $('#show_<?=$divid?>').hide();
            $('#hide_<?=$divid?>').show();
        });
        $('#hide_<?=$divid?>').click(function() {
            $('#detail_<?=$divid?>').slideUp();
            $('#hide_<?=$divid?>').hide();
            $('#show_<?=$divid?>').show();
        });
        </script>
        <?
    }

    $content = ob_get_contents();
    ob_end_clean();
    return $content;
}

function agoCalculation($timestamp)
{
    if($timestamp === null) return null;
    $ago = time() - $timestamp;
    return humanDuration($ago);
}
function humanDuration($ago)
{
    if($ago < 60) return $ago." seconds";
    if($ago < 60*60) return floor($ago/60)." minutes";
    if($ago < 60*60*24) return floor($ago/(60*60))." hours";
    return floor($ago/(60*60*24))." days";
}

function outputSelectBox2($field_id, $kv)
{
    global $g_pagename;
    global $g_filters;

    $g_filters[] = $field_id;
?>
    <select id="<?=$field_id?>" onchange="query.<?=$field_id?>=$(this).val(); document.location='<?=fullbase()."/$g_pagename?";?>'+jQuery.param(query);">
<?
    $current_value = @$_REQUEST[$field_id];
    foreach($kv as $value=>$name) {
        $selected = "";
        if($value == $current_value) {
            $selected = "selected=selected";
        }
        echo "<option value=\"$value\" $selected>$name</option>\n";
    }
?>
    </select>
<?
}

function outputSelectBox($field_id, $field_name, $model, $value_field, $name_field)
{
    global $g_pagename;
    global $g_filters;

    $g_filters[] = $field_id;

?>
    <?=$field_name?>:
    <p>
    <select id="filter_<?=$field_id?>" onchange="query.<?=$field_id?>=$(this).val(); document.location='<?=fullbase()."/$g_pagename?";?>'+jQuery.param(query);">
    <option value="">(All)</option>
    <?
    $rows = $model->get();
    $current_value = @$_REQUEST[$field_id];
    foreach($rows as $row) {
        $value = $row->$value_field;
        $name = $row->$name_field;
        $selected = "";
        if($value == $current_value) {
            $selected = "selected=selected";
        }

        //truncate name so that it won't be too long
        if(strlen($name) > 25) $name = substr($name, 0, 25)."...";

        echo "<option value=\"$value\" $selected>$name</option>\n";
    }
    ?>
    </select>
    </p>
<?
}

function outputCheckboxList($prefix, $items)
{
    global $g_pagename;
    global $g_filters;


    echo "<p>";
    foreach($items as $id=>$value) {
        $name = $prefix."_".$id;

        $g_filters[] = $name;
        $current_value = @$_REQUEST[$name];
        $selected = "";
        if($current_value == "true") {
            $selected = "checked=\"checked\"";
        }
        $script = "query.$name=this.checked;document.location='".fullbase()."/$g_pagename?"."'+jQuery.param(query);";
        echo "<input type=\"checkbox\" name=\"$name\" onclick=\"$script\" $selected/> $value<br/>";
    } 
    echo "</p>";
}

function outputClearFilterButton()
{
    global $g_pagename;
    global $g_filters;

    //we need to clear only the variables that are set via filters
    $query = $_SERVER["QUERY_STRING"];
    $query_s = split("&", $query);
    $cleared_q = "";
    foreach($query_s as $q) {
        $q_s = split("=", $q);
        if(!in_array($q_s[0], $g_filters)) {
            if($cleared_q == "") {
                $cleared_q .= "?";
            } else {
                $cleared_q .= "&amp;";
            }
            $cleared_q .= $q;
        }
    }

    ?>
    <p><a href="#" onclick="document.location='<?=fullbase()."/$g_pagename$cleared_q";?>';">Clear All Filters</a></p>
    <?
}

function checklist($id, $title, $kv)
{
    //output title check box
    $checked = "";
    if(isset($_REQUEST[$id])) { 
        $checked = "checked=checked"; 
        ?>
        <script type="text/javascript">
        $(document).ready(function() {
            $("#<?=$id?>__list").show();
        });
        </script>
        <?
    }
    $title = "<input type=\"checkbox\" name=\"$id\" $checked onclick=\"if(this.checked) {\$('#${id}__list').show('normal');} else {\$('#${id}__list').hide();}\"/> <span>$title</span><br/>";

    //determine list class
    $c = "hidden ";
    if(count($kv) > 8) { 
        $c .= "scrolled_list ";
    }

    //output list
    $list = "";
    $list .= "<div class=\"$c list\" id=\"${id}__list\">";
    foreach($kv as $key=>$value) {
        $name = "${id}_$key";
        $checked = "";
        if(isset($_REQUEST[$name])) {
            $checked = "checked=checked";
        }
        $list .= "<input type=\"checkbox\" name=\"$name\" $checked/> <span>$value</span><br/>";
    }
    $list .= "</div>";

    return "<div id=\"$id\">".$title.$list."</div>";
}

function fblist($id, $title, $kv)
{
    $out = "";

    //output title check box
    $checked = "";
    if(isset($_REQUEST[$id])) { 
        $checked = "checked=checked"; 
        ?>
        <script type="text/javascript">
        $(document).ready(function() {
            $("#<?=$id?>__list").show();
        });
        </script>
        <?
    }
    $out .= "<input type=\"checkbox\" name=\"$id\" $checked onclick=\"if(this.checked) {\$('#${id}__list').show('normal');} else {\$('#${id}__list').hide();}\"/> <span>$title</span><br/>";

    //output list editor
    $out .= "<div class=\"indent hidden fblist_container\" id=\"${id}__list\"><div class=\"fblist\" style=\"position: relative;\" onclick=\"$(this).find('.autocomplete').focus(); return false;\">";

    //output script
    $delete_url = fullbase()."/images/delete.png";
    $script = "<script type='text/javascript'>$(document).ready(function() {";
    $script .= "var ${id}__listdata = [";
    $first = true;
    $pre_selected ="";
    foreach($kv as $key=>$value) {
        $itemid = "${id}_$key";
        if(isset($_REQUEST[$itemid])) {
            $pre_selected .= "<div><img onclick=\"$(this).parent().remove();\" src=\"$delete_url\"/>".$value[0]."<input type=\"hidden\" name=\"$itemid\"/ value=\"on\"></div>";
        }
        $name = str_replace(array("\n", "\r"), "", htmlsafe($value[0]));
        $desc = str_replace(array("\n", "\r"), "", htmlsafe($value[1]));
        if(!$first) {
            $script .= ",\n";
        }
        $first = false;
        $script .= "{ id: \"$itemid\", name: \"$name\", desc: \"$desc\" }";
    }
    $script .= "];";
    $script .= <<<BLOCK
    $("#${id}__list input.autocomplete").autocomplete(${id}__listdata, {
        max: 9999999,
        minChars: 0,
        mustMatch: true,
        matchContains: true,
        width: 280,
        formatItem: function(item) {
            if(item.desc == "") return item.name; 
            return item.name + " (" + item.desc + ")";
        }
    }).result(function(event, item) {
        if(item != null) {
            $(this).val("");
            $(this).before("<div><img onclick=\"$(this).parent().remove();\" src=\"$delete_url\"/>"+item.name+"<input type=\"hidden\" name=\""+item.id+"\" value=\"on\"/></div>");
        }
    });
});</script>
BLOCK;

    $out .= $pre_selected;
    $out .= "<input type='text' class='autocomplete' onfocus='$(\"#${id}__acnote\").fadeIn(\"slow\");' onblur='$(\"#${id}__acnote\").fadeOut(\"slow\");'/>";
    $out .= $script;

    //display note
    $out .= "<p id=\"${id}__acnote\" class=\"hidden\" style=\"position: absolute; color: #999; font-size: 9px; right: 3px; bottom: 0px; text-align: right; font-size: 10px;line-height: 100%;\">Press Down key to show all</p>";

    $out .= "</div>";
    $out .= "</div>";

    return $out;
}

function radiolist($id, $title, $kv, $default)
{
    //output title check box
    $checked = "";
    if(isset($_REQUEST[$id])) { 
        $checked = "checked=checked"; 
        ?>
        <script type="text/javascript">
        $(document).ready(function() {
            $("#<?=$id?>__list").show();
        });
        </script>
        <?
    }
    $title = "<input type=\"checkbox\" name=\"$id\" $checked onclick=\"if(this.checked) {\$('#${id}__list').show('normal');} else {\$('#${id}__list').hide();}\"/> <span>$title</span><br/>";

    //determine list class
    $c = "hidden ";
    if(count($kv) > 8) { 
        $c .= "scrolled_list ";
    }

    //output list
    $list = "";
    $list .= "<div class=\"$c list\" id=\"${id}__list\">";

    //set default 
    $current_value = $default; 
    $name = "${id}_value";
    if(isset($_REQUEST[$name])) {
        $current_value = $_REQUEST[$name];
    }

    foreach($kv as $key=>$value) {
        $checked = "";
        if($current_value == $key) {
            $checked = "checked=checked";
        }
        $list .= "<input type=\"radio\" name=\"$name\" value=\"$key\" $checked/> <span>$value</span><br/>";
    }
    $list .= "</div>";

    return "<div id=\"$id\">".$title.$list."</div>";
}

function helpbutton($type)
{
    return "<a target=\"_help\" href=\"https://twiki.grid.iu.edu/bin/view/Operations/OIMTermDefinition#$type\"><img src=\"".fullbase()."/images/help.png"."\"/></a>";
}

function externalurl($url)
{
    if($url == "") {
        return "";
    }
    return "<a target=\"_blank\" href=\"$url\">".htmlsafe($url)."</a>";
}

function emailaddress($email)
{
    if($email == "") {
        return "";
    }
    return "<a class=\"mailto\" href=\"mailto:$email\">".htmlsafe($email)."</a>";
}

function htmlsafe($str)
{
    return htmlentities($str, ENT_NOQUOTES, "UTF-8");
}
