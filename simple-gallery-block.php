<?php
/**
 * Plugin Name: Simple Gallery Block
 * Plugin URI: https://github.com/juliasummersdev/simple-gallery-block
 * Description: Creates a masonry gallery within Gutenburg with lightbox support. Replaces Justified Gallery that comes with Powerkit.
 * Version: 0.1
 * Author: Julia Summers
 * Author URI: https://juliasummers.dev
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: jsdev-simple-gallery-block
 * Requires at least: 6.0
 * Requires PHP: 8.0
 */

// Exit if accessed directly.
if (!defined('ABSPATH'))
{
    exit;
}

/**
 * Add settings link to plugins page
 */
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'jsdev_simple_gallery_block_add_settings_link');
function jsdev_simple_gallery_block_add_settings_link($links)
{
    $settings_link = '<a href="' . admin_url('options-general.php?page=jsdev-simple-gallery-block') . '">' . __('Settings') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}

/**
 * Modify plugin row meta to open links in new window
 */
add_filter('plugin_row_meta', 'jsdev_simple_gallery_block_modify_plugin_row_meta', 10, 2);
function jsdev_simple_gallery_block_modify_plugin_row_meta($links, $file)
{
    if ($file === plugin_basename(__FILE__)) {
        // Make all links open in new window
        $links = array_map(function($link) {
            // Add target="_blank" to links that don't already have it
            if (strpos($link, 'target=') === false && strpos($link, '<a ') !== false) {
                $link = str_replace('<a ', '<a target="_blank" ', $link);
            }
            return $link;
        }, $links);
    }
    return $links;
}

/**
 * Remove Powerkit Justified Gallery scripts and styles
 */
add_action('wp_enqueue_scripts', 'jsdev_remove_powerkit_justified_gallery_assets', 100);
add_action('admin_enqueue_scripts', 'jsdev_remove_powerkit_justified_gallery_assets', 100);
function jsdev_remove_powerkit_justified_gallery_assets()
{
    // Dequeue Powerkit Justified Gallery scripts
    wp_dequeue_script('jquery-justified-gallery');
    wp_deregister_script('jquery-justified-gallery');
    
    wp_dequeue_script('powerkit-justified-gallery');
    wp_deregister_script('powerkit-justified-gallery');
    
    // Dequeue Powerkit Justified Gallery styles
    wp_dequeue_style('jquery-justified-gallery');
    wp_deregister_style('jquery-justified-gallery');
    
    wp_dequeue_style('powerkit-justified-gallery');
    wp_deregister_style('powerkit-justified-gallery');
    
    // Additional Powerkit gallery-related scripts that might be loaded
    wp_dequeue_script('powerkit-gallery');
    wp_deregister_script('powerkit-gallery');
    
    wp_dequeue_style('powerkit-gallery');
    wp_deregister_style('powerkit-gallery');
    
    // Canvas plugin gallery-related assets
    wp_dequeue_script('canvas-justified-gallery');
    wp_deregister_script('canvas-justified-gallery');
    
    wp_dequeue_script('canvas-gallery');
    wp_deregister_script('canvas-gallery');
    
    wp_dequeue_style('canvas-justified-gallery');
    wp_deregister_style('canvas-justified-gallery');
    
    wp_dequeue_style('canvas-gallery');
    wp_deregister_style('canvas-gallery');
    
    // Generic justified gallery script that might be used by either plugin
    wp_dequeue_script('justified-gallery');
    wp_deregister_script('justified-gallery');
    
    wp_dequeue_style('justified-gallery');
    wp_deregister_style('justified-gallery');
    
    // Canvas block-specific styles
    wp_dequeue_style('canvas-justified-gallery-block-style');
    wp_deregister_style('canvas-justified-gallery-block-style');
}

/**
 * Add admin menu for gallery settings
 */
add_action('admin_menu', 'jsdev_simple_gallery_block_add_admin_menu');
function jsdev_simple_gallery_block_add_admin_menu()
{
    add_options_page(
        'Simple Gallery Settings',
        'Simple Gallery',
        'manage_options',
        'jsdev-simple-gallery-block',
        'jsdev_simple_gallery_block_settings_page'
    );
}

/**
 * Register settings
 */
add_action('admin_init', 'jsdev_simple_gallery_block_register_settings');
function jsdev_simple_gallery_block_register_settings()
{
    register_setting(
        'simple_gallery_block_settings',
        'simple_gallery_block_columns',
        array(
            'type' => 'integer',
            'default' => 4,
            'sanitize_callback' => 'jsdev_simple_gallery_block_sanitize_columns'
        )
    );

    register_setting(
        'simple_gallery_block_settings',
        'simple_gallery_block_thumbnail_size',
        array(
            'type' => 'string',
            'default' => 'medium',
            'sanitize_callback' => 'sanitize_text_field'
        )
    );

    register_setting(
        'simple_gallery_block_settings',
        'simple_gallery_block_lightbox_size',
        array(
            'type' => 'string',
            'default' => 'large',
            'sanitize_callback' => 'sanitize_text_field'
        )
    );

    register_setting(
        'simple_gallery_block_settings',
        'simple_gallery_block_show_all',
        array(
            'type' => 'integer',
            'default' => 0,
            'sanitize_callback' => 'jsdev_simple_gallery_block_sanitize_checkbox'
        )
    );

    register_setting(
        'simple_gallery_block_settings',
        'simple_gallery_block_initial_height',
        array(
            'type' => 'integer',
            'default' => 400,
            'sanitize_callback' => 'jsdev_simple_gallery_block_sanitize_initial_height_with_check'
        )
    );

    register_setting(
        'simple_gallery_block_settings',
        'simple_gallery_block_initial_height_mobile',
        array(
            'type' => 'integer',
            'default' => 300,
            'sanitize_callback' => 'jsdev_simple_gallery_block_sanitize_initial_height_mobile_with_check'
        )
    );

    register_setting(
        'simple_gallery_block_settings',
        'simple_gallery_block_mobile_breakpoint',
        array(
            'type' => 'integer',
            'default' => 768,
            'sanitize_callback' => 'jsdev_simple_gallery_block_sanitize_breakpoint_with_check'
        )
    );

    register_setting(
        'simple_gallery_block_settings',
        'simple_gallery_block_min_images',
        array(
            'type' => 'integer',
            'default' => 0,
            'sanitize_callback' => 'jsdev_simple_gallery_block_sanitize_min_images_with_check'
        )
    );

    register_setting(
        'simple_gallery_block_settings',
        'simple_gallery_block_overlay_bg_color',
        array(
            'type' => 'string',
            'default' => '#ffffff',
            'sanitize_callback' => 'sanitize_hex_color'
        )
    );

    register_setting(
        'simple_gallery_block_settings',
        'simple_gallery_block_button_bg_color',
        array(
            'type' => 'string',
            'default' => '#000000',
            'sanitize_callback' => 'sanitize_hex_color'
        )
    );

    register_setting(
        'simple_gallery_block_settings',
        'simple_gallery_block_button_text_color',
        array(
            'type' => 'string',
            'default' => '#ffffff',
            'sanitize_callback' => 'sanitize_hex_color'
        )
    );

    add_settings_section(
        'simple_gallery_block_main_section',
        'Gallery Display Settings',
        'jsdev_simple_gallery_block_section_callback',
        'jsdev-simple-gallery-block'
    );

    add_settings_field(
        'simple_gallery_block_columns',
        'Number of Columns',
        'jsdev_simple_gallery_block_columns_field_callback',
        'jsdev-simple-gallery-block',
        'simple_gallery_block_main_section'
    );

    add_settings_field(
        'simple_gallery_block_thumbnail_size',
        'Thumbnail Image Size',
        'jsdev_simple_gallery_block_thumbnail_size_field_callback',
        'jsdev-simple-gallery-block',
        'simple_gallery_block_main_section'
    );

    add_settings_field(
        'simple_gallery_block_lightbox_size',
        'Lightbox Image Size',
        'jsdev_simple_gallery_block_lightbox_size_field_callback',
        'jsdev-simple-gallery-block',
        'simple_gallery_block_main_section'
    );

    add_settings_field(
        'simple_gallery_block_show_all',
        'Show All Images',
        'jsdev_simple_gallery_block_show_all_field_callback',
        'jsdev-simple-gallery-block',
        'simple_gallery_block_main_section'
    );

    add_settings_field(
        'simple_gallery_block_initial_height',
        'Initial Gallery Height (Desktop)',
        'jsdev_simple_gallery_block_initial_height_field_callback',
        'jsdev-simple-gallery-block',
        'simple_gallery_block_main_section'
    );

    add_settings_field(
        'simple_gallery_block_initial_height_mobile',
        'Initial Gallery Height (Mobile)',
        'jsdev_simple_gallery_block_initial_height_mobile_field_callback',
        'jsdev-simple-gallery-block',
        'simple_gallery_block_main_section'
    );

    add_settings_field(
        'simple_gallery_block_mobile_breakpoint',
        'Mobile Breakpoint',
        'jsdev_simple_gallery_block_mobile_breakpoint_field_callback',
        'jsdev-simple-gallery-block',
        'simple_gallery_block_main_section'
    );

    add_settings_field(
        'simple_gallery_block_min_images',
        'Minimum Images for Overlay',
        'jsdev_simple_gallery_block_min_images_field_callback',
        'jsdev-simple-gallery-block',
        'simple_gallery_block_main_section'
    );

    add_settings_field(
        'simple_gallery_block_overlay_bg_color',
        'Overlay Background Color',
        'jsdev_simple_gallery_block_overlay_bg_color_field_callback',
        'jsdev-simple-gallery-block',
        'simple_gallery_block_main_section'
    );

    add_settings_field(
        'simple_gallery_block_button_bg_color',
        'Button Background Color',
        'jsdev_simple_gallery_block_button_bg_color_field_callback',
        'jsdev-simple-gallery-block',
        'simple_gallery_block_main_section'
    );

    add_settings_field(
        'simple_gallery_block_button_text_color',
        'Button Text Color',
        'jsdev_simple_gallery_block_button_text_color_field_callback',
        'jsdev-simple-gallery-block',
        'simple_gallery_block_main_section'
    );
}

/**
 * Sanitize column number input
 */
function jsdev_simple_gallery_block_sanitize_columns($input)
{
    $input = intval($input);
    if ($input < 1) {
        $input = 1;
    } elseif ($input > 12) {
        $input = 12;
    }
    return $input;
}

/**
 * Sanitize initial height input
 */
function jsdev_simple_gallery_block_sanitize_initial_height($input)
{
    $input = intval($input);
    if ($input < 100) {
        $input = 100;
    }
    return $input;
}

/**
 * Sanitize breakpoint input
 */
function jsdev_simple_gallery_block_sanitize_breakpoint($input)
{
    $input = intval($input);
    if ($input < 320) {
        $input = 320;
    }
    return $input;
}

/**
 * Sanitize checkbox input
 */
function jsdev_simple_gallery_block_sanitize_checkbox($input)
{
    // When checkbox is unchecked, $input will be null/empty
    // When checked, it will be '1'
    return !empty($input) ? 1 : 0;
}

/**
 * Sanitize initial height with show_all check
 */
function jsdev_simple_gallery_block_sanitize_initial_height_with_check($input)
{
    // Check if show_all checkbox is checked
    $show_all = isset($_POST['simple_gallery_block_show_all']) ? 1 : 0;

    // If show_all is checked, preserve the old value (ignore new input)
    if ($show_all) {
        return get_option('simple_gallery_block_initial_height', 400);
    }

    // If input is empty (because field was disabled), preserve old value
    if (empty($input)) {
        return get_option('simple_gallery_block_initial_height', 400);
    }

    // Otherwise, sanitize and save the new value
    return jsdev_simple_gallery_block_sanitize_initial_height($input);
}

/**
 * Sanitize initial height mobile with show_all check
 */
function jsdev_simple_gallery_block_sanitize_initial_height_mobile_with_check($input)
{
    // Check if show_all checkbox is checked
    $show_all = isset($_POST['simple_gallery_block_show_all']) ? 1 : 0;

    // If show_all is checked, preserve the old value (ignore new input)
    if ($show_all) {
        return get_option('simple_gallery_block_initial_height_mobile', 300);
    }

    // If input is empty (because field was disabled), preserve old value
    if (empty($input)) {
        return get_option('simple_gallery_block_initial_height_mobile', 300);
    }

    // Otherwise, sanitize and save the new value
    return jsdev_simple_gallery_block_sanitize_initial_height($input);
}

/**
 * Sanitize breakpoint with show_all check
 */
function jsdev_simple_gallery_block_sanitize_breakpoint_with_check($input)
{
    // Check if show_all checkbox is checked
    $show_all = isset($_POST['simple_gallery_block_show_all']) ? 1 : 0;

    // If show_all is checked, preserve the old value (ignore new input)
    if ($show_all) {
        return get_option('simple_gallery_block_mobile_breakpoint', 768);
    }

    // If input is empty (because field was disabled), preserve old value
    if (empty($input)) {
        return get_option('simple_gallery_block_mobile_breakpoint', 768);
    }

    // Otherwise, sanitize and save the new value
    return jsdev_simple_gallery_block_sanitize_breakpoint($input);
}

/**
 * Sanitize min images with show_all check
 */
function jsdev_simple_gallery_block_sanitize_min_images_with_check($input)
{
    // Check if show_all checkbox is checked
    $show_all = isset($_POST['simple_gallery_block_show_all']) ? 1 : 0;

    // If show_all is checked, preserve the old value (ignore new input)
    if ($show_all) {
        return get_option('simple_gallery_block_min_images', 0);
    }

    // If input is empty/null (because field was disabled), preserve old value
    if (!isset($input) || $input === '') {
        return get_option('simple_gallery_block_min_images', 0);
    }

    // Otherwise, sanitize and save the new value
    return absint($input);
}

/**
 * Settings section description
 */
function jsdev_simple_gallery_block_section_callback()
{
    echo '<p>Configure how galleries are displayed on your site.</p>';
}

/**
 * Columns field callback
 */
function jsdev_simple_gallery_block_columns_field_callback()
{
    $columns = get_option('simple_gallery_block_columns', 4);
    echo '<input type="number" name="simple_gallery_block_columns" value="' . esc_attr($columns) . '" min="1" max="12" class="small-text" />';
    echo '<p class="description">Set the number of columns for all galleries (1-12). Default is 4.</p>';
}

/**
 * Get available image sizes
 */
function jsdev_simple_gallery_block_get_image_sizes()
{
    $sizes = array();
    $image_sizes = get_intermediate_image_sizes();

    // Add full size
    $sizes['full'] = 'Full Size (Original)';

    // Get dimensions for each size
    foreach ($image_sizes as $size) {
        $width = get_option($size . '_size_w');
        $height = get_option($size . '_size_h');

        if ($width && $height) {
            $sizes[$size] = ucwords(str_replace('_', ' ', $size)) . " ({$width}x{$height})";
        } else {
            $sizes[$size] = ucwords(str_replace('_', ' ', $size));
        }
    }

    return $sizes;
}

/**
 * Thumbnail size field callback
 */
function jsdev_simple_gallery_block_thumbnail_size_field_callback()
{
    $thumbnail_size = get_option('simple_gallery_block_thumbnail_size', 'medium');
    $sizes = jsdev_simple_gallery_block_get_image_sizes();

    echo '<select name="simple_gallery_block_thumbnail_size">';
    foreach ($sizes as $value => $label) {
        $selected = selected($thumbnail_size, $value, false);
        echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($label) . '</option>';
    }
    echo '</select>';
    echo '<p class="description">Image size displayed in the gallery grid. Default is Medium.</p>';
}

/**
 * Lightbox size field callback
 */
function jsdev_simple_gallery_block_lightbox_size_field_callback()
{
    $lightbox_size = get_option('simple_gallery_block_lightbox_size', 'large');
    $sizes = jsdev_simple_gallery_block_get_image_sizes();

    echo '<select name="simple_gallery_block_lightbox_size">';
    foreach ($sizes as $value => $label) {
        $selected = selected($lightbox_size, $value, false);
        echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($label) . '</option>';
    }
    echo '</select>';
    echo '<p class="description">Image size displayed in the lightbox popup. Default is Large.</p>';
}

/**
 * Show all images field callback
 */
function jsdev_simple_gallery_block_show_all_field_callback()
{
    $show_all = get_option('simple_gallery_block_show_all', 0);
    echo '<label>';
    echo '<input type="checkbox" name="simple_gallery_block_show_all" value="1" ' . checked($show_all, 1, false) . ' />';
    echo ' Always show all images';
    echo '</label>';
    echo '<p class="description">When unchecked, galleries will show a limited number of images by default with a "View all images" button to reveal all images. Helpful when displaying a large number of images per gallery on a large post with multiple galleries.</p>';
}

/**
 * Initial height field callback (Desktop)
 */
function jsdev_simple_gallery_block_initial_height_field_callback()
{
    $initial_height = get_option('simple_gallery_block_initial_height', 400);
    $show_all = get_option('simple_gallery_block_show_all', 0);

    echo '<input type="number" name="simple_gallery_block_initial_height" value="' . esc_attr($initial_height) . '" min="100" step="50" class="small-text" ' . disabled($show_all, 1, false) . ' /> px';
    echo '<p class="description">Height (in pixels) to show on desktop before the "Show All Images" button. Default: 400px</p>';
}

/**
 * Initial height field callback (Mobile)
 */
function jsdev_simple_gallery_block_initial_height_mobile_field_callback()
{
    $initial_height_mobile = get_option('simple_gallery_block_initial_height_mobile', 300);
    $show_all = get_option('simple_gallery_block_show_all', 0);

    echo '<input type="number" name="simple_gallery_block_initial_height_mobile" value="' . esc_attr($initial_height_mobile) . '" min="100" step="50" class="small-text" ' . disabled($show_all, 1, false) . ' /> px';
    echo '<p class="description">Height (in pixels) to show on mobile before the "Show All Images" button. Default: 300px</p>';
}

/**
 * Mobile breakpoint field callback
 */
function jsdev_simple_gallery_block_mobile_breakpoint_field_callback()
{
    $mobile_breakpoint = get_option('simple_gallery_block_mobile_breakpoint', 768);
    $show_all = get_option('simple_gallery_block_show_all', 0);

    echo '<input type="number" name="simple_gallery_block_mobile_breakpoint" value="' . esc_attr($mobile_breakpoint) . '" min="320" step="1" class="small-text" ' . disabled($show_all, 1, false) . ' /> px';
    echo '<p class="description">Screen width (in pixels) below which mobile height is used. Default: 768px</p>';
}

/**
 * Minimum images field callback
 */
function jsdev_simple_gallery_block_min_images_field_callback()
{
    $min_images = get_option('simple_gallery_block_min_images', 0);
    $show_all = get_option('simple_gallery_block_show_all', 0);

    echo '<input type="number" name="simple_gallery_block_min_images" value="' . esc_attr($min_images) . '" min="0" step="1" class="small-text" ' . disabled($show_all, 1, false) . ' /> images';
    echo '<p class="description">Only show overlay and "Show All Images" button if gallery has MORE than this many images. Set to 0 to always show for any gallery. Default: 0</p>';
}

/**
 * Overlay background color field callback
 */
function jsdev_simple_gallery_block_overlay_bg_color_field_callback()
{
    $overlay_bg_color = get_option('simple_gallery_block_overlay_bg_color', '#ffffff');
    $show_all = get_option('simple_gallery_block_show_all', 0);

    echo '<input type="text" name="simple_gallery_block_overlay_bg_color" value="' . esc_attr($overlay_bg_color) . '" class="simple-gallery-color-picker" ' . disabled($show_all, 1, false) . ' />';
    echo '<p class="description">Background color for the overlay gradient. Default: #ffffff (white)</p>';
}

/**
 * Button background color field callback
 */
function jsdev_simple_gallery_block_button_bg_color_field_callback()
{
    $button_bg_color = get_option('simple_gallery_block_button_bg_color', '#000000');
    $show_all = get_option('simple_gallery_block_show_all', 0);

    echo '<input type="text" name="simple_gallery_block_button_bg_color" value="' . esc_attr($button_bg_color) . '" class="simple-gallery-color-picker" ' . disabled($show_all, 1, false) . ' />';
    echo '<p class="description">Background color for the "Show All Images" button. Default: #000000 (black)</p>';
}

/**
 * Button text color field callback
 */
function jsdev_simple_gallery_block_button_text_color_field_callback()
{
    $button_text_color = get_option('simple_gallery_block_button_text_color', '#ffffff');
    $show_all = get_option('simple_gallery_block_show_all', 0);

    echo '<input type="text" name="simple_gallery_block_button_text_color" value="' . esc_attr($button_text_color) . '" class="simple-gallery-color-picker" ' . disabled($show_all, 1, false) . ' />';
    echo '<p class="description">Text color for the "Show All Images" button. Default: #ffffff (white)</p>';
}

/**
 * Settings page HTML
 */
function jsdev_simple_gallery_block_settings_page()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    // Enqueue WordPress color picker
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wp-color-picker');

    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form action="options.php" method="post">
            <?php
            settings_fields('simple_gallery_block_settings');
            do_settings_sections('jsdev-simple-gallery-block');
            submit_button('Save Settings');
            ?>
        </form>
        <script>
            jQuery(document).ready(function($) {
                $('.simple-gallery-color-picker').wpColorPicker();
            });
        </script>
    </div>
    <?php
}

/**
 * Enqueue frontend styles for the gallery
 */
add_action('wp_enqueue_scripts', 'jsdev_simple_gallery_block_enqueue_styles');
function jsdev_simple_gallery_block_enqueue_styles()
{
    wp_enqueue_style(
        'simple-gallery-block-style',
        plugins_url('assets/css/style.css', __FILE__),
        array(),
        filemtime(plugin_dir_path(__FILE__) . 'assets/css/style.css')
    );

    // Only enqueue GLightbox CSS if not already registered or enqueued by another plugin/theme
    if (!wp_style_is('glightbox', 'registered') && !wp_style_is('glightbox', 'enqueued')) {
        wp_enqueue_style(
            'glightbox',
            plugins_url('assets/css/glightbox.css', __FILE__),
            array(),
            filemtime(plugin_dir_path(__FILE__) . 'assets/css/glightbox.css')
        );
    }
}

/**
 * Enqueue frontend scripts for the gallery
 */
add_action('wp_enqueue_scripts', 'jsdev_simple_gallery_block_enqueue_scripts');
function jsdev_simple_gallery_block_enqueue_scripts()
{
    // Only enqueue GLightbox JS if not already registered or enqueued by another plugin/theme
    if (!wp_script_is('glightbox', 'registered') && !wp_script_is('glightbox', 'enqueued')) {
        wp_enqueue_script(
            'glightbox',
            plugins_url('assets/js/glightbox.min.js', __FILE__),
            array(),
            filemtime(plugin_dir_path(__FILE__) . 'assets/js/glightbox.min.js'),
            true
        );
    }

    // Enqueue initialization script with GLightbox as dependency
    // This will work whether GLightbox comes from our plugin or another source
    $dependencies = array();
    if (wp_script_is('glightbox', 'registered') || wp_script_is('glightbox', 'enqueued')) {
        $dependencies[] = 'glightbox';
    }

    wp_enqueue_script(
        'simple-gallery-init',
        plugins_url('assets/js/gallery-init.js', __FILE__),
        $dependencies,
        filemtime(plugin_dir_path(__FILE__) . 'assets/js/gallery-init.js'),
        true
    );
}

/**
 * Enqueue editor styles with dynamic column width
 */
add_action('enqueue_block_editor_assets', 'jsdev_simple_gallery_block_editor_styles');
function jsdev_simple_gallery_block_editor_styles()
{
    // Get column setting
    $columns = absint(get_option('simple_gallery_block_columns', 4));

    // Additional validation
    if ($columns < 1 || $columns > 12) {
        $columns = 4;
    }

    $width = number_format((100 / $columns), 4, '.', '');

    // Add inline CSS for editor
    $editor_css = "
        .simple-gallery-preview.mosaic-gallery {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -5px;
        }
        .simple-gallery-preview .sm-gallery-item {
            width: {$width}%;
            padding: 0 5px;
            margin-bottom: 10px;
            box-sizing: border-box;
        }
        .simple-gallery-preview .sm-gallery-item img {
            width: 100%;
            height: auto;
            display: block;
        }
    ";

    wp_add_inline_style('wp-edit-blocks', $editor_css);
}

/**
 * Register the block
 */
add_action('init', 'jsdev_register_simple_gallery_block');
function jsdev_register_simple_gallery_block()
{
    // Get global column setting
    $global_columns = absint(get_option('simple_gallery_block_columns', 4));
    if ($global_columns < 1 || $global_columns > 12) {
        $global_columns = 4;
    }

    // Register and enqueue masonry for both editor and frontend
    wp_register_script(
        'simple-gallery-masonry',
        plugins_url('assets/js/masonry.js', __FILE__),
        [],
        filemtime(plugin_dir_path(__FILE__) . 'assets/js/masonry.js')
    );

    // Enqueue masonry on frontend
    if (!is_admin()) {
        wp_enqueue_script('simple-gallery-masonry');
    }

    // Register block script with masonry as dependency
    wp_register_script(
        'simple-gallery-block-editor',
        plugins_url('assets/js/block.js', __FILE__),
        array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'simple-gallery-masonry'),
        filemtime(plugin_dir_path(__FILE__) . 'assets/js/block.js')
    );

    // Pass global column setting to JavaScript - must be done before enqueue
    wp_add_inline_script(
        'simple-gallery-block-editor',
        'window.simpleGallerySettings = ' . json_encode(array(
            'globalColumns' => $global_columns
        )) . ';',
        'before'
    );

    // Register editor styles
    wp_register_style(
        'simple-gallery-block-editor-style',
        plugins_url('assets/css/style.css?v=1', __FILE__),
        array(),
        @filemtime(plugin_dir_path(__FILE__) . 'assets/css/style.css?v=1')
    );

    // Register the block
    register_block_type('simple-gallery/gallery', array(
        'editor_script' => 'simple-gallery-block-editor',
        'editor_style' => 'simple-gallery-block-editor-style',
        'render_callback' => 'jsdev_render_simple_gallery_block',
        'attributes' => array(
            'images' => array(
                'type' => 'array',
                'default' => array(),
                'items' => array(
                    'type' => 'object',
                ),
            ),
            'columns' => array(
                'type' => 'integer',
                'default' => $global_columns,
            ),
        ),
    ));
}

/**
 * Server-side rendering for the gallery block
 */
function jsdev_render_simple_gallery_block($attributes)
{
    // No images
    if (empty($attributes['images']))
    {
        return '';
    }

    // Get column setting from block attributes (already has global default applied)
    $columns = isset($attributes['columns']) ? absint($attributes['columns']) : 4;

    // Validate columns
    if ($columns < 1 || $columns > 12) {
        $columns = 4;
    }

    $column_width = number_format((100 / $columns), 4, '.', '');

    // Get image size settings
    $thumbnail_size = get_option('simple_gallery_block_thumbnail_size', 'medium');
    $lightbox_size = get_option('simple_gallery_block_lightbox_size', 'large');

    // Get display settings
    $show_all = get_option('simple_gallery_block_show_all', 0);
    $initial_height = get_option('simple_gallery_block_initial_height', 400);
    $initial_height_mobile = get_option('simple_gallery_block_initial_height_mobile', 300);
    $mobile_breakpoint = get_option('simple_gallery_block_mobile_breakpoint', 768);
    $min_images = get_option('simple_gallery_block_min_images', 0);

    // Get color settings
    $overlay_bg_color = get_option('simple_gallery_block_overlay_bg_color', '#ffffff');
    $button_bg_color = get_option('simple_gallery_block_button_bg_color', '#000000');
    $button_text_color = get_option('simple_gallery_block_button_text_color', '#ffffff');

    // Get WordPress upload directory
    $upload_dir = wp_upload_dir();
    $upload_baseurl = $upload_dir['baseurl'];
    $upload_basepath = $upload_dir['basedir'];

    // Generate unique ID for this gallery instance
    $gallery_id = 'gallery-' . uniqid();

    // Count total images
    $total_images = count($attributes['images']);

    // Track gallery position on page (static counter across all gallery blocks)
    static $gallery_counter = 0;
    $gallery_counter++;
    $is_above_fold = $gallery_counter <= 2; // First 2 galleries assumed to be above/near fold

    // Generate unique class for this gallery's container
    $container_class = 'gallery-container-' . uniqid();

    // Start the output with inline styles for this specific gallery
    $output = '<style>
        @media (min-width: 1201px) {
            #' . esc_attr($gallery_id) . ' .sm-gallery-sizer,
            #' . esc_attr($gallery_id) . ' .sm-gallery-item {
                width: ' . esc_attr($column_width) . '%;
            }
        }
        .' . esc_attr($container_class) . ' .gallery-overlay-gradient {
            background: linear-gradient(to bottom, rgba(255,255,255,0) 0%, ' . esc_attr($overlay_bg_color) . ' 100%) !important;
        }
        .' . esc_attr($container_class) . ' .gallery-show-more-container {
            background-color: ' . esc_attr($overlay_bg_color) . ' !important;
        }
        .' . esc_attr($container_class) . ' .gallery-show-more-button {
            background-color: ' . esc_attr($button_bg_color) . ' !important;
            color: ' . esc_attr($button_text_color) . ' !important;
        }
    </style>';

    $output .= '<div class="mosaic-gallery-container ' . esc_attr($container_class) . (!$show_all && $total_images > $min_images ? ' has-show-more' : '') . '"><div id="' . esc_attr($gallery_id) . '" class="simple-gallery mosaic-gallery" data-initial-height="' . esc_attr($initial_height) . '" data-total-images="' . esc_attr($total_images) . '" data-lazy-gallery="' . ($is_above_fold ? 'false' : 'true') . '"><div class="sm-gallery-sizer"></div>';

    $image_index = 0;
    foreach ($attributes['images'] as $image)
    {
        $image_index++;
        // Validate image ID
        if (!isset($image['id'])) {
            continue;
        }

        $image_id = absint($image['id']);

        // Verify this is a valid attachment
        if (!$image_id || get_post_type($image_id) !== 'attachment') {
            continue;
        }

        // Get thumbnail image URL for gallery display
        $thumbnail_url = wp_get_attachment_image_url($image_id, $thumbnail_size);

        if (!$thumbnail_url) {
            continue;
        }

        // Get lightbox image URL
        $img_link = wp_get_attachment_image_url($image_id, $lightbox_size);

        if (!$img_link) {
            continue;
        }

        // Generate WebP path for lightbox image
        $img_link_webp = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $img_link);

        // Convert URL to file path for lightbox image
        $img_link_path = str_replace($upload_baseurl, $upload_basepath, $img_link);
        $img_link_webp_path = str_replace($upload_baseurl, $upload_basepath, $img_link_webp);

        // Validate paths are within uploads directory (prevent path traversal)
        $real_upload_path = realpath($upload_basepath);
        $real_webp_path = realpath(dirname($img_link_webp_path));

        // Check if WebP exists and set the final lightbox link
        $final_lightbox_link = $img_link;
        if ($real_webp_path && strpos($real_webp_path, $real_upload_path) === 0 && file_exists($img_link_webp_path)) {
            $final_lightbox_link = $img_link_webp;
        }

        // Generate WebP path for thumbnail image
        $thumbnail_webp = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $thumbnail_url);
        $thumbnail_path = str_replace($upload_baseurl, $upload_basepath, $thumbnail_url);
        $thumbnail_webp_path = str_replace($upload_baseurl, $upload_basepath, $thumbnail_webp);

        // Check if thumbnail WebP exists
        $final_thumbnail = $thumbnail_url;
        $real_thumbnail_dir = realpath(dirname($thumbnail_webp_path));
        if ($real_thumbnail_dir && strpos($real_thumbnail_dir, $real_upload_path) === 0 && file_exists($thumbnail_webp_path)) {
            $final_thumbnail = $thumbnail_webp;
        }

        // Get image alt text for accessibility
        $alt_text = get_post_meta($image_id, '_wp_attachment_image_alt', true);

        // Smart loading strategy based on gallery position:
        // - Galleries 1-2 (likely visible): Eager load first 14 images, lazy load rest
        // - Galleries 3+ (likely below fold): Lazy load all images
        // This balances performance with accurate Masonry height calculations
        if ($is_above_fold) {
            // Hybrid approach for above-fold galleries
            $loading_attr = ($image_index <= 14) ? 'eager' : 'lazy';
        } else {
            // Full lazy loading for below-fold galleries
            $loading_attr = 'lazy';
        }

        $output .= '<div class="sm-gallery-item" data-index="' . esc_attr($image_index) . '">';
        $output .= '<a href="' . esc_url($final_lightbox_link) . '" class="pk-image-popup pk-zoom-icon-popup" data-gallery="' . esc_attr($gallery_id) . '">';
        $output .= '<img src="' . esc_url($final_thumbnail) . '" alt="' . esc_attr($alt_text) . '" class="gallery-image" loading="' . esc_attr($loading_attr) . '" />';
        $output .= '</a>';
        $output .= '</div>';
    }

    // Close the gallery
    $output .= '</div>';

    // Add sliding overlay and button if needed (only if gallery has more than minimum images)
    if (!$show_all && $total_images > $min_images) {
        $output .= '<div class="gallery-sliding-overlay" data-initial-height="' . esc_attr($initial_height) . '" data-initial-height-mobile="' . esc_attr($initial_height_mobile) . '" data-mobile-breakpoint="' . esc_attr($mobile_breakpoint) . '">';
        $output .= '<div class="gallery-overlay-gradient"></div>';
        $output .= '<div class="gallery-show-more-container">';
        $output .= '<button type="button" class="gallery-show-more-button" style="background: ' . esc_attr($button_bg_color) . ' !important; color: ' . esc_attr($button_text_color) . ' !important;">';
        $output .= '<span class="gallery-show-more-text">Show All Images</span>';
        $output .= '</button>';
        $output .= '</div>';
        $output .= '</div>';
    }

    // Close the container
    $output .= '</div>';
    
    return $output;
}
