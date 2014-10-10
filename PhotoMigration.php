<?php
// Include the SDK using the Composer autoloader
require 'vendor/autoload.php';
require 'functions.php';

use Aws\S3\S3Client;

/**
 * Get our data from the database
 */ 
$photos = getData();
// Debug
// echo '<pre>' . print_r($photos, true) . '</pre>';

/**
 * If database retrieval is successful, continue
 */
if ($photos['status'] === 'Success') {
	$sourceBucket = 'sourcebucket';
	$targetBucket = 'targetbucket';

	echo 'Copying from ' . $sourceBucket . ' to ' . $targetBucket;

	/**
	 * Instantiates an S3 client
	 * 
	 * Need to pass in key and secret if not already specified in ~/.aws/credentials 
	 * or can use a credentials object. 
	 */ 
	$s3 = S3Client::factory(array(
			'key' => 'key',
			'secret' => 'secret'
			));

	try {
		/**
		 * Iterates over the photos we have
		 * 
		 * For each photo, it will get its extension, prefix our source bucket folder,
		 * concatenate its key from the provided information, and copy into the target
		 * bucket.
		 * 
		 * It will also output a log of photos that have been copied so far with their
		 * keys.
		 */ 
		foreach ($photos['photos'] as $photo) {
			// echo '<pre>' . print_r($photo, true) . '</pre>';

			$extension = getExtension($photo['photoFileName']);

			$prefix = 'uploads/';

			$sourceKey = $prefix . $photo['photoPath'] . '/' . $photo['photoId'] . '-' . $photo['photoSecret'] . '.' . $extension;
			$targetKey = $photo['photoId'] . '-' . $photo['photoSecret'] . '.' . $extension;

			$s3->copyObject(array(
				'Bucket' => $targetBucket,
				'Key' => $targetKey,
				'CopySource' => $sourceBucket . '/' . $sourceKey
				));

			$separator = '=';

			$output = 'Source: ' . $sourceKey . PHP_EOL . 'Target: ' . $targetKey . PHP_EOL . str_repeat($separator, 100) . PHP_EOL;
			file_put_contents('copiedPhotos.log', trim($output).PHP_EOL, FILE_APPEND);

			echo 'Copying File:' . PHP_EOL;
			echo $extension;
			echo $output;
		}
	} catch (Exception $e) {
	    $output = timeNow() . ' : ' . $e;

	    echo $output;

	    file_put_contents('photo_migration_errors.log', trim($output).PHP_EOL, FILE_APPEND);
	}

/**
 * If database retrieval fails
 */ 
} elseif ($photos['status'] === 'Error') {
	echo 'Database retrieval has failed';
}