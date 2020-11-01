<?php
/*
Plugin Name: Events Manager - File Import
Version: 1.0.1
Plugin URI:
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

	$uploadResult = uploadFile($_FILES['file']);
	if($uploadResult['status']!="SUCCESS"){
		$result['status']  = "FAILURE";
		$result['message'] = 'Error while uploading file: ' . $uploadResult['message'];
		echo json_encode($result);
		wp_die();
	}

	$events = emfi_process_file($uploadResult['file']);
	$data = json_encode($events);
	$result['status']  = "SUCCESS";
	$result['message'] = 'File has been successfully uploaded';
	$result['data']    = $data;
	// $sample_array = array();
	// $sample_array[] = ["Hello1", "to", "you"];
	// $sample_array[] = ["Hello2", "to", "you"];
	echo json_encode($result);

	wp_die();
}


add_action( 'admin_post_upload_file', 'emfi_admin_upload_file' );

function emfi_admin_upload_file()
{

	$error = array();
	$message = array();

    // Handle request then generate response using echo or leaving PHP and using HTML

    $plugin_dir = plugin_dir_path( __FILE__ );
    $settings   = false;
    $target_dir = (!empty($settings['target_dir'])) ? $settings['target_dir'] : $plugin_dir . "uploaded/";
    $iName      = (!empty($settings['input'])) ? $settings['input'] : "fileToUpload";
    $filter     = (!empty($settings['filter']) && is_array($settings['filter'])) ? $settings['filter'] : array("txt", "csv");

    // Create output directory when it doesn't exist yet
    if (!is_dir($target_dir))
        mkdir($target_dir, 0755, true);

    // Empty output directory preventing duplicates and getting rid of garbage is always a good thing
	$files = glob($target_dir . "*"); // get all file names
	foreach($files as $file){ // iterate files
		if(is_file($file))
			unlink($file); // delete file
	}

    $filename = trim(basename($_FILES[$iName]["name"]));
	if (empty($filename))
		$error[] = array("error" => true, "details" => "No file selected");
	else {

	    $target_file = str_replace("//", "/", $target_dir . $filename);
	    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));


	    // Check if file already exists (should not be the case, but in case deletion had failed...
	    if (file_exists($target_file)) {
		    if (unlink($target_file)) // delete file, try it again
			    $message[] = array("details" => "Previous file with that name has been removed: " . $target_file);
		    else
			    $error[] = array("error" => true, "details" => "Deleting previous file with that name has failed");
	    }

	    // Check file size
	    if ($_FILES["fileToUpload"]["size"] > 500000) {
	        $error[] = array("error" => true, "details" => "Sorry, your file is too large.");
	    }

	    // Allow certain file formats
	    if (!in_array($imageFileType, $filter)) {
	        $error[] = array("error" => true, "details" => "Sorry, only csv & txt files are allowed.<br>");
	    }

	    $events = [];
	    if (empty($error)) {
	        // if everything is ok, try to upload file
	        if (move_uploaded_file($_FILES[$iName]["tmp_name"], $target_file)) {
	            try{
	            } catch (Exception $e){
	                $error[] = array("error" => true, "details" => "Something went wrong while processing file " .
	                    basename($_FILES[$iName]["name"]) . ": ". $e->getMessage());
	            }

	        } else {
	            $error[] = array("error" => true, "details" => "The file " .
	                                                           basename($_FILES[$iName]["name"]) .
	                                                           "failed to upload." );
	        }
	    }
		$_SESSION["events"] = $events;
	}
    $_SESSION["errors"] = array_merge($message, $error);
    wp_redirect( $_SERVER['HTTP_REFERER'] );
    exit();
}

/**
 * Process the file into rows with events to display on the screen
 * @param $filename
 * @return array
 * 
 * TODO: make it monkey-proof for invalid file and file types
 */
function emfi_process_file($filename){

	$events = array();
	$i = 0;

    // Open the file for reading
    if (($h = fopen("{$filename}", "r")) !== FALSE)
    {
        // Each line in the file is converted into an individual array that we call $data
		// The items of the array are comma separated
        while (($data = fgetcsv($h, 3000, ",")) !== FALSE)
        {
			array_unshift($data, $i);
			// Each individual array is being pushed into the nested array
			$events[] = $data;
			$i+=1;
        }

        // Close the file
        fclose($h);
    }

    return $events;
}


/*
 * Process the array with events, creating POST and EVENTS
 * This is called when the user presses the 'process_events' button on the upload page
 */
add_action( 'admin_post_process_events', 'emfi_admin_process_events' );

function emfi_admin_process_events(){
	$messages = [];
	$results = [];
    $event_rows = $_SESSION["events"];
    $_SESSION["events"] = null;

    $row_nr = 0;
    foreach ($event_rows as $event_row) {

    	/*
    	 * Add the smaller fields (meaning all except post_excerpt and post_content) to the result row to display later
    	 * including the result (message about event creation or error).
    	 */
	    $display_row_nr = $row_nr + 1;
    	$result_row = array($display_row_nr, $event_row[0], $event_row[1], $event_row[2],
		    $event_row[3], $event_row[4], $event_row[7], $event_row[8]);
    	if ($row_nr == 0)
    		$result_row[] = "Result";

    	try {

		    if ( $row_nr > 0 ) {  // skip first row with headers

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
		    $result_row[] = "Error creating event: <strong>" . $e->getMessage() . "</strong>";

	    } finally {
		    $results[] = $result_row;
		    $row_nr += 1;
	    }
    }
	$messages[] = array("details" => "Events are processed, see results below.");
	$_SESSION["errors"] = $messages;
    $_SESSION["results"] = $results;
    wp_redirect( $_SERVER['HTTP_REFERER'] );
    exit();
}

// --------------- [ Upload Function ] -----------------------
function uploadFile($file) {

	$data                       =           array();

	$source_path                =           $file['tmp_name'];

	$file_name                  =           $file['name'];
	
	$file_extension             =           pathinfo($file_name, PATHINFO_EXTENSION);

	$target_file_name           =           $file_name;

    $plugin_dir                 =           plugin_dir_path( __FILE__ );
    $target_dir                 =           $plugin_dir . "uploaded/";
	$target_filepath            =           $target_dir.$target_file_name;

	$file_type                  =           $file['type'];

	// ------------ [ File Validation ] --------------------------           

	if($file_type != "text/csv" && $file_type != "text/txt" ){
		$data['status']         =           "FAILED";
		$data['message']        =           "Invalid file type: ".$file_type." (File type only txt and csv allowed)";
		return $data;
	}

	if($file['size']  > 2048000) {
		$data['status']         =           "FAILED";
		$data['message']        =           "File size is larger than 2 MB";
		return $data;
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

		$data['status']     =           "SUCCESS";
		$data['message']    =           "File uploaded successfully to: ".$target_filepath ;
		$data['file']       =           $target_filepath;
	} else {
		$data['status']     =           "FAILED";
		$data['message']    =           "File uploaded failed.";
		$data['file']       =           $target_filepath;
	}
	
	return $data;
}
