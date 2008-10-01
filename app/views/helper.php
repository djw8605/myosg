<?

//returns a unique id number for div element (only valid for each session - don't store!)
function getuid()
{
    if(isset(session()->uid)) {
        $next_uid = session()->uid;
        session()->uid = $next_uid + 1;
        return $next_uid+rand(); //add random number to avoid case when 2 different sessions are used
    } else {
        session()->uid = 1000; //let's start from 1000
        return session()->uid;
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
                if(uwa()) {
                    widget.callback('onUpdateBody');
                }
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
