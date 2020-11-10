<!DOCTYPE html>
<html>
<head>
    <style>
        td.emu-error {
            background-color: #f1c8c7;
        }
        .emu-row-hover:hover {
            background-color: whitesmoke;
        }
    </style>
</head>
    
<body>
    <h1>Events Manager File Upload</h1>
    <h4>A non-official 'Events Manager' add-on to import events from a CSV file.</h4>
    Project website:<a target="_blank" rel="noopener noreferrer" 
           href='https://github.com/EelcoA/events-manager-file-upload'>
           https://github.com/EelcoA/events-manager-file-upload</a>.
    <p>
       <br>
       </p>
    <h2>Step 1: Create CSV file with events</h2>
    <p>
       The file can be created manualy or by using a (web scraping) program. 
       The file must be comma seperated and must contain a header row. 
       See this <a target="_blank" rel="noopener noreferrer" 
       href='https://github.com/EelcoA/events-manager-file-upload/blob/master/doc/example_input_file.csv'>
       github page</a> for an example file.
       <br>See this <a target="_blank" rel="noopener noreferrer" 
           href='https://github.com/EelcoA/fabriekscraper'>web-scraping project</a> 
           for an example how to create these files from a website with events, written 
           in Python, using <a target="_blank" rel="noopener noreferrer" 
           href='https://www.scrapy.org'>Scrapy</a>.
    </p>

    <h2>Step 2: Upload file with events</h2>
    <p>
    This step only shows the content of the file on the screen. It doesn't import anything yet.
    Select a file and press the 'upload' button.
    </p>
    <form class="fileUpload" enctype="multipart/form-data">
        <div id="form-group" class="form-group">
            <label><?php _e('Select file:'); ?></label>
            <input type="file" id="file" name="file" accept=".csv"/>
            <input class="button-primary" 
                   id="emfu-upload-file-button" 
                   title="This does NOT import the events yet, just shows them"
                   value="Upload file" />
        </div>
    </form>

    <div class="import-events-button" hidden="hidden">
       <h2>Step 3: Check shown events</h2>
       <p>Events from the file are shown below. Before importing them, check them to see if they are ok. 
       </p>
       <h2>Step 4: Import the events by pressing the button</h2>
       <p>
       Events with already existing Name (case sensitive), Start date, Start time and Location are skipped.
       </p>
        <br>
        <input class="button-primary import-events" 
               title="Import the shown events in the database, skipping events that already exist with same location, title, date and time."
               name="processBtn" 
               value="Import events" />
        <br>
    </div>

    <br>
    <div id="emfu-message" rows="4" cols="80">
        <br>
    </div>
    <br>
    <div id="events_table"></div>
    <script id="events_table_template" type="text/x-jsrender">
        <div>
            <table class='widefat'>
            {{for events}}
                {{if #getIndex() === 0}}
                    <thead>
                        <tr class='row-title valign='top'>
                            {{for}}
                               <td>{{:}}</td>
                            {{/for}}
                        </tr>
                    </thead>
                {{else}}
                    <tbody>
                        {{if #getIndex() % 2 == 0}}
                            <tr valign='top' class='emu-row-hover alternate'>
                        {{else}}
                            <tr valign='top' class='emu-row-hover'>
                        {{/if}}
                            {{for}}
                              {{decorateCellValue:}}
                            {{/for}}
                        </tr>
                    </tbody>
                {{/if}}
            {{/for}}
            </table>
        </div>
    </script>

    <br>

    <div class="import-events-button" hidden="hidden">
        <br>
        <input class="button-primary import-events" 
               title="Import the shown events in the database, skipping events that already exist with same location, title, date and time."
               name="processBtn" 
               value="Import events" />
        <br>
    </div>

</body>

</html>
