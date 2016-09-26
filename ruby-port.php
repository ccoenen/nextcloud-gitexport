<?php
include('./config.php');

// directory preparations

$VCFS_DIR = $config['directories']['contacts'];
$VTODO_DIR = $config['directories']['todos'];
$ICS_DIR = $config['directories']['calendar'];


if (empty($VCFS_DIR) || empty($VTODO_DIR) || empty($ICS_DIR)) {
	die("directories must be set, please check your config.php!");
}


@mkdir("./{$VCFS_DIR}/", 0777, true);
@mkdir("./{$VTODO_DIR}/", 0777, true);
@mkdir("./{$ICS_DIR}/", 0777, true);

// recursive delete. YES, it's not part of the standard library. This is ridiculous.
function recursively_remove_contents($path) {
	$iterator = new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS);
	$files = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::CHILD_FIRST);
	foreach($files as $file) {
		if ($file->isDir()){
			rmdir($file->getRealPath());
		} else {
			unlink($file->getRealPath());
		}
	}
}


function sanitize_filename($filename) {
	return str_replace(array('/'), '_', $filename);
}



// database connection
$connectstring = "mysql:host={$config['database']['host']};dbname={$config['database']['database']};charset=utf8mb4";
$db = new PDO($connectstring, $config['database']['username'], $config['database']['password']);


// address book export
recursively_remove_contents($VCFS_DIR);
$statement = $db->prepare(<<<"SQL"
	SELECT uri, carddata, value AS fullname
	FROM {$config['database']['cards_table']}, {$config['database']['cards_properties_table']}
	WHERE oc_cards.id = oc_cards_properties.cardid
		AND name = 'FN'
		AND oc_cards.addressbookid = ?
	ORDER BY fullname;
SQL
);
foreach ($config['addressbook_ids'] as $addressbook_id) {
	echo "Addressbook {$addressbook_id}".PHP_EOL;
	$statement->execute(array($addressbook_id));

	foreach ($statement as $row) {
		echo $row['fullname'][0]; // 1 letter progress indicator
		// var_dump($row);
		file_put_contents("./{$VCFS_DIR}/".sanitize_filename("{$row['fullname']} - {$row['uri']}"), $row['carddata']);
	}
	echo PHP_EOL;
}


// calendar export
recursively_remove_contents($ICS_DIR);
$statement = $db->prepare(<<<"SQL"
	SELECT uri, calendardata, firstoccurence
	FROM {$config['database']['calendar_table']}
	WHERE calendarid = ?
		AND componenttype = 'VEVENT';
SQL
);
foreach ($config['calendar_ids'] as $calendar_id) {
	echo "Calendar {$calendar_id}".PHP_EOL;
	$statement->execute(array($calendar_id));

	foreach ($statement as $row) {
		echo ".";
		// var_dump($row);
		$year = date('Y', $row['firstoccurence']);
		$date = date('Y-m-d', $row['firstoccurence']);
		$summary = "asdfasdfasf"; // TODO fix summary
		@mkdir("./{$ICS_DIR}/{$year}/", 0777, true);
		file_put_contents("./{$ICS_DIR}/{$year}/".sanitize_filename("{$date} {$summary} - {$row['uri']}"), $row['calendardata']);
	}
	echo PHP_EOL;
}


// todo export
recursively_remove_contents($VTODO_DIR);
$statement = $db->prepare(<<<"SQL"
        SELECT uri, calendardata, lastmodified
        FROM {$config['database']['calendar_table']}
        WHERE calendarid = ?
		AND componenttype = 'VTODO';
SQL
);
foreach ($config['calendar_ids'] as $calendar_id) {
        echo "Calendar {$calendar_id} (TODO)".PHP_EOL;
        $statement->execute(array($calendar_id));

        foreach ($statement as $row) {
                echo ".";
                // var_dump($row);
		$time = $row['lastmodified']; // default
                $year = date('Y', $time);
                $date = date('Y-m-d', $time);
                $summary = "asdfasdfasf"; // TODO fix summary
                @mkdir("./{$VTODO_DIR}/{$year}/", 0777, true);
                file_put_contents("./{$VTODO_DIR}/{$year}/".sanitize_filename("{$date} {$summary} - {$row['uri']}"), $row['calendardata']);
        }
        echo PHP_EOL;
}
