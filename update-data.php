<?php
/**
 * Updates the wp-public-data zip file if it's older than 7 days
 */

$zipFile = __DIR__ . '/trunk.zip';
$url = 'https://github.com/dd32/wp-public-data/archive/refs/heads/trunk.zip';
$maxAge = 86400 * 7;

$shouldDownload = false;

if ( !file_exists( $zipFile ) ) {
	echo "Zip file not found. Downloading...\n";
	$shouldDownload = true;
} else {
	$fileAge = time() - filemtime( $zipFile );
	$ageInDays = floor( $fileAge / 86400 );
	$ageInHours = floor( ( $fileAge % 86400 ) / 3600 );

	echo "Current file age: {$ageInDays} days, {$ageInHours} hours\n";

	if ( $fileAge > $maxAge ) {
		echo "File is older than 1 day. Updating...\n";
		$shouldDownload = true;
	} else {
		echo "File is up to date. No download needed.\n";
	}
}

if ( $shouldDownload ) {
	echo "Downloading from {$url}...\n";

	$tmpFile = $zipFile . '.tmp';

	$ch = curl_init( $url );
	curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
	curl_setopt( $ch, CURLOPT_NOPROGRESS, false );
	curl_setopt( $ch, CURLOPT_PROGRESSFUNCTION, function( $resource, $downloadSize, $downloaded ) {
		if ( $downloadSize > 0 ) {
			$percent = round( ( $downloaded / $downloadSize ) * 100, 1 );
			$downloadedMB = round( $downloaded / 1048576, 1 );
			$totalMB = round( $downloadSize / 1048576, 1 );
			echo "\rProgress: {$percent}% ({$downloadedMB}MB / {$totalMB}MB)";
		}
	} );

	$data = curl_exec( $ch );
	$httpCode = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
	curl_close( $ch );

	echo "\n";

	if ( $httpCode !== 200 ) {
		echo "Error: Failed to download file (HTTP {$httpCode})\n";
		exit( 1 );
	}

	if ( file_put_contents( $tmpFile, $data ) === false ) {
		echo "Error: Failed to write to temporary file\n";
		exit( 1 );
	}

	if ( !rename( $tmpFile, $zipFile ) ) {
		echo "Error: Failed to move temporary file to final location\n";
		exit( 1 );
	}

	$sizeMB = round( filesize( $zipFile ) / 1048576, 1 );
	echo "Download complete! File size: {$sizeMB}MB\n";
}

echo "Done.\n";
