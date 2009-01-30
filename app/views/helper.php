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


/*
function uwa()
{
    global $g_uwa;
    return $g_uwa;
}
function setuwa()
{
    global $g_uwa;
    $g_uwa = true;
}
*/
//returns a unique id number for div element (only valid for each session - don't store!)
function getuid()
{
    if(isset($_SESSION['next_uid'])) {
        $next_uid = $_SESSION['next_uid'];
        $_SESSION["next_uid"] = $next_uid + 1;
        return $next_uid+rand(); //add random number to avoid case when 2 different sessions are used
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
    $out .= "<div id=\"${divid}_load\" class=\"button\"><img src='".fullbase()."/images/plusbutton.gif'/> $title";
    $out .= " <span id=\"${divid}_loading\" class=\"hidden\"><img src='".fullbase()."/images/loading_animation_small.gif' height='10px'/></span>";
    $out .= "</div>";

    //hide button
    $out .= "<div id=\"${divid}_hide\" class=\"button hidden\" onclick=\"$hide_script\"><img src='".fullbase()."/images/minusbutton.gif'/> $title</div>";

    //show button
    $out .= "<div id=\"${divid}_show\" class=\"button hidden\" onclick=\"$show_script\"><img src='".fullbase()."/images/plusbutton.gif'/> $title</div>";

    //content area
    $out .= "<div id=\"${divid}\" class=\"hidden\"></div>";

    $out .= "<script text='text/javascript'>$('#${divid}_load').click(function() {loaditem('$divid', '$url')});</script>";
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
            $('#detail_<?=$divid?>').slideDown("normal", function() {
/*
                if(uwa()) {
                    widget.callback('onUpdateBody');
                }
*/
            });
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

