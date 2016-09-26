<?php
$config = array(
	"database" => array(
		"host" => "localhost",
		"username" => "owncloud",
		"password" => "owncloud",
		"database" => "oc_database",
		"cards_table" => "oc_cards",
		"cards_properties_table" => "oc_cards_properties",
		"calendar_table" => "oc_calendarobjects"
	),
	"addressbook_ids" => array(
		1
	),
	"calendar_ids" => array(
		1
	),
	"directories" => array(
		"contacts" => "./contacts",
		"calendar" => "./calendar",
		"todos" => "./todos"
	),
	"mailer" => array(
		"from" => "gitexport@example.com",
		"to" => array(
			"recipient1@example.com",
			"recipient2@example.com"
		)
	)
);
