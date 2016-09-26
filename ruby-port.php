<?php
include('./config.php');

// directory preparations

$VCFS_DIR = $config['directories']['contacts'];

if (empty($VCFS_DIR)) {
	die("directories must be set, please check your config.php!");
}


mkdir("./{$VCFS_DIR}/", 0777, true);


// database connection
$connectstring = "mysql:host={$config['database']['host']};dbname={$config['database']['database']};charset=utf8mb4";
$db = new PDO($connectstring, $config['database']['username'], $config['database']['password']);



// address book export
$statement = $db->prepare("SELECT uri, carddata, value AS fullname from {$config['database']['cards_table']}, {$config['database']['cards_properties_table']} WHERE oc_cards.id = oc_cards_properties.cardid AND name = 'FN' AND oc_cards.addressbookid = ?;");
foreach ($config['addressbook_ids'] as $addressbook_id) {
	echo "Addressbook {$addressbook_id}".PHP_EOL;
	$statement->execute(array($addressbook_id));

	foreach ($statement as $row) {
		var_dump($row);
		file_put_contents("./{$VCFS_DIR}/{$row['fullname']} - {$row['uri']}", $row['carddata']);
		// exit(1);
	}
}
