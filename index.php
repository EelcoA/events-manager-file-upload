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

    <div id="emfi-message" rows="4" cols="80">
        <br>
    </div>

    <form class="fileUpload" enctype="multipart/form-data">
        <div id="form-group" class="form-group">
            <label><?php _e('Choose File:'); ?></label>
            <input type="file" id="file" name="file" accept=".csv"/>
            <input class="button-primary" 
                   id="emfi-upload-file-button" 
                   title="This does NOT import the events yet, just shows them"
                   value="Upload file and show events" />
        </div>
    </form>

    <div class="import-events-button" hidden="hidden">
        <br>
        <input class="button-primary import-events" 
               title="Import the shown events in the database, skipping events that already exist with same location, title, date and time."
               name="processBtn" 
               value="Import events" />
        <br>
    </div>

    <br>
    <div id="events_table"></div>

    <div class="import-events-button" hidden="hidden">
        <br>
        <input class="button-primary import-events" 
               title="Import the shown events in the database, skipping events that already exist with same location, title, date and time."
               name="processBtn" 
               value="Import events" />
        <br>
    </div>


<?php

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
