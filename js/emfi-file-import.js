jQuery(document).ready(function($) {
	jQuery('#emfi-upload-file-button').click(function(e) {

		var files = document.getElementById("file").files;
		
		var data = new FormData();
		data.append('file', files[0]);
		data.append('action', 'emfi_file_upload');

		// alert(`Going to call ajax_object.ajaxurl with:\n- action=${data.get('action')},\n- file=${data.get('file').name}`);

		$.ajax({
			type: "POST",
			url: ajax_object.ajaxurl,
			data: data,
			contentType: false,
			processData: false,
			success: function(responseJson) {
				response = JSON.parse(responseJson);
				if(response.status=='FAILURE'){				
					jQuery('#emfi-message').html(`<span style="color: red;">${response.message}</span>`);
				} else {
					events = JSON.parse(response.data);
					jQuery('#emfi-message').html(`<span style="color: green;">${response.message}</span>`);

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
							addedClass = "emu-row-hover alternate"
						} else {
							addedClass = "emu-row-hover"
						} 
						var fragTrow = $("<tr>", {
							"class": addedClass
						}).appendTo(aTable);
						for (var j = 0; j < colmCount; j++) {
							$("<td>")
							.appendTo(fragTrow)
							.html(events[i][j]);
						}
					}

					// $.each(data, function(i,val){
					// 	jQuery('#events').html(`<tr><td>${val}</td></tr>`);
					// });
				}
				// for (var i=0;i < response.data.length; i++) {
				// 	jQuery('#emfi-message').html(`<span style="color: green;">Respons: ${response}</span>`);
				// }
				// if(response.status=="FAILURE"){
				// 	jQuery('#emfi-message').html(`<span style="color: green;">Respons: ${response.message}</span>`);
				// } else {
				// 	setTimeout(function(){
				// 		response = JSON.parse(response);
				// 		for (var i=0;i < response.Data.length; i++) {
				// 			jQuery('#emfi-message').html(`<span style="color: green;">Respons: ${response}</span>`);
				// 		}
				// 	})
				// }
			}
		});
	});    
});


// jQuery(document).ready(function($) {
// 	jQuery('#upload-file-button').click(function(e) {
// 		e.preventDefault();
		
// 		var email = jQuery('#roytuts_contact_email').val();
		
// 		var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/i;
		
// 		if(email !== '' && regex.test(email)) {
		
// 			var data = {
// 				'action': 'roytuts_email_subscription',
// 				'email': email
// 			};
//             var text = `<span style="color: red;">Valid Email: ${data.email} </span>`
// 			jQuery('#roytuts-msg').html(text);

// 			jQuery.post(ajax_object.ajaxurl, data, function(response) {
// 				jQuery('#roytuts-msg').html(`<span style="color: green;">Respons: ${response}</span>`);
// 			// 	if(response == "success") {
// 			// 		jQuery('#roytuts-msg').html('<span style="color: green;">Subscription Successful</span>');
// 			// 	} else if(response == "error") {
// 			// 		jQuery('#roytuts-msg').html('<span style="color: red;">Subscription Failed</span>');
// 			// 	}
// 			});
// 		} else {
// 			jQuery('#roytuts-msg').html('<span style="color: red;">Invalid Email</span>');
// 		}
// 	});
// });
