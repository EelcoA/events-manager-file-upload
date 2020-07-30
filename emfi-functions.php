<?php

// TODO: make these field numbers flexible, depending the received columns
define('emfi_field_nr_event_start_date', 0);
define('emfi_field_nr_event_start_time', 1);
define('emfi_field_nr_event_end_date',   2);
define('emfi_field_nr_event_end_time',   3);
define('emfi_field_nr_event_name',       4);
define('emfi_field_nr_post_excerpt',     5);
define('emfi_field_nr_post_content',     6);
define('emfi_field_nr_location_slug',    7);
define('emfi_field_nr_category_slug',    8);

/**
 * Create a EM_Event object from the input row with fields
 * @param array $event_row
 *
 * @return EM_Event
 * @throws Exception
 */
function emfi_create_EM_Event_from_row(array $event_row ){

	$event = new EM_Event();

	/*
	 * hard coded values
	 */
	$event->post_type      = 'event';
	$event->post_status    = 'publish';
	$event->comment_status = 'open';
	$event->ping_status    = 'closed';
	$event->event_private  = 0;
	$event->event_status   = 1;

	/*
	 * Name, title are mandatory
	 */
	$event->event_name   = emfi_not_empty($event_row[ emfi_field_nr_event_name ], "event_name");
	$event->post_title   = emfi_not_empty($event_row[ emfi_field_nr_event_name ], "event_name");

	/*
	 * excerpt and content are optional
	 */
	$event->post_excerpt = $event_row[ emfi_field_nr_post_excerpt ];
	$event->post_content = $event_row[ emfi_field_nr_post_content ];

	/*
	 * start date and time
	 */
	$event_start_date =  $event_row[ emfi_field_nr_event_start_date ];
	$event_start_time =  $event_row[ emfi_field_nr_event_start_time ];
	if (empty($event_start_date) or empty($event_start_time)) {
		throw new Exception("Start date and/or time have no value.");
	}
	$event->event_start_date = $event_start_date;
	$event->event_start_time = $event_start_time;
	$event->event_start      = $event_start_date . ' ' . $event_start_time;

	/*
	 * end data and time, when not given, same as start date and time
	 */
	$event_end_date = $event_row[ emfi_field_nr_event_end_date ];
	$event_end_time = $event_row[ emfi_field_nr_event_end_time ];
	if (empty($event_end_date) or empty($event_end_time)) {
		$event->event_end_date = $event_start_date;
		$event->event_end_time   = $event_start_time;
	} else {
		$event->event_end_date = $event_end_date;
		$event->event_end_time = $event_end_time;
	}
	/*
	 * Location
	 */
	$location_id        = emfi_get_location_id( $event_row[ emfi_field_nr_location_slug ] );
	$event->location_id = $location_id;

	/*
	 * Set the Category according to the category slug
	 */
	$category_id       = emfi_get_category_id( $event_row[ emfi_field_nr_category_slug ] );
	$category          = array( $category_id );
	$event->categories = new EM_Categories( $category );

	return $event;

}

function emfi_not_empty(string $value, string $name): string {
	if (empty($value))
		throw new Exception("{$name} has no value" );
	return $value;
}
/**
 * Check if a event already exists with event_status = 1 and the same:
 * - location
 * - name
 * - start date
 * - start time
 *  *
 * @param EM_Event $event
 *
 * @return bool
 * @throws Exception
 */
function emfi_event_exists(EM_Event $event): bool
{

	global $wpdb;
	if (empty($event))
		throw new Exception("Event is empty.", "function event_exists()");

	$location_id = $event->location_id;
	$event_name  = $event->event_name;
	$event_start = $event->start()->getDateTime();

	$query_string = $wpdb->prepare( "SELECT count(*) FROM " . EM_EVENTS_TABLE .
	                                " where event_status = 1 and location_id = %d and event_name = %s and event_start = %s",
		array($location_id, $event_name, $event_start)
	);
	$count = (int) $wpdb->get_var($query_string);
	return ($count > 0);
}

/**
 * Get the id of the Category with category_slug = $category_slug
 * @param string $category_slug
 * @return int term_id, being the category id
 * @throws Exception when $category_slug is empty or no category can be found with that slug
 */
function emfi_get_category_id(string $category_slug): int
{
	if (empty($category_slug))
		throw new Exception("Category-slug is empty.");
	$term              = get_term_by( 'slug', $category_slug, 'event-categories' );
	$term_id           = $term->term_id;
	if (!$term_id)
		throw new Exception("There is no category with category-slug '" . $category_slug . "'.");
	return $term_id;
}

/**
 * Get the id of the EM_location with location_slug = $location_slug
 * @param string $location_slug
 * @return int location_id
 * @throws Exception when no location can be found or $location_slug was empty
 */
function emfi_get_location_id(string $location_slug): int
{
	global $wpdb;
	if (empty($location_slug))
		throw new Exception("Location-slug is empty.");
	$query_string = $wpdb->prepare( "SELECT location_id FROM " . EM_LOCATIONS_TABLE . " WHERE location_slug =  %s",
		array( $location_slug));
	$location_id  = $wpdb->get_var($query_string);
	if (is_null($location_id))
		throw new Exception("There is no location with location-slug '" . $location_slug . "'.");
	return $location_id;
}

