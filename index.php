    <!DOCTYPE html>
    <html>

    <style>
        td.emu-error {
            background-color: #f1c8c7;
        }
        .emu-row-hover:hover {
            background-color: whitesmoke;
        }
    </style>
    <body>
      <h1>Events Manager File Import</h1>

    <div id="emfi-error" class="notice notice-error inline" style="display:none;"/>
    </div>

    <div id="emfi-success" class="notice notice-success inline" style="display:none;"/>
    </div>

    <div id="emfi-message"></div>

    <form class="fileUpload" enctype="multipart/form-data">
        <div id="form-group" class="form-group">
            <label><?php _e('Choose File:'); ?></label>
            <input type="file" id="file" name="file" accept=".csv"/>
            <input class="button-primary" id="emfi-upload-file-button" value="Upload event file" />
        </div>
    </form>

    <br>

    <div id="events_table"></div>

<?php

    if(isset($_SESSION['events']))
        $events = $_SESSION['events'];

    if(!empty($events)) {

        # Show button to process the events
        ?>
        <br>
        <form method="POST" action="<?php echo get_site_url() ?>/wp-admin/admin-post.php">
            <input type="hidden" name="action" value="process_events">
            <input class="button-primary" type="submit" name="processBtn" value="Import events" />
        </form>
        <br>
        <?php

        $row_nr = 0;
        echo "<table class='widefat'>";
        foreach($events as $event) {

	        if ($row_nr == 0) {
		        echo "<thead>";
		        echo "<tr valign='top'>";
		        echo "<th class='row=title'>1</th>";
	        } else {
		        $file_row_nr = $row_nr + 1;
		        echo "<tr valign='top' class='emu-row-hover'>";
		        echo "<td>" . $file_row_nr . "</td>";
	        }

            # loop through all fields
            foreach ($event as $field){
                if ($row_nr == 0)
                    echo "<th class='row=title'>" . $field . "</th>";
                else
                    echo "<td>" . $field . "</td>";
            }
            echo "</tr>";
            if ($row_nr == 0)
                echo "</thead><tbody>";
            $errArray   =   array();
            $row_nr += 1;
        }
        echo "</tbody></table>";

	    ?>
        <br>
        <form method="POST" action="<?php echo get_site_url() ?>/wp-admin/admin-post.php">
            <input type="hidden" name="action" value="process_events">
            <input class="button-primary" type="submit" name="processBtn" value="Import events" />
        </form>
        <br>
	    <?php

    }


    if(isset($_SESSION['results']))
        $results = $_SESSION['results'];

    if(!empty($results)) {

        echo "<H3>Result</H3>";

        $row_nr = 0;
        echo "<table class='widefat'>";
        foreach($results as $result) {

            if ($row_nr == 0) {
	            echo "<thead>";
	            echo "<tr valign='top'>";
            } else
                echo "<tr valign='top' class='emu-row-hover'>";

            # loop through all fields
            foreach ($result as $field){
                if ($row_nr == 0)
                    echo "<th class='row=title'>" . $field . "</th>";
                else
                    if (stristr($field, "error"))
                        echo "<td class='emu-error'>" . $field . "</td>";
                    else
                        echo "<td>" . $field . "</td>";
            }

            echo "</tr>";
            if ($row_nr == 0)
                echo "</thead><tbody>";
            $errArray   =   array();
            $row_nr += 1;
        }
        echo "</tbody></table>";
	    $_SESSION["results"] = null;
    }

?>

    </body>
    </html>
