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
$htmlContent .= "body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen-Sans, Ubuntu, Cantarell, sans-serif; max-width: 1400px; margin: 60px auto 20px; padding: 20px; background: #fff; }\n";
$htmlContent .= ".wp-block-table table { border-collapse: collapse; width: 100%; margin-bottom: 30px; }\n";
$htmlContent .= ".wp-block-table th { background-color: #f5f5f5; font-weight: 600; padding: 12px; text-align: left; border: 1px solid #ddd; }\n";
$htmlContent .= ".wp-block-table td { padding: 10px; border: 1px solid #ddd; }\n";
$htmlContent .= ".wp-block-table tbody tr:nth-child(even) { background-color: #fafafa; }\n";
$htmlContent .= ".wp-block-table tbody tr:hover { background-color: #f0f0f0; }\n";
$htmlContent .= ".wp-block-table a { color: #0073aa; text-decoration: none; }\n";
$htmlContent .= ".wp-block-table a:hover { text-decoration: underline; }\n";
$htmlContent .= ".fixed-nav { position: fixed; top: 0; left: 0; right: 0; background: #0073aa; color: white; padding: 12px 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); z-index: 1000; display: flex; align-items: center; gap: 10px; overflow-x: auto; white-space: nowrap; }\n";
$htmlContent .= ".fixed-nav-title { font-weight: 600; margin-right: 10px; }\n";
$htmlContent .= ".fixed-nav a { color: white; text-decoration: none; border-radius: 3px; transition: background 0.2s; font-size: 14px; }\n";
$htmlContent .= ".fixed-nav a:hover { background: rgba(255,255,255,0.2); }\n";
$htmlContent .= ".fixed-nav-separator { color: rgba(255,255,255,0.5); }\n";
$htmlContent .= ".filter-input { padding: 6px 12px; border: none; border-radius: 3px; font-size: 14px; min-width: 200px; }\n";
$htmlContent .= ".filter-input:focus { outline: 2px solid rgba(255,255,255,0.5); }\n";
$htmlContent .= ".hidden { display: none !important; }\n";
$htmlContent .= ".section { position: relative; margin-bottom: 40px; }\n";
$htmlContent .= ".section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }\n";
$htmlContent .= ".copy-btn { background: #0073aa; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-size: 14px; }\n";
$htmlContent .= ".copy-btn:hover { background: #005a87; }\n";
$htmlContent .= ".copy-btn:active { background: #004368; }\n";
$htmlContent .= ".copy-btn.copied { background: #00a32a; }\n";
$htmlContent .= ".wordpress-html { display: none; }\n";
$htmlContent .= "h2.wp-block-heading { margin: 0; }\n";
$htmlContent .= ".wp-block-p2-task { display: flex; align-items: flex-start; gap: 12px; padding: 12px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 8px; background: #fff; }\n";
$htmlContent .= ".wp-block-p2-task__emoji-status { font-size: 18px; }\n";
$htmlContent .= ".wp-block-p2-task__checkbox-wrapper { display: none; }\n";
$htmlContent .= ".wp-block-p2-task__checkbox { width: 20px; height: 20px; border: 2px solid #ddd; border-radius: 50%; display: inline-block; background: #fff; }\n";
$htmlContent .= ".wp-block-p2-task__main { flex: 1; }\n";
$htmlContent .= ".wp-block-p2-task__content { line-height: 1.5; }\n";
$htmlContent .= ".wp-block-p2-task:hover { background: #f9f9f9; border-color: #0073aa; }\n";
$htmlContent .= ".wp-block-group { display: flex; flex-direction: column; }\n";
$htmlContent .= "</style>\n";
$htmlContent .= "</head>\n";
$htmlContent .= "<body>\n\n";

$htmlContent .= "<nav class=\"fixed-nav\">\n";
$htmlContent .= "<input type=\"text\" class=\"filter-input\" id=\"filterInput\" placeholder=\"Filter plugins...\" oninput=\"filterContent(this.value)\">\n";
$htmlContent .= "<span class=\"fixed-nav-separator\">|</span>\n";
$htmlContent .= "<span class=\"fixed-nav-title\">Jump to:</span>\n";
$htmlContent .= "<a href=\"#summary\">Summary</a>\n";
$htmlContent .= "<span class=\"fixed-nav-separator\">•</span>\n";
$htmlContent .= "<a href=\"#top-100-status\">Top 100 Preview Status</a>\n";
$htmlContent .= "<span class=\"fixed-nav-separator\">•</span>\n";
$htmlContent .= "<a href=\"#p2-tasks\">P2 Tasks</a>\n";
$htmlContent .= "<span class=\"fixed-nav-separator\">•</span>\n";
$htmlContent .= "<a href=\"#plugins-with-blueprints\">With Blueprints (" . number_format( count( $pluginsWithBlueprints ) ) . ")</a>\n";
$htmlContent .= "<span class=\"fixed-nav-separator\">•</span>\n";
$htmlContent .= "<a href=\"#plugins-without-blueprints\">Without Blueprints (Top 100)</a>\n";
$htmlContent .= "<span class=\"fixed-nav-separator\">•</span>\n";
$htmlContent .= "<a href=\"#plugins-top-100\">All Top 100</a>\n";
$htmlContent .= "</nav>\n\n";

$htmlContent .= "<h1>WordPress Plugin Statistics</h1>\n\n";

echo "Generating summary table...\n";
$wpSummaryHtml = "<!-- wp:heading -->\n";
$wpSummaryHtml .= "<h2 class=\"wp-block-heading\" id=\"summary\">Summary</h2>\n";
$wpSummaryHtml .= "<!-- /wp:heading -->\n\n";
$wpSummaryHtml .= "<!-- wp:table {\"hasFixedLayout\":false} -->\n";
$wpSummaryHtml .= "<figure class=\"wp-block-table\"><table><thead><tr><th></th><th>Plugins with Previews</th><th>Percent</th></tr></thead><tbody>";
$wpSummaryHtml .= "<tr><td>Top 100 Plugins (by active installs)</td><td>{$pluginsWithPreviewsTop100}</td><td>{$percentTop100}%</td></tr>";
$wpSummaryHtml .= "<tr><td>All Plugins</td><td>" . number_format( $pluginsWithPreviewsAll ) . "</td><td>{$percentAll}%</td></tr>";
$wpSummaryHtml .= "</tbody></table></figure>\n";
$wpSummaryHtml .= "<!-- /wp:table -->";

$htmlContent .= "<div class=\"section\">\n";
$htmlContent .= "<div class=\"section-header\">\n";
$htmlContent .= "<h2 class=\"wp-block-heading\" id=\"summary\">Summary</h2>\n";
$htmlContent .= "<button class=\"copy-btn\" onclick=\"copyToClipboard('summary-wp', this)\">Copy Table HTML</button>\n";
$htmlContent .= "</div>\n";
$htmlContent .= "<div class=\"wordpress-html\" id=\"summary-wp\">" . htmlspecialchars( $wpSummaryHtml ) . "</div>\n";
$htmlContent .= "<figure class=\"wp-block-table\"><table><thead><tr><th></th><th>Plugins with Previews</th><th>Percent</th></tr></thead><tbody>";
$htmlContent .= "<tr><td>Top 100 Plugins (by active installs)</td><td>{$pluginsWithPreviewsTop100}</td><td>{$percentTop100}%</td></tr>";
$htmlContent .= "<tr><td>All Plugins</td><td>" . number_format( $pluginsWithPreviewsAll ) . "</td><td>{$percentAll}%</td></tr>";
$htmlContent .= "</tbody></table></figure>\n";
$htmlContent .= "</div>\n\n";

echo "Generating Top 100 plugins preview status table...\n";
$wpTop100StatusHtml = "<!-- wp:heading -->\n";
$wpTop100StatusHtml .= "<h2 class=\"wp-block-heading\" id=\"top-100-status\">Which Plugins in the Top 100 have a Preview?</h2>\n";
$wpTop100StatusHtml .= "<!-- /wp:heading -->\n\n";
$wpTop100StatusHtml .= "<!-- wp:table {\"hasFixedLayout\":false} -->\n";
$wpTop100StatusHtml .= "<figure class=\"wp-block-table\"><table><thead><tr><th>Rank</th><th>Plugin Preview</th><th>Enabled</th></tr></thead><tbody>";

$top100TableRows = '';
$rank = 1;
foreach ( $pluginsTop100 as $slug => $plugin ) {
	$name = htmlspecialchars( html_entity_decode( $plugin['name'], ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
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
$htmlContent .= "<h2 class=\"wp-block-heading\" id=\"top-100-status\">Which Plugins in the Top 100 have a Preview?</h2>\n";
$htmlContent .= "<button class=\"copy-btn\" onclick=\"copyToClipboard('top-100-status-wp', this)\">Copy Table HTML</button>\n";
$htmlContent .= "</div>\n";
$htmlContent .= "<div class=\"wordpress-html\" id=\"top-100-status-wp\">" . htmlspecialchars( $wpTop100StatusHtml ) . "</div>\n";
$htmlContent .= "<figure class=\"wp-block-table\"><table><thead><tr><th>Rank</th><th>Plugin Preview</th><th>Enabled</th></tr></thead><tbody>";
$htmlContent .= $top100TableRows;
$htmlContent .= "</tbody></table></figure>\n";
$htmlContent .= "</div>\n\n";

echo "Generating P2 task list for plugins without previews...\n";
$wpP2TasksHtml = "<!-- wp:heading -->\n";
$wpP2TasksHtml .= "<h2 class=\"wp-block-heading\" id=\"p2-tasks\">Top 100 Plugins Without Previews - P2 Tasks</h2>\n";
$wpP2TasksHtml .= "<!-- /wp:heading -->\n\n";
$wpP2TasksHtml .= "<!-- wp:group -->\n";
$wpP2TasksHtml .= "<div class=\"wp-block-group\">\n";

$p2TasksHtml = '';
$rank = 1;
foreach ( $pluginsTop100 as $slug => $plugin ) {
	if ( isset( $plugin['preview_url'] ) && $plugin['preview_url'] !== false ) {
		$rank++;
		continue;
	}

	$name = html_entity_decode( $plugin['name'], ENT_QUOTES | ENT_HTML5, 'UTF-8' );
	$pluginUrl = "https://wordpress.org/plugins/{$slug}/";
	$playgroundUrl = "https://playground.wordpress.net/?plugin={$slug}";

	$blueprintData = [
		'steps' => [
			[
				'step' => 'installPlugin',
				'vars' => [
					'url' => $slug,
					'prs' => false,
					'permalink' => false,
				],
			],
		],
	];
	$blueprintJson = json_encode( $blueprintData );
	$blueprintBase64 = base64_encode( $blueprintJson );
	$stepLibraryUrl = "https://akirk.github.io/playground-step-library/#{$blueprintBase64}";

	$taskContent = "{$rank}: <a href=\"{$pluginUrl}\">{$name}</a>: <a href=\"{$playgroundUrl}\">Playground</a>, <a href=\"{$stepLibraryUrl}\">Step Library</a>";

	$task = "<!-- wp:p2/task -->\n";
	$task .= "<div class=\"wp-block-p2-task\"><div><span class=\"wp-block-p2-task__emoji-status\" title=\"Pending\">⬜ </span><div class=\"wp-block-p2-task__checkbox-wrapper\"><span title=\"Pending\" class=\"wp-block-p2-task__checkbox is-disabled is-aria-checked-false\"></span></div></div><div class=\"wp-block-p2-task__main\"><div class=\"wp-block-p2-task__left\"><div class=\"wp-block-p2-task__content-wrapper\"><span class=\"wp-block-p2-task__content\">{$taskContent}</span></div><div class=\"wp-block-p2-task__dates\"></div></div><div class=\"wp-block-p2-task__right\"><div class=\"wp-block-p2-task__assignees-avatars\"></div></div></div></div>\n";
	$task .= "<!-- /wp:p2/task -->\n\n";

	$wpP2TasksHtml .= $task;
	$p2TasksHtml .= $task;
	$rank++;
}

$wpP2TasksHtml .= "</div>\n";
$wpP2TasksHtml .= "<!-- /wp:group -->";

$htmlContent .= "<div class=\"section\">\n";
$htmlContent .= "<div class=\"section-header\">\n";
$htmlContent .= "<h2 class=\"wp-block-heading\" id=\"p2-tasks\">Top 100 Plugins Without Previews - P2 Tasks</h2>\n";
$htmlContent .= "<button class=\"copy-btn\" onclick=\"copyToClipboard('p2-tasks-wp', this)\">Copy Table HTML</button>\n";
$htmlContent .= "</div>\n";
$htmlContent .= "<div class=\"wordpress-html\" id=\"p2-tasks-wp\">" . htmlspecialchars( $wpP2TasksHtml ) . "</div>\n";
$htmlContent .= "<div class=\"wp-block-group\">\n";
$htmlContent .= $p2TasksHtml;
$htmlContent .= "</div>\n";
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
		$wpSectionHtml .= "<th>Step Library</th>";
	}

	$wpSectionHtml .= "</tr></thead><tbody>";

	$tableHeaderHtml = '<thead><tr>';
	$tableHeaderHtml .= "<th>Plugin Name</th>";
	$tableHeaderHtml .= "<th>Downloads</th>";
	$tableHeaderHtml .= "<th>Active Installs</th>";
	if ( isset( $firstPlugin['preview_url'] ) ) {
		$tableHeaderHtml .= "<th>Preview</th>";
	} elseif ( isset( $firstPlugin['url'] ) ) {
		$tableHeaderHtml .= "<th>Step Library</th>";
	}
	$tableHeaderHtml .= "</tr></thead>";

	$tableBodyHtml = '';
	$count = 0;
	foreach ( $section['data'] as $slug => $plugin ) {
		$count++;
		$name = htmlspecialchars( html_entity_decode( $plugin['name'], ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
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
			$stepLibraryUrl = htmlspecialchars( $plugin['url'] );
			$row .= "<td><a href=\"{$stepLibraryUrl}\" target=\"_blank\">Open in Step Library</a></td>";
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
	$htmlContent .= "<button class=\"copy-btn\" onclick=\"copyToClipboard('{$section['id']}-wp', this)\">Copy Table HTML</button>\n";
	$htmlContent .= "</div>\n";
	$htmlContent .= "<div class=\"wordpress-html\" id=\"{$section['id']}-wp\">" . htmlspecialchars( $wpSectionHtml ) . "</div>\n";
	$htmlContent .= '<figure class="wp-block-table"><table>' . $tableHeaderHtml . '<tbody>';
	$htmlContent .= $tableBodyHtml;
	$htmlContent .= "</tbody></table></figure>\n";
	$htmlContent .= "</div>\n\n";

	echo "Generated table with {$count} plugins\n\n";
}

$htmlContent .= "<script>\n";
$htmlContent .= "function copyToClipboard(elementId, buttonElement) {\n";
$htmlContent .= "  const element = document.getElementById(elementId);\n";
$htmlContent .= "  const text = element.textContent;\n";
$htmlContent .= "  \n";
$htmlContent .= "  navigator.clipboard.writeText(text).then(() => {\n";
$htmlContent .= "    const originalText = buttonElement.textContent;\n";
$htmlContent .= "    buttonElement.textContent = 'Copied!';\n";
$htmlContent .= "    buttonElement.classList.add('copied');\n";
$htmlContent .= "    \n";
$htmlContent .= "    setTimeout(() => {\n";
$htmlContent .= "      buttonElement.textContent = originalText;\n";
$htmlContent .= "      buttonElement.classList.remove('copied');\n";
$htmlContent .= "    }, 2000);\n";
$htmlContent .= "  }).catch(err => {\n";
$htmlContent .= "    console.error('Failed to copy:', err);\n";
$htmlContent .= "    alert('Failed to copy to clipboard');\n";
$htmlContent .= "  });\n";
$htmlContent .= "}\n\n";
$htmlContent .= "function filterContent(searchText) {\n";
$htmlContent .= "  const filter = searchText.toLowerCase().trim();\n";
$htmlContent .= "  \n";
$htmlContent .= "  // Filter table rows\n";
$htmlContent .= "  document.querySelectorAll('.wp-block-table tbody tr').forEach(row => {\n";
$htmlContent .= "    const text = row.textContent.toLowerCase();\n";
$htmlContent .= "    const links = Array.from(row.querySelectorAll('a')).map(a => a.href.toLowerCase());\n";
$htmlContent .= "    const matchesText = text.includes(filter);\n";
$htmlContent .= "    const matchesUrl = links.some(url => url.includes(filter));\n";
$htmlContent .= "    \n";
$htmlContent .= "    if (filter === '' || matchesText || matchesUrl) {\n";
$htmlContent .= "      row.classList.remove('hidden');\n";
$htmlContent .= "    } else {\n";
$htmlContent .= "      row.classList.add('hidden');\n";
$htmlContent .= "    }\n";
$htmlContent .= "  });\n";
$htmlContent .= "  \n";
$htmlContent .= "  // Filter P2 tasks\n";
$htmlContent .= "  document.querySelectorAll('.wp-block-p2-task').forEach(task => {\n";
$htmlContent .= "    const text = task.textContent.toLowerCase();\n";
$htmlContent .= "    const links = Array.from(task.querySelectorAll('a')).map(a => a.href.toLowerCase());\n";
$htmlContent .= "    const matchesText = text.includes(filter);\n";
$htmlContent .= "    const matchesUrl = links.some(url => url.includes(filter));\n";
$htmlContent .= "    \n";
$htmlContent .= "    if (filter === '' || matchesText || matchesUrl) {\n";
$htmlContent .= "      task.classList.remove('hidden');\n";
$htmlContent .= "    } else {\n";
$htmlContent .= "      task.classList.add('hidden');\n";
$htmlContent .= "    }\n";
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

echo "\nClick the 'Copy Table HTML' button next to any section to copy its Gutenberg-ready HTML.\n";
