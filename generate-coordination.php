<?php
/**
 * Generates Excel coordination tables for plugin preview button PRs
 * Processes all plugins and themes without blueprints
 */

require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Hyperlink;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Font;

$zipFile = __DIR__ . '/trunk.zip';
$pluginsOutputFile = __DIR__ . '/coordination-plugins.xlsx';
$themesOutputFile = __DIR__ . '/coordination-themes.xlsx';

$minActiveInstalls = 100;
$maxItemsPerType = 1000;

if ( !file_exists( $zipFile ) ) {
	echo "Error: {$zipFile} not found. Run 'composer update-data' first.\n";
	exit( 1 );
}

echo "Opening zip file...\n";
$zip = new ZipArchive();
if ( $zip->open( $zipFile ) !== true ) {
	echo "Error: Failed to open zip file\n";
	exit( 1 );
}

$plugins = [];
$themes = [];
$processedCount = 0;

echo "Processing plugins and themes...\n";

$knownGithubRepos = [
	'contact-form-7' => 'https://github.com/rocklobster-in/contact-form-7',
	'elementor' => 'https://github.com/elementor/elementor',
	'wordpress-seo' => 'https://github.com/Yoast/wordpress-seo',
];

for ( $i = 0; $i < $zip->numFiles; $i++ ) {
	$filename = $zip->getNameIndex( $i );

	if ( preg_match( '#/(?P<type>plugins|themes)/[^/]+/[^/]+\.json$#', $filename, $matches ) ) {
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
			echo "Processed {$processedCount} items...\n";
		}

		$type = $matches['type'];
		$hasBlueprints = isset( $data['blueprints'] ) && is_array( $data['blueprints'] ) && count( $data['blueprints'] ) > 0;

		$activeInstalls = $data['active_installs'] ?? 0;

		if ( $activeInstalls < $minActiveInstalls ) {
			continue;
		}

		$slug = $data['slug'] ?? '';
		$name = $data['name'] ?? '';

		$wpOrgUrl = $type === 'plugins'
			? "https://wordpress.org/plugins/{$slug}/"
			: "https://wordpress.org/themes/{$slug}/";

		$forumsUrl = $type === 'plugins'
			? "https://wordpress.org/support/plugin/{$slug}/#new-topic-0"
			: "https://wordpress.org/support/theme/{$slug}/#new-topic-0";

		$stepLibraryUrl = $type === 'plugins'
			? "https://akirk.github.io/playground-step-library/?step[0]=installPlugin&url[0]=wordpress.org/plugins/{$slug}/"
			: "https://akirk.github.io/playground-step-library/?step[0]=installTheme&url[0]=wordpress.org/themes/{$slug}/";

		$homepage = $data['homepage'] ?? '';
		$githubUrl = '';

		if ( isset( $knownGithubRepos[$slug] ) ) {
			$githubUrl = $knownGithubRepos[$slug];
		} elseif ( $homepage && strpos( $homepage, 'github.com' ) !== false ) {
			$githubUrl = $homepage;
		}

		$item = [
			'slug' => $slug,
			'name' => html_entity_decode( $name, ENT_QUOTES | ENT_HTML5, 'UTF-8' ),
			'active_installs' => $activeInstalls,
			'wp_org_url' => $wpOrgUrl,
			'homepage_url' => $homepage,
			'forums_url' => $forumsUrl,
			'step_library_url' => $stepLibraryUrl,
			'github_url' => $githubUrl,
			'has_live_preview' => $hasBlueprints ? 'Yes' : 'No',
		];

		if ( $type === 'plugins' ) {
			$plugins[] = $item;
		} else {
			$themes[] = $item;
		}
	}
}

$zip->close();

echo "Found " . count( $plugins ) . " plugins and " . count( $themes ) . " themes\n";
echo "Sorting by active installs...\n";

usort( $plugins, function( $a, $b ) {
	return $b['active_installs'] - $a['active_installs'];
} );

usort( $themes, function( $a, $b ) {
	return $b['active_installs'] - $a['active_installs'];
} );

$plugins = array_slice( $plugins, 0, $maxItemsPerType );
$themes = array_slice( $themes, 0, $maxItemsPerType );

echo "Limited to top {$maxItemsPerType} of each type\n";

function generateExcelFile( $items, $filename, $type = 'Plugin' ) {
	$spreadsheet = new Spreadsheet();
	$sheet = $spreadsheet->getActiveSheet();

	$pageLabel = $type === 'Plugin' ? 'Plugin Page' : 'Theme Page';

	$headers = [
		'Name',
		'Active Installs',
		'WordPress.org',
		'Homepage',
		'Forums',
		'Step Library',
		'GitHub',
		'Has Live Preview',
		'Blueprint Link',
		'Team Member Assigned',
		'PR Link',
		'Notes',
	];

	$sheet->fromArray( $headers, null, 'A1' );

	$sheet->getStyle( 'A1:L1' )->getFont()->setBold( true );

	$row = 2;
	foreach ( $items as $item ) {
		$sheet->setCellValue( "A{$row}", $item['name'] );
		$sheet->setCellValue( "B{$row}", $item['active_installs'] );

		$sheet->setCellValue( "C{$row}", $pageLabel );
		$sheet->getCell( "C{$row}" )->getHyperlink()->setUrl( $item['wp_org_url'] );
		$sheet->getStyle( "C{$row}" )->getFont()->setColor( new Color( Color::COLOR_BLUE ) )->setUnderline( Font::UNDERLINE_SINGLE );

		if ( $item['homepage_url'] ) {
			$sheet->setCellValue( "D{$row}", 'Homepage' );
			$sheet->getCell( "D{$row}" )->getHyperlink()->setUrl( $item['homepage_url'] );
			$sheet->getStyle( "D{$row}" )->getFont()->setColor( new Color( Color::COLOR_BLUE ) )->setUnderline( Font::UNDERLINE_SINGLE );
		}

		$sheet->setCellValue( "E{$row}", 'Forums' );
		$sheet->getCell( "E{$row}" )->getHyperlink()->setUrl( $item['forums_url'] );
		$sheet->getStyle( "E{$row}" )->getFont()->setColor( new Color( Color::COLOR_BLUE ) )->setUnderline( Font::UNDERLINE_SINGLE );

		$sheet->setCellValue( "F{$row}", 'Preconfigured' );
		$sheet->getCell( "F{$row}" )->getHyperlink()->setUrl( $item['step_library_url'] );
		$sheet->getStyle( "F{$row}" )->getFont()->setColor( new Color( Color::COLOR_BLUE ) )->setUnderline( Font::UNDERLINE_SINGLE );

		if ( $item['github_url'] ) {
			$githubText = 'GitHub';
			if ( preg_match( '#github\.com/([^/]+/[^/]+)#', $item['github_url'], $matches ) ) {
				$githubText = $matches[1];
			}
			$sheet->setCellValue( "G{$row}", $githubText );
			$sheet->getCell( "G{$row}" )->getHyperlink()->setUrl( $item['github_url'] );
			$sheet->getStyle( "G{$row}" )->getFont()->setColor( new Color( Color::COLOR_BLUE ) )->setUnderline( Font::UNDERLINE_SINGLE );
		}

		if ( $item['has_live_preview'] === 'Yes' ) {
			$previewUrl = $item['wp_org_url'] . '?preview=1';
			$sheet->setCellValue( "H{$row}", 'Yes' );
			$sheet->getCell( "H{$row}" )->getHyperlink()->setUrl( $previewUrl );
			$sheet->getStyle( "H{$row}" )->getFont()->setColor( new Color( Color::COLOR_BLUE ) )->setUnderline( Font::UNDERLINE_SINGLE );

			$blueprintJsonUrl = "https://wordpress.org/plugins/wp-json/plugins/v1/plugin/{$item['slug']}/blueprint.json";
			$sheet->setCellValue( "I{$row}", 'blueprint.json' );
			$sheet->getCell( "I{$row}" )->getHyperlink()->setUrl( $blueprintJsonUrl );
			$sheet->getStyle( "I{$row}" )->getFont()->setColor( new Color( Color::COLOR_BLUE ) )->setUnderline( Font::UNDERLINE_SINGLE );

			$sheet->setCellValue( "J{$row}", '-' );
		} else {
			$sheet->setCellValue( "H{$row}", 'No' );
		}

		$row++;
	}

	$sheet->getColumnDimension( 'A' )->setWidth( 50 );
	$sheet->getColumnDimension( 'G' )->setWidth( 30 );

	foreach ( range( 'B', 'F' ) as $col ) {
		$sheet->getColumnDimension( $col )->setAutoSize( true );
	}
	foreach ( range( 'H', 'L' ) as $col ) {
		$sheet->getColumnDimension( $col )->setAutoSize( true );
	}

	$writer = new Xlsx( $spreadsheet );
	$writer->save( $filename );
}

echo "Generating plugins Excel file...\n";
generateExcelFile( $plugins, $pluginsOutputFile, 'Plugin' );

echo "Generating themes Excel file...\n";
generateExcelFile( $themes, $themesOutputFile, 'Theme' );

echo "\nDone!\n";
echo "Plugins: " . count( $plugins ) . " items written to {$pluginsOutputFile}\n";
echo "Themes: " . count( $themes ) . " items written to {$themesOutputFile}\n";
echo "\nNote: Only items with {$minActiveInstalls}+ active installs, limited to top {$maxItemsPerType} of each type.\n";
echo "\nYou can now import these Excel files into Google Sheets with preserved hyperlinks!\n";
