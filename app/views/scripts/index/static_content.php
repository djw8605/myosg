<div style="display: none;">

    <div id="service_type_selector_div" class="toolbar_item">
        Filter By
        <select id="service_type_selector" onchange="current_detail.grid.changeFilter(this.value);">
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
        <select id="history_service_type_selector" onchange="history_detail.grid.changeFilter(this.value);">
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

    <div id="current_overview-details">
        <h2>Overview</h2>
        <p>Shows current status of all Open Science Grid around the world.</p>

        <p>You can drag and drop the map to change position, and use your mouse wheel to zoom in / out.</p>

        <hr/>
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
    </div>

    <div id="history_overview-details">
        <p class="details-info">TODO</p>
    </div>

</div>


