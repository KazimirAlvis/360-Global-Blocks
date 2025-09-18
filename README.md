# 360 Global Blocks

A comprehensive WordPress Gutenberg blocks plugin designed specifically for healthcare and medical practice websites. This plugin provides a collection of custom blocks that integrate seamlessly with medical practice management systems and patient engagement tools.

## Features

### 🏥 Popular Practices Block

Showcase clinic networks with logos, names, and clickable links. Integrates with custom clinic post types or can display random practices.

### 💊 Info Cards Block

Three-column medical information cards featuring:

-   12 curated medical icons from Health Icons library
-   Customizable titles and descriptions
-   Responsive grid layout

### 🎯 Hero Blocks

-   **Full Hero Block**: Full-page hero with background image and assessment CTA
-   **Simple Hero Block**: Clean page title display

### 📋 Assessment Integration

CTA and content blocks with built-in PatientReach360 assessment integration for patient engagement.

### 🖼️ Two Column Slider

Interactive content slider featuring:

-   Text content with images
-   Navigation arrows and dots
-   Autoplay functionality
-   Responsive design

### 👨‍⚕️ Find Doctor Block

Dedicated doctor search integration with customizable content and imagery.

### 📰 Latest Articles Block

Display recent blog posts and medical articles with:

-   Configurable post count
-   Excerpt length control
-   Featured image support
-   Responsive grid layout

### 📹 Video Two Column Block

Video content display with:

-   YouTube embed support
-   Assessment integration
-   Responsive design

## Medical Focus

Built specifically for healthcare websites with features like:

-   **Health Icons Integration**: 1000+ medical icons (CC0 licensed)
-   **Clinic Post Type Support**: Automatic integration with clinic management
-   **Patient Assessment Tools**: PatientReach360 integration
-   **Medical-focused Styling**: Healthcare-appropriate designs
-   **Accessibility Compliance**: Built with healthcare accessibility in mind

## Installation

1. Upload the plugin files to `/wp-content/plugins/360-global-blocks/`
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Navigate to any page/post editor to find the blocks under "360 Blocks" category

## Requirements

-   WordPress 5.0+
-   PHP 7.4+
-   Node.js 14+ (for development)

## Development

### Setup

```bash
npm install
```

### Build all blocks

```bash
npm run build
```

### Build individual blocks

```bash
npm run build:info-cards
npm run build:popular-practices
npm run build:two-column-slider
# etc.
```

### Development mode (watch)

```bash
npm run dev
```

## Block Structure

Each block follows WordPress best practices:

-   `block.json` for metadata
-   React components for editor interface
-   PHP render callbacks for frontend
-   Separate SCSS files for styling
-   Individual build processes

## Tech Stack

-   **WordPress Gutenberg API**: Block development framework
-   **React/JSX**: Interactive editor components
-   **SCSS**: Modular styling system
-   **wp-scripts**: WordPress build tools
-   **Health Icons**: Medical icon library

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/new-block`)
3. Make your changes
4. Build the blocks (`npm run build`)
5. Commit your changes (`git commit -am 'Add new block'`)
6. Push to the branch (`git push origin feature/new-block`)
7. Create a Pull Request

## License

This project is licensed under the GPL v2 or later - see the [LICENSE](LICENSE) file for details.

## Support

For support and feature requests, please create an issue on GitHub.

## Changelog

### 1.0.0

-   Initial release
-   8 custom blocks for healthcare websites
-   Health Icons integration
-   PatientReach360 assessment integration
-   Responsive designs for all blocks
