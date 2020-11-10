# Events Manager File Import

A non-official 'Events Manager' add-on to import events from a CSV file,
Github page: https://github.com/EelcoA/events-manager-file-upload

### Dependencies
Wordpress, latest tested version: 5.5.3
Event Manager, latest tested version: 5.9.8.1
JwRender, 1.0.8

### Warning
This plugin is NOT created by the people of Events Manager (https://wordpress.org/plugins/events-manager/). 
It is created because we needed a simple but efficient csv file import function.

### Installation
As long as this plugin is not available in the Wordpress plugin repositories, install it by:
 - download the latest zip from the Github releases page of the project: https://github.com/EelcoA/events-manager-file-upload/releases 
 - go to 'Plugins' in the admin menu
 - upload the downloaded zip file
 - activate the plugin
 - this creates a 'Import Events' entry in the Admin menu
 
### Functionality
The user can upload a .csv file with events to be imported. Before importing the events, 
they are shown to the user. Then the user gives the command to import the data. 
The result is shown to the user after importing the events. It shows which events were
created and which ones failed, including the reason.

#### Input file
A .csv file containing the following fields in this order:

Field             | Format      | Required     |
| :---------------- | :---------- | :----------- |
| event_start_date  | YYYY-MM-DD  |  yes   |
| event_start_time  | hh:mm:ss    |  yes   |
| event_end_date    | YYYY-MM-DD  |  no, if empty, start date is taken |
| event_end_time    | hh:mm:ss    |  no, if empty, start time is taken |
| event_name        | text        |  yes   |
| post_excerpt      | text        |  no    |
| post_content      | text        |  yes   |
| location-slug     | text        |  yes, must exist   | 
| category-slug     | text        |  yes, must exist  |

##### checks
1) duplicates - when an event already exists with the same location, event_name, start date and start time the event is 
not created and an error is shown in the result page.

##### Screenprints

1) Browse for csv file to upload
![alt text](doc/img/events-manager-file-upload-img01_browse_for_csv_file.jpg)

2) Show preview uploaded events from file
![alt text](doc/img/events-manager-file-upload-img02_preview_uploaded_events_file.jpg)

3) Show result processing the events with some errors and some successfull creations.
![alt text](doc/img/events-manager-file-upload-img03_result_processing_with_some_errors.jpg)

##### Example file
There is a file with example data in the doc directory.

That file is created by a webpage scraper, written in python, using Scrapy. If you want to see how that works, see my other github project: https://github.com/EelcoA/fabriekscraper

    
#### Author
- Eelco Aartsen
- eelco@aesset.nl
- AESSET IT - The Netherlands
- www.aesset.nl



