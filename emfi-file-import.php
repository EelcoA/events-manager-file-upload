<?php
/*
Plugin Name: Events Manager - File Import
Version: 1.1.0
Plugin URI: https://github.com/EelcoA/event-manager-file-import
Description: Import CSV file with events into Events Manager for Wordpress. Events for the same location/date/time are skipped. So if you want to replace the existing one with a new one, you first have to delete the current event. 
Author: Eelco Aartsen, AESSET IT
Author URI: https://www.aesset.nl
*/
include("emfi-functions.php");

// TODO: internationalization

global $wpdb;

// Hook the 'admin_menu' action hook, run the function named
add_action('admin_menu', 'emfi_Add_My_Admin_Link');


// Add a new top level menu link to the ACP
function emfi_Add_My_Admin_Link()
{
    add_menu_page(
        'Import Events', // Title of the page
        'Import Events', // Text to show on the menu link
        'manage_options', // Capability requirement to see the link
        'event-manager-file-import/index.php' // The 'slug' - file to display when clicking the link
    );
}

// Make JSRender javascript library available
add_action( 'admin_enqueue_scripts', 'emfi_add_JSRender' );
function emfi_add_JSRender(){
	wp_enqueue_script('emfi_jsrender', 
	'https://cdnjs.cloudflare.com/ajax/libs/jsrender/1.0.8/jsrender.min.js');
}

// Make the javascript file available for admin pages
add_action( 'admin_enqueue_scripts', 'emfi_scripts' );
function emfi_scripts() {
	wp_register_script('ajaxHandle', 
						plugin_dir_url( __FILE__ ) . '/js/emfi-file-import.js',
						array('jquery'), 
						false, 
						true);	
	wp_localize_script( 
		'ajaxHandle',
		'ajax_object',
		array( 'ajaxurl' => admin_url('admin-ajax.php') ) 
	);
	wp_enqueue_script('ajaxHandle');
}


/*
*   upload the files, read content and return as json data
*/

add_action('wp_ajax_emfi_file_upload', 'emfi_file_upload_callback');
add_action('wp_ajax_nopriv_emfi_file_upload','emfi_file_upload_callback');

function emfi_file_upload_callback() {

	$result = [];

	if(!isset($_FILES)){
		$result['status']  = "FAILURE";
		$result['message'] = "No file choosen";
		echo json_encode($result);
		wp_die();
	}

	if(empty($_FILES['file'])) {
		$result['status']  = "FAILURE";
		$result['message'] = "No file choosen";
		echo json_encode($result);
		wp_die();
	}

	$uploadResult = emfi_file_upload($_FILES['file']);

	if($uploadResult['status']!="SUCCESS"){
		$result['status']  = "FAILURE";
		$result['message'] = 'Error while uploading file: ' . $uploadResult['message'];
		echo json_encode($result);
		wp_die();
	}

	$emfi_events = emfi_get_events_from_file($uploadResult['file']);

	$data = json_encode($emfi_events);
	$result['status']  = "SUCCESS";
	$result['message'] = 'File has been successfully uploaded';
	$result['data']    = $data;
	echo json_encode($result);

	wp_die();
}


/**
 * Process the file into rows with events to display on the screen
 * @param $filename
 * @return array
 * 
 * TODO: make it monkey-proof for invalid file and file types
 */
function emfi_get_events_from_file($filename){

	$events = array();
	$rown_number = 0;

    // Open the file for reading
    if (($event_file = fopen("{$filename}", "r")) !== FALSE)
    {
        // Each line in the file is converted into an individual array that we call $data
		// The items of the array are comma separated
        while (($rowdata_array = fgetcsv($event_file, 3000, ",")) !== FALSE)
        {
			// add rownumber to the row data
			array_unshift($rowdata_array, $rown_number);

			// Each individual array is being pushed into the nested array
			$events[] = $rowdata_array;
			$rown_number+=1;
        }

        fclose($event_file);
    }

    return $events;
}


/*
*   Callback function for importing events
*/
add_action('wp_ajax_emfi_import_events', 'emfi_import_events_callback');
add_action('wp_ajax_nopriv_emfi_import_events','emfi_file_upload_callback');

function emfi_import_events_callback(){

	$result = [];

//  First check the incoming data, expecting an entry 'events' with String data in $_POST

	if(!isset($_POST)){
		$result['status']  = "FAILURE";
		$result['message'] = "Programming error, no $_POST";
		echo json_encode($result);
		wp_die();
	}

	if(empty($_POST['events'])) {
		$result['status']  = "FAILURE";
		$result['message'] = "Programming error, no 'events' in $_POST";
		echo json_encode($result);
		wp_die();
	}

	$events_to_import_json = $_POST['events'];
	$datatype = gettype($events_to_import_json);
	if(!datatype=="string"){
		$result['status']  = "FAILURE";
		$result['message'] = 'Programming error: events_to_import_json is not a string but a '.$datatype;
		echo json_encode($result);
		wp_die();
	}

//  Strip the JSON from redundant slashes and decode it

	$events_to_import_json_with_correct_html = stripslashes($events_to_import_json);
	$events_to_import = json_decode($events_to_import_json_with_correct_html);

//  Check if the decoded JSON gave us a non-empty Array with Arrays

	$datatype = gettype($events_to_import);
	if(!$datatype=='array'){
		$result['status']  = "FAILURE";
		$result['message'] = 'Programming error: events_to_import is not an array but a '.$datatype;
		echo json_encode($result);
		wp_die();
	}
	if(sizeof($events_to_import)==0){
		$result['status']  = "FAILURE";
		$result['message'] = 'Programming error: events_to_import is an empty array, size='.sizeof($events_to_import);
		echo json_encode($result);
		wp_die();
	}
	if(!gettype($events_to_import[0])=='array'){
		$result['status']  = "FAILURE";
		$result['message'] = 'Programming error: events_to_import[0] is not an arrays';
		echo json_encode($result);
		wp_die();
	}

//  Call the importing function which returns the results in an Array of Arrays

	$import_result = emfi_import_events($events_to_import);


//  When no SUCCESS, report the error

	if($import_result['status']!="SUCCESS"){
		$result['status']  = "FAILURE";
		$result['message'] = 'Error while importing events: ' . $import_result['message'];
		echo json_encode($result);
		wp_die();
	}

	$imported_events = $import_result['result_details'];

//  Some checking of things that shouldn't happen, but you never know 

	if(!gettype($imported_events)=='array'){
		$result['status']  = "FAILURE";
		$result['message'] = 'Programming error: imported_events is not an array';
		echo json_encode($result);
		wp_die();
	}
	if(sizeof($imported_events)==0){
		$result['status']  = "FAILURE";
		$result['message'] = 'Programming error: imported_events is an empty array';
		echo json_encode($result);
		wp_die();
	}
	if(!gettype($imported_events[0])=='array'){
		$result['status']  = "FAILURE";
		$result['message'] = 'Programming error: imported_events[0] is not an arrays';
		echo json_encode($result);
		wp_die();
	}

//  Encode the resulting array and return it 

	$data = json_encode($imported_events);
	$result['status']  = "SUCCESS";
	$result['message'] = 'Events have been successfully imported into the database';
	$result['data']    = $data;
	echo json_encode($result);

	wp_die();

}

/*
 *  Import events into the database and return an array with result data back
 */
function emfi_import_events($event_rows){
	$result = array();

//  Check the number of columns

	$row0 = $event_rows[0];
	if(sizeof($row0)!=10){
		if(sizeof($row0)!=0){
			$num_columns=sizeof($row0)-1;
		} else {
			$num_columns=0;
		}
		$result['status']  = "FAILURE";
		$result['message'] = 'File should contain 9 columns but it has ' . $num_columns . ' columns';
		return $result;
	}


	$result_details = [];

    $row_nr = 0;
    foreach ($event_rows as $event_row) {

    	/*
    	 * Add the smaller fields (meaning all except post_excerpt and post_content) 
		 * to the result row to display later including the result 
		 * (= message about event creation or error).
    	 */
		$result_row = array($event_row[0],  // rown nr
							$event_row[1],  // event_start_date
							$event_row[2],  // event_start_time
							$event_row[3],  // event_end_date
							$event_row[4],  // event_end_time
							$event_row[5],  // event_name
							$event_row[8],  // location_slug
							$event_row[9]); // category-slug

    	try {

			// skip first row with headers
			if ($row_nr == 0) {      
				$result_row[] = "Result";
				
			} else {

			    $event = emfi_create_EM_Event_from_row($event_row);

			    if (emfi_event_exists($event)) {
				    throw new Exception( "Event already exists with that location/title/date/time." );
			    }

			    // Save the Event
			    if ( ! $event->save() ) {
				    throw new Exception( 'Something went wrong saving your event.' );
			    }

			    // Success, so add that to the array to display in the results!
			    $result_row[] = "Event created.";
		    }
	    } catch (Exception $e){
    		// Add an error message to display in the results
		    $result_row[] = "Error creating event:<br><strong>" . $e->getMessage() . "</strong>";

	    } finally {
		    $result_details[] = $result_row;
		    $row_nr += 1;
	    }
    }

	$result['status']         = "SUCCESS";
	$result['message']        = "Event import succeeded.";
	$result['result_details'] =  $result_details;

	return $result;
}

/*
*   file upload 
*/
function emfi_file_upload($file) {

	$result                     =           array();

	$file_name                  =           $file['name'];
	$source_path                =           $file['tmp_name'];
	$file_type                  =           $file['type'];
	$file_extension             =           pathinfo($file_name, PATHINFO_EXTENSION);
	$target_file_name           =           $file_name;
    $plugin_dir                 =           plugin_dir_path( __FILE__ );
    $target_dir                 =           $plugin_dir . "uploaded/";
	$target_filepath            =           $target_dir.$target_file_name;

	// ------------ [ File Validation ] --------------------------           

	if($file_type != "text/csv" && $file_type != "text/txt" ){
		$result['status']         =           "FAILED";
		$result['message']        =           "Invalid file type: ".$file_type." (File type only txt and csv allowed)";
		return $result;
	}

	if($file['size']  > 2048000) {
		$result['status']         =           "FAILED";
		$result['message']        =           "File size is larger than 2 MB";
		return $result;
	}

    // ------------- [ Check and/or create output dir ] ---------------

	if (!is_dir($target_dir))
        mkdir($target_dir, 0755, true);

	// ------------- [ Empty output dir ] ------------------------------
	
	$existing_files = glob($target_dir . "*");
	foreach($existing_files as $existing_file){ 
		if(is_file($existing_file))
			unlink($existing_file); // delete file
	}

	// ------------------ [ File upload ] ---------------

	if(move_uploaded_file($source_path, $target_filepath)) {

		$result['status']     =           "SUCCESS";
		$result['message']    =           "File uploaded successfully to: ".$target_filepath ;
		$result['file']       =           $target_filepath;
	} else {
		$result['status']     =           "FAILED";
		$result['message']    =           "File uploaded failed.";
		$result['file']       =           $target_filepath;
	}
	
	return $result;
}

/*
*  Return a String representation form an Array with Arrays
*/
function subArraysToString($ar, $sep = ', ') {
    $str = '';
    foreach ($ar as $val) {
        $str .= implode($sep, $val);
        $str .= $sep; // add separator between sub-arrays
    }
    $str = rtrim($str, $sep); // remove last separator
    return $str;
}