<?php
/**
 * Generates HTML tables from the analyization JSON files
 */

$outputFile = __DIR__ . '/tables.html';

$pluginsWithBlueprints = [];
$pluginsWithoutBlueprints = [];
$pluginsTop100 = [];

if ( ! file_exists( __DIR__ . '/plugins-with-blueprints.json' ) ) {
	echo "Error: plugins-with-blueprints.json not found. Run analyze-plugins-with-blueprints.php first.\n";
	exit( 1 );
	}
$pluginsWithBlueprints = json_decode( file_get_contents( __DIR__ . '/plugins-with-blueprints.json' ), true );

if ( ! file_exists( __DIR__ . '/plugins-without-blueprints.json' ) ) {
	echo "Error: plugins-without-blueprints.json not found. Run analyze-plugins-without-blueprints.php first.\n";
	exit( 1 );
}

$pluginsWithoutBlueprints = json_decode( file_get_contents( __DIR__ . '/plugins-without-blueprints.json' ), true );


if ( ! file_exists( __DIR__ . '/plugins-top-100.json' ) ) {
	echo "Error: plugins-top-100.json not found. Run analyze-plugins-top-100.php first.\n";
	exit( 1 );
}
$pluginsTop100 = json_decode( file_get_contents( __DIR__ . '/plugins-top-100.json' ), true );


$totalPlugins = count( $pluginsWithBlueprints ) + count( $pluginsWithoutBlueprints );
$pluginsWithPreviewsAll = count( $pluginsWithBlueprints );
$pluginsWithPreviewsTop100 = 0;

foreach ( $pluginsTop100 as $plugin ) {
	if ( isset( $plugin['preview_url'] ) && $plugin['preview_url'] !== false ) {
		$pluginsWithPreviewsTop100++;
	}
}

$percentAll = $totalPlugins > 0 ? round( ( $pluginsWithPreviewsAll / $totalPlugins ) * 100, 1 ) : 0;
$percentTop100 = count( $pluginsTop100 ) > 0 ? round( ( $pluginsWithPreviewsTop100 / count( $pluginsTop100 ) ) * 100 ) : 0;

$htmlContent = '';

$htmlContent .= "<!DOCTYPE html>\n";
$htmlContent .= "<html lang=\"en\">\n";
$htmlContent .= "<head>\n";
$htmlContent .= "<meta charset=\"UTF-8\">\n";
$htmlContent .= "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n";
$htmlContent .= "<title>WordPress Plugin Statistics</title>\n";
$htmlContent .= "<style>\n";
$htmlContent .= "body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen-Sans, Ubuntu, Cantarell, sans-serif; max-width: 1400px; margin: 0 auto; padding: 20px; background: #fff; }\n";
$htmlContent .= ".wp-block-table table { border-collapse: collapse; width: 100%; margin-bottom: 30px; }\n";
$htmlContent .= ".wp-block-table th { background-color: #f5f5f5; font-weight: 600; padding: 12px; text-align: left; border: 1px solid #ddd; }\n";
$htmlContent .= ".wp-block-table td { padding: 10px; border: 1px solid #ddd; }\n";
$htmlContent .= ".wp-block-table tbody tr:nth-child(even) { background-color: #fafafa; }\n";
$htmlContent .= ".wp-block-table tbody tr:hover { background-color: #f0f0f0; }\n";
$htmlContent .= ".wp-block-table a { color: #0073aa; text-decoration: none; }\n";
$htmlContent .= ".wp-block-table a:hover { text-decoration: underline; }\n";
$htmlContent .= ".toc { background-color: #f9f9f9; padding: 20px; border-left: 4px solid #0073aa; margin-bottom: 30px; }\n";
$htmlContent .= ".toc h2 { margin-top: 0; }\n";
$htmlContent .= ".toc ul { list-style-type: none; padding-left: 0; }\n";
$htmlContent .= ".toc li { margin: 8px 0; }\n";
$htmlContent .= ".section { position: relative; margin-bottom: 40px; }\n";
$htmlContent .= ".section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }\n";
$htmlContent .= ".copy-btn { background: #0073aa; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-size: 14px; }\n";
$htmlContent .= ".copy-btn:hover { background: #005a87; }\n";
$htmlContent .= ".copy-btn:active { background: #004368; }\n";
$htmlContent .= ".copy-btn.copied { background: #00a32a; }\n";
$htmlContent .= ".wordpress-html { display: none; }\n";
$htmlContent .= "h2.wp-block-heading { margin: 0; }\n";
$htmlContent .= "</style>\n";
$htmlContent .= "</head>\n";
$htmlContent .= "<body>\n\n";

$htmlContent .= "<h1>WordPress Plugin Statistics</h1>\n\n";

$htmlContent .= "<div class=\"toc\">\n";
$htmlContent .= "<h2>Table of Contents</h2>\n";
$htmlContent .= "<ul>\n";
$htmlContent .= "<li><a href=\"#summary\">Summary Statistics</a></li>\n";
$htmlContent .= "<li><a href=\"#top-100-status\">Top 100 Plugins - Preview Status</a></li>\n";
$htmlContent .= "<li><a href=\"#plugins-with-blueprints\">Plugins with Blueprints (" . number_format( count( $pluginsWithBlueprints ) ) . ")</a></li>\n";
$htmlContent .= "<li><a href=\"#plugins-without-blueprints\">Top 100 Plugins without Blueprints</a></li>\n";
$htmlContent .= "<li><a href=\"#plugins-top-100\">Top 100 Plugins (All)</a></li>\n";
$htmlContent .= "</ul>\n";
$htmlContent .= "</div>\n\n";

echo "Generating summary statistics table...\n";
$wpSummaryHtml = "<!-- wp:heading -->\n";
$wpSummaryHtml .= "<h2 class=\"wp-block-heading\" id=\"summary\">Summary Statistics</h2>\n";
$wpSummaryHtml .= "<!-- /wp:heading -->\n\n";
$wpSummaryHtml .= "<!-- wp:table {\"hasFixedLayout\":false} -->\n";
$wpSummaryHtml .= "<figure class=\"wp-block-table\"><table><thead><tr><th></th><th>Plugins with Previews</th><th>Percent</th></tr></thead><tbody>";
$wpSummaryHtml .= "<tr><td>Top 100 Plugins (by active installs)</td><td>{$pluginsWithPreviewsTop100}</td><td>{$percentTop100}%</td></tr>";
$wpSummaryHtml .= "<tr><td>All Plugins</td><td>" . number_format( $pluginsWithPreviewsAll ) . "</td><td>{$percentAll}%</td></tr>";
$wpSummaryHtml .= "</tbody></table></figure>\n";
$wpSummaryHtml .= "<!-- /wp:table -->";

$htmlContent .= "<div class=\"section\">\n";
$htmlContent .= "<div class=\"section-header\">\n";
$htmlContent .= "<h2 class=\"wp-block-heading\" id=\"summary\">Summary Statistics</h2>\n";
$htmlContent .= "<button class=\"copy-btn\" onclick=\"copyToClipboard('summary-wp')\">Copy WordPress HTML</button>\n";
$htmlContent .= "</div>\n";
$htmlContent .= "<div class=\"wordpress-html\" id=\"summary-wp\">" . htmlspecialchars( $wpSummaryHtml ) . "</div>\n";
$htmlContent .= "<figure class=\"wp-block-table\"><table><thead><tr><th></th><th>Plugins with Previews</th><th>Percent</th></tr></thead><tbody>";
$htmlContent .= "<tr><td>Top 100 Plugins (by active installs)</td><td>{$pluginsWithPreviewsTop100}</td><td>{$percentTop100}%</td></tr>";
$htmlContent .= "<tr><td>All Plugins</td><td>" . number_format( $pluginsWithPreviewsAll ) . "</td><td>{$percentAll}%</td></tr>";
$htmlContent .= "</tbody></table></figure>\n";
$htmlContent .= "</div>\n\n";

echo "Generating Top 100 plugins preview status table...\n";
$wpTop100StatusHtml = "<!-- wp:heading -->\n";
$wpTop100StatusHtml .= "<h2 class=\"wp-block-heading\" id=\"top-100-status\">Top 100 Plugins - Preview Status</h2>\n";
$wpTop100StatusHtml .= "<!-- /wp:heading -->\n\n";
$wpTop100StatusHtml .= "<!-- wp:table {\"hasFixedLayout\":false} -->\n";
$wpTop100StatusHtml .= "<figure class=\"wp-block-table\"><table><thead><tr><th>Rank</th><th>Plugin Preview</th><th>Enabled</th></tr></thead><tbody>";

$top100TableRows = '';
$rank = 1;
foreach ( $pluginsTop100 as $slug => $plugin ) {
	$name = htmlspecialchars( $plugin['name'] );
	$pluginUrl = "https://wordpress.org/plugins/{$slug}/";

	$row = "<tr>";
	$row .= "<td>{$rank}</td>";
	$row .= "<td><a href=\"{$pluginUrl}\">{$name}</a></td>";

	if ( isset( $plugin['preview_url'] ) && $plugin['preview_url'] !== false ) {
		$previewUrl = htmlspecialchars( $plugin['preview_url'] );
		$row .= "<td><a href=\"{$previewUrl}\">Yes</a></td>";
	} else {
		$row .= "<td>No</td>";
	}

	$row .= "</tr>";
	$top100TableRows .= $row;
	$rank++;
}

$wpTop100StatusHtml .= $top100TableRows;
$wpTop100StatusHtml .= "</tbody></table></figure>\n";
$wpTop100StatusHtml .= "<!-- /wp:table -->";

$htmlContent .= "<div class=\"section\">\n";
$htmlContent .= "<div class=\"section-header\">\n";
$htmlContent .= "<h2 class=\"wp-block-heading\" id=\"top-100-status\">Top 100 Plugins - Preview Status</h2>\n";
$htmlContent .= "<button class=\"copy-btn\" onclick=\"copyToClipboard('top-100-status-wp')\">Copy WordPress HTML</button>\n";
$htmlContent .= "</div>\n";
$htmlContent .= "<div class=\"wordpress-html\" id=\"top-100-status-wp\">" . htmlspecialchars( $wpTop100StatusHtml ) . "</div>\n";
$htmlContent .= "<figure class=\"wp-block-table\"><table><thead><tr><th>Rank</th><th>Plugin Preview</th><th>Enabled</th></tr></thead><tbody>";
$htmlContent .= $top100TableRows;
$htmlContent .= "</tbody></table></figure>\n";
$htmlContent .= "</div>\n\n";

$sections = [
	[
		'id' => 'plugins-with-blueprints',
		'title' => 'Plugins with Blueprints',
		'file' => 'plugins-with-blueprints.json',
		'data' => $pluginsWithBlueprints,
	],
	[
		'id' => 'plugins-without-blueprints',
		'title' => 'Top 100 Plugins without Blueprints',
		'file' => 'plugins-without-blueprints.json',
		'data' => $pluginsWithoutBlueprints,
	],
	[
		'id' => 'plugins-top-100',
		'title' => 'Top 100 Plugins',
		'file' => 'plugins-top-100.json',
		'data' => $pluginsTop100,
	],
];

foreach ( $sections as $section ) {
	if ( empty( $section['data'] ) ) {
		echo "Warning: {$section['file']} not found or empty. Skipping...\n";
		continue;
	}

	echo "Processing {$section['file']}...\n";

	$wpSectionHtml = "<!-- wp:heading -->\n";
	$wpSectionHtml .= "<h2 class=\"wp-block-heading\" id=\"{$section['id']}\">{$section['title']}</h2>\n";
	$wpSectionHtml .= "<!-- /wp:heading -->\n\n";
	$wpSectionHtml .= "<!-- wp:table {\"hasFixedLayout\":false} -->\n";
	$wpSectionHtml .= '<figure class="wp-block-table"><table><thead><tr>';
	$wpSectionHtml .= "<th>Plugin Name</th>";
	$wpSectionHtml .= "<th>Downloads</th>";
	$wpSectionHtml .= "<th>Active Installs</th>";

	$firstPlugin = reset( $section['data'] );
	if ( isset( $firstPlugin['preview_url'] ) ) {
		$wpSectionHtml .= "<th>Preview</th>";
	} elseif ( isset( $firstPlugin['url'] ) ) {
		$wpSectionHtml .= "<th>Playground Link</th>";
	}

	$wpSectionHtml .= "</tr></thead><tbody>";

	$tableHeaderHtml = '<thead><tr>';
	$tableHeaderHtml .= "<th>Plugin Name</th>";
	$tableHeaderHtml .= "<th>Downloads</th>";
	$tableHeaderHtml .= "<th>Active Installs</th>";
	if ( isset( $firstPlugin['preview_url'] ) ) {
		$tableHeaderHtml .= "<th>Preview</th>";
	} elseif ( isset( $firstPlugin['url'] ) ) {
		$tableHeaderHtml .= "<th>Playground Link</th>";
	}
	$tableHeaderHtml .= "</tr></thead>";

	$tableBodyHtml = '';
	$count = 0;
	foreach ( $section['data'] as $slug => $plugin ) {
		$count++;
		$name = htmlspecialchars( $plugin['name'] );
		$downloaded = number_format( $plugin['downloaded'] );
		$activeInstalls = number_format( $plugin['active_installs'] );

		$row = "<tr>";
		$row .= "<td><a href=\"https://wordpress.org/plugins/{$slug}/\">{$name}</a></td>";
		$row .= "<td>{$downloaded}</td>";
		$row .= "<td>{$activeInstalls}</td>";

		if ( isset( $plugin['preview_url'] ) && $plugin['preview_url'] !== false ) {
			$previewUrl = htmlspecialchars( $plugin['preview_url'] );
			$row .= "<td><a href=\"{$previewUrl}\" target=\"_blank\">Preview</a></td>";
		} elseif ( isset( $plugin['preview_url'] ) && $plugin['preview_url'] === false ) {
			$row .= "<td>-</td>";
		} elseif ( isset( $plugin['url'] ) ) {
			$playgroundUrl = htmlspecialchars( $plugin['url'] );
			$row .= "<td><a href=\"{$playgroundUrl}\" target=\"_blank\">Open in Playground</a></td>";
		}

		$row .= "</tr>";
		$tableBodyHtml .= $row;
	}

	$wpSectionHtml .= $tableBodyHtml;
	$wpSectionHtml .= "</tbody></table></figure>\n";
	$wpSectionHtml .= "<!-- /wp:table -->";

	$htmlContent .= "<div class=\"section\">\n";
	$htmlContent .= "<div class=\"section-header\">\n";
	$htmlContent .= "<h2 class=\"wp-block-heading\" id=\"{$section['id']}\">{$section['title']}</h2>\n";
	$htmlContent .= "<button class=\"copy-btn\" onclick=\"copyToClipboard('{$section['id']}-wp')\">Copy WordPress HTML</button>\n";
	$htmlContent .= "</div>\n";
	$htmlContent .= "<div class=\"wordpress-html\" id=\"{$section['id']}-wp\">" . htmlspecialchars( $wpSectionHtml ) . "</div>\n";
	$htmlContent .= '<figure class="wp-block-table"><table>' . $tableHeaderHtml . '<tbody>';
	$htmlContent .= $tableBodyHtml;
	$htmlContent .= "</tbody></table></figure>\n";
	$htmlContent .= "</div>\n\n";

	echo "Generated table with {$count} plugins\n\n";
}

$htmlContent .= "<script>\n";
$htmlContent .= "function copyToClipboard(elementId) {\n";
$htmlContent .= "  const element = document.getElementById(elementId);\n";
$htmlContent .= "  const text = element.textContent;\n";
$htmlContent .= "  \n";
$htmlContent .= "  navigator.clipboard.writeText(text).then(() => {\n";
$htmlContent .= "    const button = event.target;\n";
$htmlContent .= "    const originalText = button.textContent;\n";
$htmlContent .= "    button.textContent = 'Copied!';\n";
$htmlContent .= "    button.classList.add('copied');\n";
$htmlContent .= "    \n";
$htmlContent .= "    setTimeout(() => {\n";
$htmlContent .= "      button.textContent = originalText;\n";
$htmlContent .= "      button.classList.remove('copied');\n";
$htmlContent .= "    }, 2000);\n";
$htmlContent .= "  }).catch(err => {\n";
$htmlContent .= "    console.error('Failed to copy:', err);\n";
$htmlContent .= "    alert('Failed to copy to clipboard');\n";
$htmlContent .= "  });\n";
$htmlContent .= "}\n";
$htmlContent .= "</script>\n";
$htmlContent .= "</body>\n";
$htmlContent .= "</html>\n";

file_put_contents( $outputFile, $htmlContent );

echo "HTML tables written to {$outputFile}\n";

if ( PHP_OS_FAMILY === 'Darwin' ) {
	$cmd = [ 'open', $outputFile ];
	echo "Opening in browser...\n";
	passthru( implode( ' ', array_map( 'escapeshellarg', $cmd ) ) );
} elseif ( PHP_OS_FAMILY === 'Windows' ) {
	$cmd = [ 'cmd', '/c', 'start', '', $outputFile ];
	echo "Opening in browser...\n";
	passthru( implode( ' ', array_map( 'escapeshellarg', $cmd ) ) );
} elseif ( PHP_OS_FAMILY === 'Linux' ) {
	$cmd = [ 'xdg-open', $outputFile ];
	echo "Opening in browser...\n";
	passthru( implode( ' ', array_map( 'escapeshellarg', $cmd ) ) );
} else {
	echo "\nYou can open the file in your browser: {$outputFile}\n";
}

echo "\nClick the 'Copy WordPress HTML' button next to any section to copy its Gutenberg-ready HTML.\n";
