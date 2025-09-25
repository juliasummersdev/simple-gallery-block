<?php
/**
 * Plugin Name: Simple Gallery Block
 * Description: Replaces the awful Justified Gallery that comes bundled with the Powerkit plugin
 * Version: 1.0.0
 * Author: Your Mom
 */

// Exit if accessed directly.
if (!defined('ABSPATH'))
{
    exit;
}

/**
 * Remove Powerkit Justified Gallery scripts and styles
 */
add_action('wp_enqueue_scripts', 'remove_powerkit_justified_gallery_assets', 100);
add_action('admin_enqueue_scripts', 'remove_powerkit_justified_gallery_assets', 100);
function remove_powerkit_justified_gallery_assets()
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
 * Enqueue frontend styles for the gallery
 */
add_action('wp_enqueue_scripts', 'simple_gallery_block_enqueue_styles');
function simple_gallery_block_enqueue_styles()
{
    wp_enqueue_style(
        'simple-gallery-block-style',
        plugins_url('style.css', __FILE__),
        array(),
        filemtime(plugin_dir_path(__FILE__) . 'style.css')
    );
}

/**
 * Register the block
 */
add_action('init', 'register_simple_gallery_block');
function register_simple_gallery_block()
{
    // Register block script
    wp_register_script(
        'simple-gallery-block-editor',
        plugins_url('block.js', __FILE__),
        array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components'),
        filemtime(plugin_dir_path(__FILE__) . 'block.js')
    );
    
    // Register masonry
    wp_enqueue_script(
        'simple-gallery-masonry',
        plugins_url('masonry.js', __FILE__),
        [],
        filemtime(plugin_dir_path(__FILE__) . 'masonry.js')
    );
    
    // Register editor styles
    wp_register_style(
        'simple-gallery-block-editor-style',
        plugins_url('style.css?v=1', __FILE__),
        array(),
        @filemtime(plugin_dir_path(__FILE__) . 'style.css?v=1')
    );
    
    // Register the block
    register_block_type('simple-gallery/gallery', array(
        'editor_script' => 'simple-gallery-block-editor',
        'editor_style' => 'simple-gallery-block-editor-style',
        'render_callback' => 'render_simple_gallery_block',
        'attributes' => array(
            'images' => array(
                'type' => 'array',
                'default' => array(),
                'items' => array(
                    'type' => 'object',
                ),
            ),
        ),
    ));
}

/**
 * Server-side rendering for the gallery block
 */
function render_simple_gallery_block($attributes)
{
    // No images
    if (empty($attributes['images']))
    {
        return '';
    }
    
    // Get WordPress upload directory
    $upload_dir = wp_upload_dir();
    $upload_baseurl = $upload_dir['baseurl'];
    $upload_basepath = $upload_dir['basedir'];
    $image_size = '1536x1536';
    
    // Start the output
    $output = '<div class="mosaic-gallery-container"><div class="simple-gallery mosaic-gallery"><div class="sm-gallery-sizer"></div>';
    
    foreach ($attributes['images'] as $image)
    {
        $image_id = $image['id'];
        
        // Get registered image sizes needed for mobile breakpoints
        global $_wp_additional_image_sizes;
        $wp_sizes = array();
        $allowed_sizes = array($image_size);
        
        // Get default WordPress image sizes
        $default_image_sizes = get_intermediate_image_sizes();
        
        // Filter and add only the sizes we need
        foreach ($default_image_sizes as $size)
        {
            // Only include sizes in our allowed list
            if (in_array($size, $allowed_sizes))
            {
                /*if (in_array($size, array('thumbnail', 'medium', 'medium_large')))
                {
                    // Default WordPress sizes
                    $wp_sizes[$size] = intval(get_option($size . '_size_w'));
                }
                elseif (isset($_wp_additional_image_sizes[$size]))
                {*/
                    // Additional sizes registered by theme or plugins
                    $wp_sizes[$size] = intval($_wp_additional_image_sizes[$size]['width']);
                /*}*/
            }
        }
        
        // Get original image URL for lightbox (using medium_large as the largest size)
        $img_link = wp_get_attachment_image_url($image_id, $image_size);
        
        // Generate WebP path for lightbox image
        $img_link_webp = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $img_link);
        
        // Convert URL to file path for lightbox image
        $img_link_path = str_replace($upload_baseurl, $upload_basepath, $img_link);
        $img_link_webp_path = str_replace($upload_baseurl, $upload_basepath, $img_link_webp);
        
        // Check if WebP exists and set the final lightbox link
        $final_link = file_exists($img_link_webp_path) ? $img_link_webp : $img_link;
        
        // Build srcset with WebP if available, otherwise use original format
        $srcset = array();
        $image_urls = array();
        
        // Add each WordPress size to srcset
        foreach ($wp_sizes as $size_name => $width)
        {
            $img_url = wp_get_attachment_image_url($image_id, $size_name);
            if (!$img_url)
            {
                continue;
            } // Skip if size doesn't exist
            
            $image_urls[$size_name] = $img_url;
            
            // Generate WebP path
            $img_webp = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $img_url);
            
            // Convert URL to file path
            $img_path = str_replace($upload_baseurl, $upload_basepath, $img_url);
            $img_webp_path = str_replace($upload_baseurl, $upload_basepath, $img_webp);
            
            // Use WebP if available, otherwise use original
            if (file_exists($img_webp_path))
            {
                $srcset[] = esc_url($img_webp) . ' ' . $width . 'w';
            }
            else
            {
                $srcset[] = esc_url($img_url) . ' ' . $width . 'w';
            }
        }
        
        // Join srcset entries with commas
        $srcset_attr = implode(', ', $srcset);
        
        // Determine default src (with WebP fallback) - using medium_large as default
        $default_img = isset($image_urls[$image_size]) ? $image_urls[$image_size] : reset($image_urls);
        $default_img_webp = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $default_img);
        $default_img_webp_path = str_replace($upload_baseurl, $upload_basepath, $default_img_webp);
        
        $default_src = file_exists($default_img_webp_path) ? $default_img_webp : $default_img;
        
        $output .= '
            <div class="sm-gallery-item">
                <a href="' . esc_url($final_link) . '" class="pk-image-popup pk-zoom-icon-popup" data-gallery="gallery-group">
                    <img src="' . esc_url($final_link) . '" alt="" class="gallery-image" loading="lazy" />
                </a>
            </div>
        ';
    }
    
    // Close the output
    $output .= '</div></div>';
    
    return $output;
}
