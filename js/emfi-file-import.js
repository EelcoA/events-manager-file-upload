// define events here so all function can access it
var events_JSON;

jQuery(document).ready(function($) {


	jQuery('#file').change(function() {
		$("#events_table").html('');		
		$(".import-events-button").hide();
		$('#emfi-message').html(`<br>`);
	});

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

					// Table preperations
					var parentDiv = $("#events_table");
					parentDiv.html("");
					var aTable = $("<table class='widefat'>")
						.appendTo(parentDiv);
					var rowCount = events.length;
					var colmCount = events[0].length;

					// echo "<thead>";
					// echo "<tr valign='top'>";
					// echo "<th class='row=title'>1</th>";
	
					// Add the header
					for (var k = 0; k < 1; k++) {
						var fragTrow = $("<thead>")
						.appendTo($("<tr>", {"valign": "top"}))
						.appendTo(aTable);
						for (var j = 0; j < colmCount; j++) {
							$("<th>", {
								"valign": "top",
								"class": "row-title"
							}).appendTo(fragTrow).html(events[k][j]);
						}
					}

					// Add the rows
					for (var i = 1; i < rowCount; i < i++) {
						if(i % 2 == 0) {
							rowClass = "emu-row-hover alternate";
						} else {
							rowClass = "emu-row-hover";
						} 
						messageToUpper = events[i][colmCount-1].toUpperCase();
						if(messageToUpper.includes("ERROR")){
							rowClass += " emu-error";
						}
						var fragTrow = $("<tr>", {
							"class": rowClass
						}).appendTo(aTable);
						for (var j = 0; j < colmCount; j++) {
							$("<td>")
							.appendTo(fragTrow)
							.html(events[i][j]);
						}
					}

					// empty choosen file and make upload buttons visible
					$(".import-events-button").show();
				}
			}
		});
	});    

	jQuery('.import-events').click(function(e) {

		var data = new FormData();
		data.append('events', events_JSON);
		data.append('action', 'emfi_import_events');
		// alert(events_JSON);

		$.ajax({
			type: "POST",
			url: ajax_object.ajaxurl,
			data: data,
			// dataType: "json",
			contentType: false,
			processData: false,
			success: function(responseJson) {
				response = JSON.parse(responseJson);
				if(response.status=='FAILURE'){				
					jQuery('#emfi-message').html(`<span style="color: red;">${response.message}</span><br>`);
				} else {
					events = JSON.parse(response.data);

					// Table preperations
					var parentDiv = $("#events_table");
					parentDiv.html("");
					var aTable = $("<table class='widefat'>")
						.appendTo(parentDiv);
					var rowCount = events.length;
					var colmCount = events[0].length;

					// echo "<thead>";
					// echo "<tr valign='top'>";
					// echo "<th class='row=title'>1</th>";
	
					// Add the header
					for (var k = 0; k < 1; k++) {
						var fragTrow = $("<thead>")
						.appendTo($("<tr>", {"valign": "top"}))
						.appendTo(aTable);
						for (var j = 0; j < colmCount; j++) {
							$("<th>", {
								"valign": "top",
								"class": "row-title"
							}).appendTo(fragTrow).html(events[k][j]);
						}
					}
					
					indexLastColumn = colmCount -1;
					// Add the rows
					for (var i = 1; i < rowCount; i < i++) {
						if(i % 2 == 0) {
							rowClass = "emu-row-hover alternate";
						} else {
							rowClass = "emu-row-hover";
						} 
						var fragTrow = $("<tr>", {
							"class": rowClass
						}).appendTo(aTable);
						for (var j = 0; j < colmCount; j++) {
							cellClass = "";
							if (j == indexLastColumn){
								messageToUpper = events[i][j].toUpperCase();
								if(messageToUpper.includes("ERROR")){
									cellClass = "emu-error";
								}
							}
							$("<td>", { "class": cellClass})
							.appendTo(fragTrow)
							.html(events[i][j]);
						}
					}

					// empty choosen file and make upload buttons visible
					$(".import-events-button").hide();
				}
			}
		});

	});    
});