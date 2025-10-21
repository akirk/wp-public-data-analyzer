# WordPress Public Data Analyzer

Analyzes WordPress plugin data from [wp-public-data](https://github.com/dd32/wp-public-data) to identify plugins with and without blueprints.

## Requirements

- PHP 7.4+
- ext-zip
- ext-curl

## Usage

```bash
# Download/update data (auto-updates if older than 7 days)
composer update

# Analyze plugins and generate HTML tables
composer build

# Individual commands
composer analyze:plugins-with-blueprints
composer analyze:plugins-without-blueprints
composer analyze:plugins-top-100
composer generate:html
```

## Output

- `plugins-with-blueprints.json` - All plugins with blueprints
- `plugins-without-blueprints.json` - Top 100 plugins without blueprints
- `plugins-top-100.json` - Top 100 plugins by active installs
- `tables.html` - Interactive HTML tables with copy buttons for WordPress Gutenberg

The HTML file opens automatically in your browser. Click "Copy WordPress HTML" buttons to copy Gutenberg-ready markup.
