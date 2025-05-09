# Custom Swiper Carousel
A very simple WordPress plugin for creating customizable image carousels with lightbox using the Swiper.js library.

## Features

- Easy-to-use carousel management through WordPress admin
- Fully responsive design that works on all devices
- Multiple transition effects (slide, fade, cube, coverflow, flip, cards)
- Touch/swipe support with configurable behavior
- Built-in lightbox functionality
- Customizable navigation and pagination
- Autoplay with adjustable timing
- Multiple carousels on the same page
- Shortcode integration for easy placement anywhere
- Mobile-specific settings

## Installation

1. Upload the `custom-swiper-carousel` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to 'Carousels' in your admin menu to create and manage carousels

## Usage

1. Create a new carousel from the 'Carousels' menu
2. Add your images by clicking the 'Add Images' button
3. Configure carousel settings as needed
4. Save your carousel
5. Copy the shortcode provided (e.g. `[custom_carousel id="123"]`)
6. Paste the shortcode into any post, page, or widget where you want the carousel to appear

## Carousel Settings

### Basic Settings
- **Autoplay**: Enable/disable automatic slideshow
- **Autoplay Delay**: Time between slides (in milliseconds)
- **Show Navigation Arrows**: Display previous/next arrows
- **Show Pagination Dots**: Display dots for slide navigation
- **Infinite Loop**: Enable continuous looping
- **Enable Touch/Swipe**: Allow touch/mouse drag navigation
- **Enable Lightbox**: Allow image enlargement on click

### Advanced Settings
- **Transition Effect**: Choose between slide, fade, cube, coverflow, flip, or cards
- **Animation Speed**: Duration of transition animation (in milliseconds)
- **Slides Per View**: Number of slides visible at once
- **Space Between Slides**: Gap between slides (in pixels)

### Mobile Settings
- **Hide Navigation Arrows on Mobile**: Remove arrows on small screens
- **Mobile Touch Only**: Enable touch only on mobile devices
- **Hide Navigation Arrows in Lightbox on Mobile**: Remove lightbox arrows on mobile
- **Enable Touch Swipe in Lightbox**: Allow swiping in fullscreen mode

## Shortcode Parameters

Use the shortcode with the carousel ID:
[custom_carousel id="123"]

## Requirements

- WordPress 5.0 or higher
- PHP 7.0 or higher

## Credits

- Uses [Swiper](https://swiperjs.com/) by Vladimir Kharlampidi

## License

This plugin is licensed under the GPL v2 or later.
