<?php
function dbQuery() {
/**
 * DB Connection
 */
try { 
	$host = '127.0.0.1';
	$dbname = 'canvaspeople';
	$user = 'root';
	$pass = 'pmg1';

	$handler = new PDO('mysql:host='.$host.';dbname='.$dbname, $user, $pass);
	$handler->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo $e->getMessage();
}

$query = <<<EOD
SELECT p.photoId, p.photoPath, p.photoFileName, p.photoSecret
	FROM photo p
    INNER JOIN member_photo mp
    	ON p.photoId=mp.photoId
    	AND mp.galleryEnabled = 'yes';
EOD;

/**
 * Fetches results in the chosen mode
 */
$result = $handler->query($query);

$result->setFetchMode(PDO::FETCH_ASSOC);

$photos = $result->fetchAll();

// echo '<pre>' . print_r($photos, true) . '</pre>';

/**
 * Loop to write files to local file system.
 * 
 * Once written the files can be copied to an s3 bucket.
 * 
 * Example file location on our current s3 bucket:
 * https://s3.amazonaws.com/s3.canvaspeople.com/uploads/20090814/7-wnHK-100.jpg
 */
// foreach ($photos as $photo) {
// 	echo '<pre>' . print_r($photo, true) . '</pre>';

// 	// $fileLocation = 'https://s3.amazonaws.com/s3.canvaspeople.com/uploads/' . $photo['photoPath'] . '/' . $photo['photoFileName'];

// 	// // Should we have standardized names for files?
// 	// $filename = $photo['photoFileName'];	
// }
return $photos;
}




?>