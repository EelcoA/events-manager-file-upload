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
    <h1>Events Manager File Import</h1>
    <h4>A non-official plugin to upload files with events into Event Manager.</h4>
    <p>
       Files must be comma seperated files, and contain a header row. 
       See this <a href='https://github.com/EelcoA/event-manager-file-import/blob/master/doc/example_input_file.csv'>github page</a>
       for an example file.
       <br>
       <br>Project website and documentation: <a href='https://github.com/EelcoA/event-manager-file-import'>https://github.com/EelcoA/event-manager-file-import</a>.
       <br>See this <a href='https://github.com/EelcoA/fabriekscraper'>web-scraping project</a> for an example how to create these files from a website with events, written in Python, using <a href='https://www.scrapy.org'>Srapy</a>.
    </p>

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
