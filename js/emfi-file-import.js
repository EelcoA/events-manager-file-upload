// define events here so all function can access it
var events_JSON;


jQuery(document).ready(function($) {


	/*
	*  If field contains the word 'error', add the emu-error class
	*/
	$.views.converters("decorateCellValue", function(val) {
		val = ""+val;
		if(val.toUpperCase().includes("ERROR")){
			returnValue = "<td class='emu-error'>"+val+"</td>";
		} else {
			returnValue = "<td>"+val+"</td>";
		}
		return returnValue; 
	});


	/*
	*  Initialise fields when a new file is choosen
	*/
	jQuery('#file').change(function() {
		$("#events_table").html('');		
		$(".import-events-button").hide();
		$('#emfi-message').html(`<br>`);
	});


	/*
	*  Upload the file and show the content
	*/
	jQuery('#emfi-upload-file-button').click(function(e) {

		jQuery('#emfi-message').html(`<br>`);

		var files = document.getElementById("file").files;
		var data = new FormData();
		data.append('file', files[0]);
		data.append('action', 'emfi_file_upload');

		events = [];

		$.ajax({
			type: "POST",
			url: ajax_object.ajaxurl,
			data: data,
			contentType: false,
			processData: false,
			success: function(responseJson) {
				response = JSON.parse(responseJson);
				if(response.status=='FAILURE'){
					jQuery('#emfi-message').html(`<span style="color: red;">${response.message}</span><br>`);
				} else {

					events_JSON = response.data;
					events = JSON.parse(events_JSON);

					// prepare JSRender template, data and render it
					var tmpl = $.templates("#events_table_template");
					var eventsArray = { "events": events};
					var html = tmpl.render(eventsArray);
					$('#events_table').html(html);
			
					// empty choosen file and make upload buttons visible
					$(".import-events-button").show();
				}
			}
		});
	});    

	/*
	*  Import the events from the file and show the result
	*/
	jQuery('.import-events').click(function(e) {

		var data = new FormData();
		data.append('events', events_JSON);
		data.append('action', 'emfi_import_events');

		$.ajax({
			type: "POST",
			url: ajax_object.ajaxurl,
			data: data,
			contentType: false,
			processData: false,
			success: function(responseJson) {
				response = JSON.parse(responseJson);
				if(response.status=='FAILURE'){				
					jQuery('#emfi-message').html(`<span style="color: red;">${response.message}</span><br>`);
				} else {
					jQuery('#emfi-message').html(`<h2>Import results:</h2>`);

					events = JSON.parse(response.data);

					// prepare JSRender template, data and render it
					var tmpl = $.templates("#events_table_template");
					var eventsArray = { "events": events};
					var html = tmpl.render(eventsArray);
					$('#events_table').html(html);

					// Hide the buttons for importing the events
					$(".import-events-button").hide();
				}
			}
		});

	});    
});