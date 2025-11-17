# VLThemes Toolkit

A comprehensive WordPress helper plugin that provides essential functionality for VLThemes projects. This plugin includes Elementor extensions, WooCommerce integration, custom fonts, social icons, and more.

## Features

- 🎨 **Elementor Extensions** - Custom CSS, Jarallax parallax, AOS animations, custom attributes
- 🛒 **WooCommerce Integration** - Enhanced WooCommerce functionality and utilities
- 📝 **Custom Fonts** - Easy custom font management
- 👥 **Social Icons** - Social sharing and icon management with 300+ icons
- 📊 **Post Views** - Track and display post view counts
- 🍞 **Breadcrumbs** - Customizable breadcrumb navigation
- 📋 **Demo Import** - Content import functionality
- 📁 **Upload Mimes** - Extended file upload support
- 🧩 **ACF Integration** - Advanced Custom Fields helpers
- 📧 **Contact Form 7 Integration** - CF7 utilities and helpers
- 🖼️ **Visual Portfolio Integration** - Portfolio management support

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher

## Installation

1. Upload the plugin files to `/wp-content/plugins/vlthemes-toolkit/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. The plugin will automatically register all modules

## Module Overview

### Core Features

#### Social Icons
Over 300 social icons with Sharer.js integration for easy social sharing.

```php
// Get all social icons
$social_icons = vlt_get_social_icons();

// Get share buttons for current post
echo vlt_get_post_share_buttons();

// Get share buttons for specific post with custom style
echo vlt_get_post_share_buttons(123, 'style-2');

// Customize which socials to show
add_filter('vlt_helper_post_share_socials', function($socials) {
    return ['facebook', 'twitter', 'linkedin', 'email'];
});
```

#### Post Views
Track and display post view counts.

```php
// Set/increment post views
vlt_set_post_views($post_id);

// Get post views
$views = vlt_get_post_views($post_id);
echo $views . ' views';

// Reset post views
vlt_reset_post_views($post_id);
```

#### Breadcrumbs
Display breadcrumb navigation.

```php
// Default breadcrumbs
echo vlt_breadcrumbs();

// Custom breadcrumbs
echo vlt_breadcrumbs([
    'separator' => ' / ',
    'home_text' => 'Home',
    'show_current' => true
]);
```

#### AOS (Animate On Scroll)
Add scroll-based animations to elements.

```php
// Get available animations
$animations = vlt_aos_get_animations();

// Render AOS attributes
echo vlt_aos_render('fade-up', [
    'duration' => 1000,
    'delay' => 100,
    'offset' => 200,
    'once' => 'true'
]);

// Output: data-aos="fade-up" data-aos-duration="1000" data-aos-delay="100"
```

### Integrations

#### Elementor Integration

**Custom CSS Extension**
Add custom CSS to any Elementor element or entire pages.

- Element-level custom CSS in Advanced tab
- Page-level custom CSS in Page Settings
- Automatic Pro CSS section removal when Pro is not active
- Support for `selector` placeholder

**Additional Extensions:**
- **Jarallax** - Background parallax effects
- **AOS** - Scroll animations integration
- **Element Parallax** - Element-based parallax
- **Layout Extensions** - Advanced layout controls
- **Custom Attributes** - Add custom HTML attributes

**Template Functions**
```php
// Get Elementor templates
$templates = vlt_get_elementor_templates();
$page_templates = vlt_get_elementor_templates('page');

// Render template
echo vlt_render_elementor_template($template_id);

// Check if built with Elementor
if (vlt_is_built_with_elementor()) {
    // Page is built with Elementor
}
```

**Widget Registration**
```php
// Load widget files
add_action('vlt_helper_elementor_register_widgets', function() {
    $widgets = ['widget1.php', 'widget2.php'];
    foreach ($widgets as $widget) {
        require_once get_template_directory() . '/elementor/widgets/' . $widget;
    }
});

// Register widget classes
add_filter('vlt_helper_elementor_widget_classes', function($classes) {
    return [
        '\Elementor\Widget_Custom_Heading',
        '\Elementor\Widget_Custom_Button',
    ];
});
```

#### WooCommerce Integration

```php
// Check if on WooCommerce page
if (vlt_is_woocommerce_page()) {
    // On any WooCommerce page
}

// Check specific pages
if (vlt_is_woocommerce_page('cart')) {
    // On cart page
}

if (vlt_is_woocommerce_page('checkout')) {
    // On checkout page
}

if (vlt_is_woocommerce_page('account')) {
    // On account page
}

// Check for specific endpoint
if (vlt_is_woocommerce_page('endpoint', 'edit-address')) {
    // On edit address endpoint
}
```

**Features:**
- Automatic WooCommerce default styles removal
- SelectWoo script dequeue
- Page detection utilities

#### Contact Form 7 Integration

```php
// Get all CF7 forms
$forms = vlt_get_cf7_forms();

// Render form
echo vlt_render_cf7_form($form_id);
```

#### Visual Portfolio Integration

```php
// Get portfolios
$portfolios = vlt_get_vp_portfolios();

// Render portfolio
echo vlt_render_vp_portfolio($portfolio_id);
```

#### ACF Integration

```php
// Populate ACF select with Elementor templates
add_filter('acf/load_field/name=my_template_field', function($field) {
    return vlt_acf_populate_elementor_templates($field);
});

// Populate with Visual Portfolio layouts
add_filter('acf/load_field/name=my_portfolio_field', function($field) {
    return vlt_acf_populate_vp_saved_layouts($field);
});

// Populate with social icons
add_filter('acf/load_field/name=my_social_field', function($field) {
    return vlt_acf_populate_social_icons($field);
});
```

### Custom Fonts

Register custom fonts for use throughout your theme.

```php
add_filter('vlt_helper_register_custom_fonts', function($fonts) {
    $fonts['Mulish'] = [
        'label' => 'Mulish',
        'variants' => ['300', '400', '500', '600', '700', '800'],
        'category' => 'theme_fonts',
        'category_label' => esc_html__('My Theme Fonts', 'textdomain'),
    ];

    $fonts['Montserrat'] = [
        'label' => 'Montserrat',
        'variants' => ['400', '500', '600', '700', '800'],
        'category' => 'theme_fonts',
        'category_label' => esc_html__('My Theme Fonts', 'textdomain'),
    ];

    return $fonts;
});
```

## Available Filters

### Social Icons
- `vlt_helper_social_icons` - Modify social icons list
- `vlt_helper_post_share_socials` - Customize enabled share buttons
- `vlt_helper_post_share_data` - Modify share data
- `vlt_helper_post_share_buttons` - Filter share buttons HTML
- `vlt_helper_user_contact_methods` - Modify user contact methods

### AOS Animations
- `vlt_helper_aos_animations` - Add custom animations

### Elementor
- `vlt_helper_elementor_widget_classes` - Register widget classes
- `vlt_helper_elementor_categories` - Modify widget categories
- `vlt_helper_elementor_locations` - Modify theme locations
- `vlt_helper_elementor_badge` - Customize widget badge
- `vlt_helper_elementor_custom_css_controls` - Add custom CSS controls
- `vlt_helper_elementor_custom_css_page_settings_controls` - Add page settings controls

### Custom Fonts
- `vlt_helper_register_custom_fonts` - Register custom fonts

### WooCommerce
- `vlt_helper_woocommerce_dequeue_scripts` - Dequeue additional scripts

## Available Actions

### Elementor
- `vlt_helper_elementor_register_widgets` - Load widget files
- `vlt_helper_elementor_widgets_registered` - After widgets registered

### Assets
- `vlt_helper/register_assets` - Register additional assets
- `vlt_helper/modules_loaded` - After all modules loaded

### WooCommerce
- `vlt_helper_woocommerce_dequeue_scripts` - Dequeue scripts action

## Helper Functions Reference

### General
```php
vlt_has_helper_plugin()           // Check if plugin is active
vlt_helper_plugin_instance()      // Get plugin instance
```

### Social Icons
```php
vlt_get_social_icons()                        // Get social icons list
vlt_get_post_share_buttons($post_id, $style)  // Get share buttons
```

### Post Views
```php
vlt_set_post_views($post_id)      // Set/increment views
vlt_get_post_views($post_id)      // Get view count
vlt_reset_post_views($post_id)    // Reset views
```

### Breadcrumbs
```php
vlt_breadcrumbs($args)            // Render breadcrumbs
```

### AOS
```php
vlt_aos_get_animations()          // Get available animations
vlt_aos_render($animation, $args) // Render AOS attributes
```

### Elementor
```php
vlt_get_elementor_templates($type)    // Get templates
vlt_render_elementor_template($id)    // Render template
vlt_is_built_with_elementor()         // Check if built with Elementor
```

### WooCommerce
```php
vlt_is_woocommerce_page($page, $endpoint)  // Check WooCommerce pages
```

### Contact Form 7
```php
vlt_get_cf7_forms()                   // Get all forms
vlt_render_cf7_form($id, $args)       // Render form
```

### Visual Portfolio
```php
vlt_get_vp_portfolios()               // Get portfolios
vlt_render_vp_portfolio($id, $args)   // Render portfolio
```

### ACF
```php
vlt_acf_populate_elementor_templates($field, $type)  // Populate with Elementor templates
vlt_acf_populate_vp_saved_layouts($field)           // Populate with VP layouts
vlt_acf_populate_social_icons($field)               // Populate with social icons
```

## Registered Assets

The plugin registers (but doesn't enqueue) the following assets for use by themes:

### Scripts
- `gsap` - GSAP animation library
- `scrolltrigger` - GSAP ScrollTrigger
- `scrolltoplugin` - GSAP ScrollTo plugin
- `textplugin` - GSAP Text plugin
- `observer` - GSAP Observer
- `draggable` - GSAP Draggable
- `jarallax` - Jarallax parallax library
- `jarallax-video` - Jarallax video extension
- `aos` - Animate On Scroll library
- `sharer` - Sharer.js social sharing

### Styles
- `jarallax` - Jarallax styles
- `aos` - AOS styles
- `socicons` - Social icons font

## Development

### Module Structure

All modules extend the `BaseModule` class and follow this structure:

```php
namespace VLT\Helper\Modules\Features;

use VLT\Helper\Modules\BaseModule;

class MyModule extends BaseModule {
    protected $name = 'my_module';
    protected $version = '1.0.0';

    protected function can_register() {
        // Optional: Add conditions for module loading
        return true;
    }

    public function register() {
        // Register hooks and functionality
    }
}
```

### Adding New Modules

1. Create module file in `includes/Modules/Features/` or `includes/Modules/Integrations/`
2. Extend `BaseModule` class
3. Register module in `includes/Helper.php` in `init_modules()` method

## Changelog

### Version 1.0.0
- Initial release
- Elementor extensions (Custom CSS, Jarallax, AOS, Custom Attributes)
- WooCommerce integration
- Social icons with Sharer.js
- Post views tracking
- Breadcrumbs
- Custom fonts
- ACF integration
- Contact Form 7 integration
- Visual Portfolio integration

## Credits

**Developed by:** VLThemes
**Version:** 1.0.0
**License:** GPL v2 or later

## Support

For support, please visit [VLThemes Support](https://themeforest.net/user/vlthemes)

---

Made with ❤️ by VLThemes
