# Simple Gallery Block

A WordPress plugin that creates a masonry gallery block with lightbox support for the Gutenberg editor. Designed as a replacement for Justified Gallery that comes with Powerkit.

![Version](https://img.shields.io/badge/version-0.1-blue.svg)
![WordPress](https://img.shields.io/badge/wordpress-6.0%2B-blue.svg)
![PHP](https://img.shields.io/badge/php-8.0%2B-blue.svg)
![License](https://img.shields.io/badge/license-GPL%20v2%2B-blue.svg)

## Features

- Masonry grid layout with customizable columns (1-12)
- GLightbox integration for lightbox functionality
- Responsive design with separate mobile/desktop settings
- "Show All Images" button with customizable reveal height
- WebP image support with automatic fallback
- Lazy loading for below-fold galleries
- Per-gallery column customization
- Global settings with per-gallery overrides
- Customizable overlay and button colors

## Installation

1. Download or clone this repository into your WordPress plugins directory:
   ```
   wp-content/plugins/simple-gallery-block/
   ```

2. Activate the plugin through the WordPress admin panel:
   - Navigate to **Plugins** in the WordPress admin
   - Find **Simple Gallery Block**
   - Click **Activate**

3. Configure global settings:
   - Go to **Settings → Simple Gallery** in the WordPress admin
   - Adjust default columns, image sizes, overlay settings, and colors

## Usage

### Adding a Gallery Block

1. In the WordPress editor, add a new block
2. Search for **Simple Gallery**
3. Click **Add Images** to select images from your media library
4. Adjust columns in the block sidebar (optional - uses global default if not changed)

### Block Settings

Each gallery block has the following options in the sidebar:

- **Columns:** Number of columns (1-12)
  - Defaults to the global setting
  - Override per gallery as needed

### Global Settings

Access global settings at **Settings → Simple Gallery**:

#### Display Settings

- **Number of Columns:** Default column count for all galleries (1-12, default: 4)
- **Thumbnail Image Size:** Image size for gallery grid (default: Medium)
- **Lightbox Image Size:** Image size for lightbox popup (default: Large)
- **Always Show All Images:** When unchecked, shows limited images with expand button

#### Overlay Settings (when "Always show all images" is unchecked)

- **Initial Gallery Height (Desktop):** Height before expand button (default: 400px)
- **Initial Gallery Height (Mobile):** Height before expand button on mobile (default: 300px)
- **Mobile Breakpoint:** Screen width for mobile behavior (default: 768px)
- **Minimum Images for Overlay:** Only show expand for galleries with more images (default: 0)

#### Color Settings

- **Overlay Background Color:** Background color for the overlay gradient (default: white)
- **Button Background Color:** "Show All Images" button background (default: black)
- **Button Text Color:** "Show All Images" button text (default: white)

## Features in Detail

### Masonry Layout

Uses Masonry.js to create a Pinterest-style cascading grid layout. The layout automatically adjusts based on image aspect ratios and screen size.

### Responsive Behavior

- **Desktop (1201px+):** Uses configured column count
- **Tablet (768px-1200px):** Auto-adjusts columns
- **Mobile (<768px):** Optimized for mobile viewing

### Smart Image Loading

- **Above-fold galleries (first 2):** First 14 images eager-loaded, rest lazy-loaded
- **Below-fold galleries:** All images lazy-loaded
- WebP images used automatically when available

### Overlay System

When "Always show all images" is unchecked:

- Gallery displays up to configured height
- Gradient overlay fades in at the bottom
- "Show All Images" button reveals full gallery
- Smooth slide-down animation
- Customizable colors for overlay and button

## Development

### File Structure

```
simple-gallery-block/
├── assets/
│   ├── css/
│   │   ├── style.css           # Main gallery styles
│   │   └── glightbox.css       # Lightbox styles
│   └── js/
│       ├── block.js            # Gutenberg block
│       ├── masonry.js          # Masonry layout
│       ├── gallery-init.js     # Frontend initialization
│       └── glightbox.min.js    # Lightbox library
├── simple-gallery-block.php    # Main plugin file
```

### Function Prefixes

All functions use the `jsdev_simple_gallery_block_` or `jsdev_` prefix to avoid conflicts.

### Hooks and Filters

The plugin uses WordPress standard hooks:
- `init` - Register block and scripts
- `admin_menu` - Add settings page
- `admin_init` - Register settings
- `wp_enqueue_scripts` - Enqueue frontend assets
- `enqueue_block_editor_assets` - Enqueue editor assets

## License

GPL v2 or later
