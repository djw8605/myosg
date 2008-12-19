<?

//global variable to store list of filter variables
$g_filters = array();

$g_uwa = false;
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
                $cleared_q .= "&";
            }
            $cleared_q .= $q;
        }
    }

    ?>
    <p><a href="#" onclick="document.location='<?=fullbase()."/$g_pagename?$cleared_q";?>';">Clear All Filters</a></p>
    <?
}

