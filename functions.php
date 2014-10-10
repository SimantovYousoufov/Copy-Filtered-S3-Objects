<?php
/**
 * Big function to handle getting data from the database
 * 
 * @TODO Can break apart later 
 */ 
function getData() 
{
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
	    $error = $e->getMessage();

	    $output = timeNow() . ' : ' . $error;

	    file_put_contents('photo_migration_errors.log', trim($output).PHP_EOL, FILE_APPEND);

	    return array(
			'status' => 'Error',
			'error' => $error
			);
	}

	$query = <<<EOD
	SELECT p.photoId, p.photoPath, p.photoFileName, p.photoSecret
		FROM photo p
	    INNER JOIN member_photo mp
	    	ON p.photoId=mp.photoId
	    	AND mp.galleryEnabled = 'yes';
EOD;

	/**
	 * Fetches results in the chosen mode and then writes our array of photo data to a log file.
	 */
	if (isset($handler)) {
		$result = $handler->query($query);

		$result->setFetchMode(PDO::FETCH_ASSOC);

		$photos = $result->fetchAll();

		file_put_contents('retrievedPhotos.log', print_r($photos, true), FILE_APPEND);

		return array(
			'status' => 'Success',
			'photos' => $photos
			);

	} else {
		// Catch should catch any errors from a database connection, so this
		// isn't strictly needed except for a special case
		$return = array(
			'status' => 'Error',
			'error' => $error
			);

		file_put_contents('photo_migration_errors.log', trim($return).PHP_EOL, FILE_APPEND);

		return $return;
	}
}

/**
 * Gets date in the format we want
 */
function timeNow()
{
	date_default_timezone_set('America/New_York');
	$thisTime = new DateTime();
	return $thisTime->format('r');
}

/**
 * Gets iamge's extension
 */
function getExtension($filename)
{
	$nameArray = explode('.', $filename);

	$extension = end($nameArray);

	return $extension;
}
?>