# Random Quotes Plugin

A lightweight WordPress plugin for displaying random quotes via widget, shortcode, or PHP function.

---

## Features

### 1. Custom Post Type: `randquotes`
- Adds a "Quotes" section in your WordPress admin
- Each quote supports a title, content, and author
- SEO-friendly URLs using `/randquotes/` slug

### 2. Widget Support
- "Random Quote" widget
- Configurable title and show/hide author option
- Found in **Appearance > Customize > Widgets**
- Drag-and-drop into any widget area

### 3. Shortcode Support
Use in posts, pages, or text widgets:

- `[random_quote]`
- `[random_quote show_author="false"]`
- `[random_quote class="my-custom-class"]`

### 4. PHP Functions
Use in your theme templates:

- `display_random_quote()`
- `display_random_quote(false, 'my-class')`
- `get_random_quote_data()`

---

## Installation

1. Save the plugin code as `random-quotes.php`
2. Upload it to `/wp-content/plugins/random-quotes/`
3. Go to **Plugins > Installed Plugins** and activate it
4. A **Quotes** menu will appear in your admin panel

---

## Usage

1. Navigate to **Quotes Manager**
2. Click **+ Add Another Quote** to add entries
3. Fill in quote content and author
4. Click **Save All Quotes** to store them

---

The plugin includes basic styling and is fully customizable. Quotes appear in a styled blockquote with optional author attribution.
