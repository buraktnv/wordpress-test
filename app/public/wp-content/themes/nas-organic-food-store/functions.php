<?php
/**
 * Organic Food Store functions and definitions
 *
 * @package Organic Food Store
 */
add_action( 'wp_ajax_get_product_info', 'my_get_product_info' );
add_action( 'wp_ajax_nopriv_get_product_info', 'my_get_product_info' );
function my_get_product_info() {
    $product_id = isset($_REQUEST['product_id']) ? absint($_REQUEST['product_id']) : 0;
    if ( ! $product_id ) {
        wp_send_json_error('Missing product ID');
    }
    $product = wc_get_product( $product_id );
    if ( ! $product ) {
        wp_send_json_error('Invalid product');
    }
    $data = [
        'id'            => $product->get_id(),
        'name'          => $product->get_name(),
        'slug'          => $product->get_slug(),
        'price'         => $product->get_price(),
        'regular_price' => $product->get_regular_price(),
        'groupcode'     => $product->get_parent_id(),
        'type'          => $product->get_type(),
        'in_stock'      => $product->is_in_stock(),
        'attributes'    => $product->get_attributes(),
        'image'         => wp_get_attachment_url( $product->get_image_id() ),
    ];
    wp_send_json_success( $data );
}

add_action( 'wp_enqueue_scripts', function(){
    // Enqueue jQuery + our shop.js in the footer
    wp_enqueue_script(
      'my-shop-script',
      get_stylesheet_directory_uri() . '/js/shop.js',
      [ 'jquery' ],
      '1.0',
      true
    );
    // Pass admin-ajax.php URL to JS
    wp_localize_script( 'my-shop-script', 'MyShop', [
        'ajax_url' => admin_url( 'admin-ajax.php' ),
    ] );
});

add_action( 'wp_footer', 'enqueue_add_to_cart_ajax_via_query' );
function enqueue_add_to_cart_ajax_via_query() {
    if ( ! function_exists( 'is_woocommerce' ) ) {
        return;
    }
    ?>
    <script>
    ;(function($){
      'use strict';

      /**
       * Fetches updated cart fragments and applies them to the page, then fires the Woo event.
       */
      const refreshFragments = async () => {
        try {
          const data = await $.get('?wc-ajax=get_refreshed_fragments');
          if (data && data.fragments) {
            $.each(data.fragments, (selector, html) => {
              $(selector).replaceWith(html);
            });
            $(document.body).trigger('added_to_cart', [ data.fragments, data.cart_hash ]);
          }
        } catch (err) {
          console.error('Fragment refresh failed:', err);
        }
      };

      /**
       * Adds a product (simple or variation) to the cart via AJAX,
       * then refreshes fragments twice (immediately + retry after 1s).
       *
       * Usage examples:
       *   window.addToCart( productId );                    // simple, qty = 1
       *   window.addToCart( productId, 3 );                 // simple, qty = 3
       *   window.addToCart( parentId, 1, variationId );     // variation, qty = 1
       *
       * @param {number|string} productId     — ID of the simple product or parent ID for variations
       * @param {number}          [quantity=1] — Quantity to add
       * @param {number|string}   [variationId] — If set, this ID is used instead of productId
       */
      window.addToCart = async (productId, quantity = 1, variationId) => {
        const id  = variationId ?? productId;
        const params = new URLSearchParams({
          'add-to-cart': id,
          'quantity':    quantity
        });

        try {
          await $.get('?' + params.toString());

          setTimeout(refreshFragments, 500);

          setTimeout(refreshFragments, 1500);
        } catch (err) {
          console.error('Add to cart failed:', err);
        }
      };

      window.getProductInfo = async ( productId ) => {
        try {
          const resp = await $.getJSON( woocommerce_params.ajax_url, {
            action:     'get_product_info',
            product_id: productId
          });
          if ( resp.success ) {
            return resp.data;
          }
          throw new Error( resp.data );
        } catch (err) {
          console.error('Error fetching product info:', err);
          throw err;
        }
      }
    })(jQuery);
    </script>

    <?php
}

if ( ! function_exists( 'organic_food_store_setup' ) ) :
function organic_food_store_setup() {
	
	if ( ! isset( $content_width ) )
		$content_width = 640; /* pixels */

	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'custom-logo', array(
		'height'      => 240,
		'width'       => 240,
		'flex-height' => true,
	) );
	
	add_theme_support( 'custom-background', array(
		'default-color' => 'ffffff'
	) );
	
	// Add support for Block Styles.
	add_theme_support( 'wp-block-styles' );

	// Add support for full and wide align images.
	add_theme_support( 'align-wide' );
			
	// Add support for responsive embedded content.
	add_theme_support( 'responsive-embeds' );
	add_filter( 'should_load_separate_core_block_assets', '__return_false' );
	
}
endif; // organic_food_store_setup
add_action( 'after_setup_theme', 'organic_food_store_setup' );

function organic_food_store_scripts() {
	wp_enqueue_style( 'nas-organic-food-store-basic-style', get_stylesheet_uri() );
}
add_action( 'wp_enqueue_scripts', 'organic_food_store_scripts' );

// Block Patterns.
require get_template_directory() . '/block-patterns.php';
/**
 * Load core file
 */
require get_theme_file_path() . '/inc/core/init.php';

/**
 * Theme info
 */
require get_theme_file_path( '/inc/theme-info/theme-info.php' );

/**
 * Getting started notification
 */
require get_theme_file_path( '/inc/getting-started/getting-started.php' );
