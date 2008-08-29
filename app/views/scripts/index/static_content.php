<div style="display: none;">

    <?
    function output_standardfilter($id, $obj = "") {
    ?>
        <div id="<?=$id?>_toolbar_div" class="toolbar_item">
            Filter By
            <select id="<?=$id?>_grid_type_selector" onchange="<?=$id?>_obj<?=$obj?>.changeFilter(this.value, 'gridtype');">
            <option value="all">(All Grid Types)</option>
        <?
            $gridtypes = new GridTypes();
            $types = $gridtypes->fetchAll();
            foreach($types as $type) {
                $gid = $type->grid_type_id;
                $desc = $type->short_name;
                echo "<option value=\"$gid\">$desc</option>\n";
            }
        ?>
            </select>

            <select id="<?=$id?>_service_type_selector" onchange="<?=$id?>_obj<?=$obj?>.changeFilter(this.value, 'servicetype');">
            <option value="all">(All Service Types)</option>
        <?
            $servicetypes = new ServiceTypes();
            $types = $servicetypes->fetchAll();
            foreach($types as $type) {
                $id = $type->service_id;
                $desc = $type->description;
                echo "<option value=\"$id\">$desc</option>\n";
            }
        ?>
            </select>
        </div>
    <?
    }
    output_standardfilter("current_overview");
    
    ?>

    <div id="vomatrix_toolbar_div" class="toolbar_item">
        Filter By
        <select id="vomatrix_grid_type_selector" onchange="vo_matrix_obj.changeFilter(this.value, 'gridtype');">
        <option value="all">(All Grid Types)</option>
    <?
        $gridtypes = new GridTypes();
        $types = $gridtypes->fetchAll();
        foreach($types as $type) {
            $id = $type->grid_type_id;
            $desc = $type->short_name;
            echo "<option value=\"$id\">$desc</option>\n";
        }
    ?>
        </select>

        <select id="vomatrix_service_type_selector" onchange="vo_matrix_obj.changeFilter(this.value, 'servicetype');">
        <option value="all">(All Service Types)</option>
    <?
        $servicetypes = new ServiceTypes();
        $types = $servicetypes->fetchAll();
        foreach($types as $type) {
            $id = $type->service_id;
            $desc = $type->description;
            echo "<option value=\"$id\">$desc</option>\n";
        }
    ?>
        </select>
    </div>


    <div id="service_type_selector_div" class="toolbar_item">
        Filter By
        <select id="current_detail_grid_type_selector" onchange="current_detail_obj.grid.changeFilter(this.value, 'gridtype');">
        <option value="all">(All Grid Types)</option>
    <?
        $gridtypes = new GridTypes();
        $types = $gridtypes->fetchAll();
        foreach($types as $type) {
            $id = $type->grid_type_id;
            $desc = $type->short_name;
            echo "<option value=\"$id\">$desc</option>\n";
        }
    ?>
        </select>

        <select id="current_detail_service_type_selector" onchange="current_detail_obj.grid.changeFilter(this.value, 'servicetype');">
        <option value="all">(All Service Types)</option>
    <?
        $servicetypes = new ServiceTypes();
        $types = $servicetypes->fetchAll();
        foreach($types as $type) {
            $id = $type->service_id;
            $desc = $type->description;
            echo "<option value=\"$id\">$desc</option>\n";
        }
    ?>
        </select>
    </div>

    <div id="history_service_type_selector_div" class="toolbar_item">
        Filter By
        <select id="history_grid_type_selector" onchange="history_detail_obj.grid.changeFilter(this.value, 'gridtype');">
        <option value="all">(All Grid Types)</option>
    <?
        $gridtypes = new GridTypes();
        $types = $gridtypes->fetchAll();
        foreach($types as $type) {
            $id = $type->grid_type_id;
            $desc = $type->short_name;
            echo "<option value=\"$id\">$desc</option>\n";
        }
    ?>
        </select>

         <select id="history_service_type_selector" onchange="history_detail_obj.grid.changeFilter(this.value, 'servicetype');">
        <option value="all">(All Service Types)</option>
    <?
        $servicetypes = new ServiceTypes();
        $types = $servicetypes->fetchAll();
        foreach($types as $type) {
            $id = $type->service_id;
            $desc = $type->description;
            echo "<option value=\"$id\">$desc</option>\n";
        }
    ?>
        </select>
    </div>

    <div id="start-div">
        <div style="float:left;" ><img src="images/logo_big.png" /></div>
        <div style="margin-left:150px;">
            <h2>Welcome <?=user()->getPersonName();?>!</h2>
            <p>This is just a place holder for some brief explanation of what <?=config()->app_name?> is, and how to use it. Yes, I am not using this logo for real. Please suggest more appropriate logo.
            </p>
            <p>Select a page from the nativation menu to the left to begin.</p>
            
            <h2>Supported Browsers</h2>
            <p>We currently support following browsers.</p>
            <ul>
                <li>IE 6</li>
                <li>IE 7</li>
                <li>Firefox 3.0.1</li>
                <li>Safari 3.1.2</li>
                <li>Opera 9.51</li>
            </ul>
            <p>Please submit your bug report <a href="http://code.google.com/p/rsv/issues/entry">here</a></p>

        </div>
    </div>

    <div id="home_start-details">
        <p class="details-info">When you select a page from the navigation menu, additional details will display here.</p>
    </div>
    <div id="vo_matrix-details">
        <p class="details-info">TODO: Add help info for VO Matrix</p>
        <p><a href="<?=base()?>?page=vo_matrix">Direct Link</a> to this page </p>
    </div>
    <div id="vo_group-details">
        <p class="details-info">TODO: Add help info for VO Matrix Group</p>
        <p><a href="<?=base()?>?page=vo_group">Direct Link</a> to this page </p>
    </div>

    <div id="current_overview-details">
        <h2>Overview</h2>
        <p>Shows current status of all Open Science Grid around the world.</p>

        <p>You can drag and drop the map to change position, and use your mouse wheel to zoom in / out.</p>

        <h2>Links</h2>
        <p><a href="<?=base()?>?page=current_overview">Direct Link</a> to this page </p>
        <p><a href="http://vors.grid.iu.edu/cgi-bin/index.cgi" target="_blank">VORS</a></p>
    </div>

    <div id="current_detail-details">
        <h2>Metric Detail</h2>
        <p>Shows the latest metrics gathered by various RSV (Resource and Service Validation) probes.</p>
        <p>Select the resource in the upper panel to show details in the lower panel.</p>
        <h3>Legend</h3>
        <div class="legend">
            <p><img src="images/status_ok.png" align="middle">&nbsp;&nbsp;<b>OK</b></p>
            <p class="sub">Normal or operational condition.</p>
        </div>
        <div class="legend">
            <p><img src="images/status_warning.png" align="middle">&nbsp;&nbsp;<b>Warning</b></p>
            <p class="sub">TODO - what does Warning mean?</p>
        </div>
        <div class="legend">
            <p><img src="images/button_cancel.png" align="middle">&nbsp;&nbsp;<b>Critical</b></p>
            <p class="sub">TODO - what does Critical mean?</p>
        </div>
        <div class="legend">
            <p><img src="images/status_unknown.png" align="middle">&nbsp;&nbsp;<b>Unknown / No Data</b></p>
            <p class="sub">Probe could not gather necessary information to determine its status, or probe is not installed on this CE.</p>
        </div>
        <p>Please be aware that the resource which has never posted any RSV metricdata will not be displayed on the resource list</p>
        <h2>Links</h2>
        <p><a href="<?=base()?>?page=current_detail">Direct Link</a> to this page </p>

    </div>

    <div id="history_overview-details">
        <p class="details-info">TODO</p>
        <p><a href="<?=base()?>?page=history_overview">Direct Link</a> to this page </p>
    </div>

</div>


