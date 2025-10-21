<?php
/**
 * Analyzes top 100 plugins without blueprints
 */

$zipFile = __DIR__ . '/trunk.zip';
$outputFile = __DIR__ . '/plugins-without-blueprints.json';

if ( !file_exists( $zipFile ) ) {
	echo "Error: {$zipFile} not found. Run update-data.php first.\n";
	exit( 1 );
}

echo "Opening zip file...\n";
$zip = new ZipArchive();
if ( $zip->open( $zipFile ) !== true ) {
	echo "Error: Failed to open zip file\n";
	exit( 1 );
}

echo "Processing plugin JSON files...\n";
$plugins = [];
$processedCount = 0;

for ( $i = 0; $i < $zip->numFiles; $i++ ) {
	$filename = $zip->getNameIndex( $i );

	if ( preg_match( '#/plugins/[^/]+/[^/]+\.json$#', $filename ) ) {
		$content = $zip->getFromIndex( $i );
		if ( $content === false ) {
			continue;
		}

		$data = json_decode( $content, true );
		if ( !$data ) {
			continue;
		}

		$processedCount++;
		if ( $processedCount % 1000 === 0 ) {
			echo "Processed {$processedCount} plugins...\n";
		}

		if ( isset( $data['blueprints'] ) && is_array( $data['blueprints'] ) && count( $data['blueprints'] ) === 0 ) {
			$plugins[] = [
				'name' => $data['name'] ?? '',
				'slug' => $data['slug'] ?? '',
				'downloaded' => $data['downloaded'] ?? 0,
				'active_installs' => $data['active_installs'] ?? 0,
				'url' => 'https://akirk.github.io/playground-step-library/#installPlugin__plugin-.-' . ( $data['slug'] ?? '' ),
			];
		}
	}
}

$zip->close();

echo "Found " . count( $plugins ) . " plugins without blueprints\n";
echo "Sorting by active installs...\n";

usort( $plugins, function( $a, $b ) {
	return $b['active_installs'] - $a['active_installs'];
} );

$plugins = array_slice( $plugins, 0, 100 );

$result = [];
foreach ( $plugins as $plugin ) {
	$slug = $plugin['slug'];
	unset( $plugin['slug'] );
	$result[$slug] = $plugin;
}

echo "Writing output to {$outputFile}...\n";
file_put_contents( $outputFile, json_encode( $result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );

echo "Done! Processed {$processedCount} total plugins, selected top 100 without blueprints\n";
