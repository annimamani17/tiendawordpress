<?php
/**
 * HSN/SAC codes for the GST invoice template.
 *
 * The codes stay where the store keeps them (a GST plugin field, ACF, a WooCommerce attribute or a CSV import);
 * this class only reads them. Where to read from is an option of the template's HSN/SAC column, saved in the
 * template HTML as data-hsn-source (auto, meta or attr) and data-hsn-key (meta key or attribute name).
 *
 * @package Wf_Woocommerce_Packing_List
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Wt_Pklist_Gst_Hsn' ) ) {

	/**
	 * Reads HSN/SAC codes from products and lists the codes found on recently ordered products.
	 */
	class Wt_Pklist_Gst_Hsn {

		/**
		 * Product table column key.
		 */
		const COLUMN = 'hsn_code';

		/**
		 * Number of most recent orders whose products the customizer scan reads.
		 */
		const SCAN_ORDERS = 10;

		/**
		 * Source of the template being rendered.
		 *
		 * @var array|null
		 */
		private static $rendering = null;

		/**
		 * Source of the active invoice template, false when it has no HSN/SAC column.
		 *
		 * @var array|false|null
		 */
		private static $active = null;

		/**
		 * Register hooks.
		 */
		public static function register_hooks() {
			add_filter( 'wf_module_generate_template_html_for_invoice', array( __CLASS__, 'capture_template_source' ), 1, 2 );
			add_action( 'wp_ajax_wt_pklist_hsn_scan', array( __CLASS__, 'ajax_scan' ) );
			add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );
		}

		/*
		 * ------------------------------------------------------------------
		 * Source of the HSN/SAC column
		 * ------------------------------------------------------------------
		 */

		/**
		 * Normalised source.
		 *
		 * @param string $source auto, meta or attr.
		 * @param string $key    Meta key or attribute name.
		 * @return array { source, key }
		 */
		public static function make_source( $source, $key = '' ) {
			$source = in_array( $source, array( 'meta', 'attr' ), true ) ? $source : 'auto';
			$key    = trim( (string) $key );
			if ( 'auto' === $source || '' === $key ) {
				return array(
					'source' => 'auto',
					'key'    => '',
				);
			}
			return array(
				'source' => $source,
				'key'    => $key,
			);
		}

		/**
		 * Source saved on the HSN/SAC column of a template.
		 *
		 * @param string $html Template HTML.
		 * @return array|false Source, or false when the template has no HSN/SAC column.
		 */
		public static function source_from_html( $html ) {
			if ( ! preg_match( '/<th\b[^>]*\bcol-type="-?' . self::COLUMN . '"[^>]*>/i', (string) $html, $th ) ) {
				return false;
			}
			$source = preg_match( '/\bdata-hsn-source="([^"]*)"/i', $th[0], $match ) ? html_entity_decode( $match[1], ENT_QUOTES ) : 'auto';
			$key    = preg_match( '/\bdata-hsn-key="([^"]*)"/i', $th[0], $match ) ? html_entity_decode( $match[1], ENT_QUOTES ) : '';
			return self::make_source( $source, $key );
		}

		/**
		 * Remember the source of the invoice template being rendered, for the column and the HSN summary.
		 *
		 * @param array  $find_replace Placeholders.
		 * @param string $html         Template HTML.
		 * @return array
		 */
		public static function capture_template_source( $find_replace, $html ) {
			$source          = self::source_from_html( $html );
			self::$rendering = $source ? $source : null;
			return $find_replace;
		}

		/**
		 * Source of the active invoice template.
		 *
		 * @return array|false Source, or false when the active invoice template has no HSN/SAC column.
		 */
		public static function active_template_source() {
			if ( null === self::$active ) {
				global $wpdb;
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Read once per request.
				$html         = $wpdb->get_var( $wpdb->prepare( "SELECT template_html FROM `{$wpdb->prefix}wfpklist_template_data` WHERE is_active=1 AND template_type=%s", 'invoice' ) );
				self::$active = $html ? self::source_from_html( html_entity_decode( stripslashes( $html ) ) ) : false;
			}
			return self::$active;
		}

		/**
		 * Source to use: the column options passed with the cell, the template being rendered, then the active template.
		 *
		 * @param array  $options     Column options (one column's, or all columns keyed by column).
		 * @param string $columns_key Column key.
		 * @return array
		 */
		public static function current_source( $options = array(), $columns_key = self::COLUMN ) {
			if ( is_array( $options ) && isset( $options[ $columns_key ] ) && is_array( $options[ $columns_key ] ) ) {
				$options = $options[ $columns_key ];
			}
			if ( is_array( $options ) && ! empty( $options['hsn-source'] ) ) {
				return self::make_source( $options['hsn-source'], isset( $options['hsn-key'] ) ? $options['hsn-key'] : '' );
			}
			if ( null !== self::$rendering ) {
				return self::$rendering;
			}
			$active = self::active_template_source();
			return $active ? $active : self::make_source( 'auto' );
		}

		/*
		 * ------------------------------------------------------------------
		 * Reading codes
		 * ------------------------------------------------------------------
		 */

		/**
		 * HSN/SAC code of a product. Reads only, never writes.
		 *
		 * @param WC_Product|null $product Product (a variation falls back to its parent).
		 * @param array|null      $source  Source, defaults to current_source().
		 * @return string
		 */
		public static function get_code( $product, $source = null ) {
			$source = is_array( $source ) ? $source : self::current_source();
			$code   = self::read_products( $product, $source );
			/**
			 * HSN/SAC code shown on GST invoices.
			 *
			 * @param string          $code    Code.
			 * @param WC_Product|null $product Product.
			 * @param array|null      $source  Source { source, key }.
			 */
			return (string) apply_filters( 'wt_pklist_hsn_code', $code, $product, $source ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Established plugin hook prefix.
		}

		/**
		 * Code from a product, then from its parent for a variation.
		 *
		 * @param WC_Product|null $product Product.
		 * @param array           $source  Source.
		 * @return string
		 */
		private static function read_products( $product, $source ) {
			if ( ! is_object( $product ) || ! is_a( $product, 'WC_Product' ) ) {
				return '';
			}
			$code = self::read_product( $product, $source );
			if ( '' === $code && $product->get_parent_id() ) {
				$parent = wc_get_product( $product->get_parent_id() );
				$code   = $parent ? self::read_product( $parent, $source ) : '';
			}
			return $code;
		}

		/**
		 * Code from one product (no parent fallback).
		 *
		 * @param WC_Product $product Product.
		 * @param array      $source  Source.
		 * @return string
		 */
		private static function read_product( $product, $source ) {
			if ( 'meta' === $source['source'] ) {
				return self::clean( get_post_meta( $product->get_id(), $source['key'], true ) );
			}
			if ( 'attr' === $source['source'] ) {
				return self::clean( $product->get_attribute( $source['key'] ) );
			}
			// Find automatically: known GST plugin keys, then any meta key like hsn / sac, then an HSN/SAC attribute.
			foreach ( self::known_meta_keys() as $key ) {
				$code = self::clean( get_post_meta( $product->get_id(), $key, true ) );
				if ( '' !== $code ) {
					return $code;
				}
			}
			foreach ( (array) get_post_meta( $product->get_id() ) as $key => $values ) {
				if ( self::is_hsn_meta_key( $key ) && isset( $values[0] ) ) {
					$code = self::clean( maybe_unserialize( $values[0] ) );
					if ( '' !== $code ) {
						return $code;
					}
				}
			}
			foreach ( array_keys( (array) $product->get_attributes() ) as $name ) {
				if ( self::is_hsn_attribute_name( $name ) ) {
					$code = self::clean( $product->get_attribute( $name ) );
					if ( '' !== $code ) {
						return $code;
					}
				}
			}
			return '';
		}

		/**
		 * Meta keys of known GST plugins, read first by "Find automatically".
		 *
		 * @return string[]
		 */
		public static function known_meta_keys() {
			/**
			 * Product meta keys checked first by "Find automatically", in order.
			 *
			 * @param string[] $keys Meta keys.
			 */
			return array_filter( (array) apply_filters( 'wt_pklist_hsn_code_meta_keys', array( '_wt_pklist_hsn_code', 'hsn_prod_id', '_hsn_code', 'hsn_code' ) ), 'is_string' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Established plugin hook prefix.
		}

		/**
		 * Whether a meta key looks like an HSN/SAC field, e.g. hsn_code, _gst_hsn, sac_code (not "isaac").
		 *
		 * @param string $key Meta key.
		 * @return bool
		 */
		public static function is_hsn_meta_key( $key ) {
			return (bool) preg_match( '/hsn|(^|[^a-z])sac([^a-z]|$)/i', (string) $key );
		}

		/**
		 * Whether an attribute is named HSN, SAC or HSN/SAC (optionally "code", "no" or "number").
		 *
		 * @param string $name Attribute name, label or taxonomy (pa_hsn).
		 * @return bool
		 */
		public static function is_hsn_attribute_name( $name ) {
			$name = preg_replace( '/[^a-z]/', '', strtolower( preg_replace( '/^pa_/', '', (string) $name ) ) );
			return (bool) preg_match( '/^(hsn|sac|hsnsac|sachsn)(code|no|number)?$/', $name );
		}

		/**
		 * A usable code: scalar, trimmed, not empty and not an ACF field reference.
		 *
		 * @param mixed $value Value.
		 * @return string
		 */
		private static function clean( $value ) {
			if ( ! is_scalar( $value ) ) {
				return '';
			}
			$value = trim( (string) $value );
			return 0 === strpos( $value, 'field_' ) ? '' : $value;
		}

		/*
		 * ------------------------------------------------------------------
		 * Scan for the customizer
		 * ------------------------------------------------------------------
		 */

		/**
		 * Products of the most recent orders, grouped by product (a variation goes with its parent).
		 *
		 * @param int $order_count Set to the number of orders read.
		 * @return array Product ID => WC_Product[] to read (the line's product, and its parent for a variation).
		 */
		private static function recent_order_products( &$order_count = 0 ) {
			$out         = array();
			$orders      = wc_get_orders(
				array(
					'type'    => 'shop_order',
					'limit'   => self::SCAN_ORDERS,
					'orderby' => 'date',
					'order'   => 'DESC',
				)
			);
			$order_count = count( $orders );
			foreach ( $orders as $order ) {
				foreach ( $order->get_items() as $item ) {
					$product = is_callable( array( $item, 'get_product' ) ) ? $item->get_product() : null;
					if ( ! $product ) {
						continue;
					}
					$id = $product->get_parent_id() ? $product->get_parent_id() : $product->get_id();
					if ( ! isset( $out[ $id ][ $id ] ) && $id !== $product->get_id() ) {
						$parent = wc_get_product( $id );
						if ( $parent ) {
							$out[ $id ][ $id ] = $parent;
						}
					}
					$out[ $id ][ $product->get_id() ] = $product;
				}
			}
			return $out;
		}

		/**
		 * Record a code found on a product.
		 *
		 * @param array  $found Found codes, key => { key, label, ids, example }.
		 * @param string $key   Meta key or attribute name.
		 * @param string $label Label.
		 * @param int    $id    Product ID.
		 * @param string $value Code.
		 */
		private static function add_found( &$found, $key, $label, $id, $value ) {
			if ( ! isset( $found[ $key ] ) ) {
				$found[ $key ] = array(
					'key'     => $key,
					'label'   => $label,
					'ids'     => array(),
					'example' => wp_html_excerpt( $value, 20 ),
				);
			}
			$found[ $key ]['ids'][ $id ] = true;
		}

		/**
		 * Found codes as listed in the customizer, most used first.
		 *
		 * @param array $found Found codes from add_found().
		 * @param bool  $label Keep the label.
		 * @return array[] { key, label?, count, example }
		 */
		private static function list_found( $found, $label ) {
			$out = array();
			foreach ( $found as $row ) {
				$item = array(
					'key'     => $row['key'],
					'label'   => $row['label'],
					'count'   => count( $row['ids'] ),
					'example' => $row['example'],
				);
				if ( ! $label ) {
					unset( $item['label'] );
				}
				$out[] = $item;
			}
			usort(
				$out,
				function ( $a, $b ) {
					return $b['count'] - $a['count'];
				}
			);
			return $out;
		}

		/**
		 * HSN/SAC-like meta keys and attributes on the products of the most recent orders (reading only those few
		 * products keeps the scan quick on stores with many products).
		 *
		 * @return array { orders (number read), meta: [ { key, count, example } ], attrs: [ { key, label, count, example } ] }
		 */
		public static function scan() {
			$orders   = 0;
			$products = self::recent_order_products( $orders );
			$meta     = array();
			$custom   = array();
			$global   = array();
			foreach ( $products as $id => $group ) {
				foreach ( $group as $product ) {
					foreach ( (array) get_post_meta( $product->get_id() ) as $key => $values ) {
						$key = (string) $key;
						if ( ! self::is_hsn_meta_key( $key ) || ! isset( $values[0] ) || is_serialized( $values[0] ) ) {
							continue;
						}
						$value = self::clean( $values[0] );
						if ( '' !== $value ) {
							self::add_found( $meta, $key, $key, $id, $value );
						}
					}
					if ( $product->is_type( 'variation' ) ) {
						continue; // A variation's attributes are its variation choices, not codes.
					}
					foreach ( (array) $product->get_attributes() as $attribute ) {
						if ( ! is_a( $attribute, 'WC_Product_Attribute' ) ) {
							continue;
						}
						$name  = $attribute->get_name();
						$label = $attribute->is_taxonomy() ? wc_attribute_label( $name ) : $name;
						if ( ! self::is_hsn_attribute_name( $name ) && ! self::is_hsn_attribute_name( $label ) ) {
							continue;
						}
						$value = self::clean( $product->get_attribute( $name ) );
						if ( '' === $value ) {
							continue;
						}
						if ( $attribute->is_taxonomy() ) {
							self::add_found( $global, $name, $label, $id, $value );
						} else {
							self::add_found( $custom, $name, $label, $id, $value );
						}
					}
				}
			}
			// Like WC_Product::get_attribute(), a custom attribute name also reads the global attribute of that name.
			foreach ( $custom as $key => $row ) {
				$taxonomy = 'pa_' . sanitize_title( $key );
				if ( isset( $global[ $taxonomy ] ) ) {
					$custom[ $key ]['ids'] += $global[ $taxonomy ]['ids'];
					unset( $global[ $taxonomy ] );
				}
			}
			return array(
				'orders' => $orders,
				'meta'   => self::list_found( $meta, false ),
				'attrs'  => self::list_found( array_merge( $global, $custom ), true ),
			);
		}

		/**
		 * Ajax: the scan for the customizer's HSN/SAC column options.
		 */
		public static function ajax_scan() {
			if ( ! Wf_Woocommerce_Packing_List_Admin::check_write_access() ) {
				wp_send_json_error( array( 'msg' => __( 'You are not allowed to do this action', 'print-invoices-packing-slip-labels-for-woocommerce' ) ) );
			}
			wp_send_json_success( self::scan() );
		}

		/**
		 * Load the HSN/SAC column options script on the invoice settings page (where the customizer is).
		 *
		 * @param string $hook_suffix Admin page.
		 */
		public static function enqueue_scripts( $hook_suffix ) {
			if ( false === strpos( (string) $hook_suffix, 'wf_woocommerce_packing_list_invoice' ) ) {
				return;
			}
			$handle = 'wt-pklist-gst-hsn';
			wp_enqueue_script( $handle, WF_PKLIST_PLUGIN_URL . 'admin/js/wt-pklist-gst-hsn.js', array( 'jquery' ), WF_PKLIST_VERSION, true );
			wp_localize_script(
				$handle,
				'wt_pklist_gst_hsn',
				array(
					'ajaxurl' => admin_url( 'admin-ajax.php' ),
					'nonce'   => wp_create_nonce( WF_PKLIST_PLUGIN_NAME ),
					'help'    => 'https://woocommerce.com/document/managing-product-taxonomies/#product-attributes',
					'i18n'    => array(
						'meta_label'       => __( 'Meta key', 'print-invoices-packing-slip-labels-for-woocommerce' ),
						'attr_label'       => __( 'Attribute name', 'print-invoices-packing-slip-labels-for-woocommerce' ),
						'meta_placeholder' => __( 'e.g. hsn_code', 'print-invoices-packing-slip-labels-for-woocommerce' ),
						'attr_placeholder' => __( 'e.g. HSN or pa_hsn', 'print-invoices-packing-slip-labels-for-woocommerce' ),
						'found_hint'       => __( 'Click to use for HSN/SAC.', 'print-invoices-packing-slip-labels-for-woocommerce' ),
						/* translators: %s: meta key */
						'found_meta'       => __( 'Meta key: %s', 'print-invoices-packing-slip-labels-for-woocommerce' ),
						/* translators: %s: attribute name */
						'found_attr'       => __( 'Attribute: %s', 'print-invoices-packing-slip-labels-for-woocommerce' ),
						/* translators: %s: number of products */
						'product'          => __( '%s product', 'print-invoices-packing-slip-labels-for-woocommerce' ),
						/* translators: %s: number of products */
						'products'         => __( '%s products', 'print-invoices-packing-slip-labels-for-woocommerce' ),
						/* translators: %d: number of orders */
						'none_title'       => sprintf( __( 'No HSN/SAC codes found on products in your last %d orders', 'print-invoices-packing-slip-labels-for-woocommerce' ), self::SCAN_ORDERS ),
						'no_orders'        => __( 'No orders yet to check for HSN/SAC codes', 'print-invoices-packing-slip-labels-for-woocommerce' ),
						'none_text'        => __( 'Products without a code print — in the HSN/SAC column. Add codes in any of these ways:', 'print-invoices-packing-slip-labels-for-woocommerce' ),
						'none_attr'        => __( 'Add a product attribute named HSN (standard WooCommerce)', 'print-invoices-packing-slip-labels-for-woocommerce' ),
						'none_plugin'      => __( "Use your GST plugin's HSN field", 'print-invoices-packing-slip-labels-for-woocommerce' ),
						'none_csv'         => __( 'Import codes into an attribute or custom field by CSV', 'print-invoices-packing-slip-labels-for-woocommerce' ),
						'scan_again'       => __( 'Scan again', 'print-invoices-packing-slip-labels-for-woocommerce' ),
						'scanning'         => __( 'Checking your recent orders…', 'print-invoices-packing-slip-labels-for-woocommerce' ),
						'how_to'           => __( 'How to add HSN as an attribute', 'print-invoices-packing-slip-labels-for-woocommerce' ),
						'scan_failed'      => __( 'Could not check your recent orders. Please try again.', 'print-invoices-packing-slip-labels-for-woocommerce' ),
					),
				)
			);
		}

		/**
		 * Customizer panel inputs for the HSN/SAC column: where to read the code from.
		 *
		 * @param string $target Column head element.
		 * @return array[]
		 */
		public static function customize_fields( $target ) {
			return array(
				array(
					'label'        => '&nbsp;',
					'type'         => 'spacer',
					'frmgrp_class' => 'wt_cst_spacer',
					'width'        => '10%',
				),
				array(
					'label'          => __( 'Code from', 'print-invoices-packing-slip-labels-for-woocommerce' ),
					'type'           => 'select',
					'select_options' => array(
						'auto' => __( 'Find automatically', 'print-invoices-packing-slip-labels-for-woocommerce' ),
						'meta' => __( 'Product meta key', 'print-invoices-packing-slip-labels-for-woocommerce' ),
						'attr' => __( 'Product attribute', 'print-invoices-packing-slip-labels-for-woocommerce' ),
					),
					'css_prop'       => 'attr-data-hsn-source',
					'trgt_elm'       => $target,
					'event_class'    => 'wf_cst_change',
					'default_data'   => 'auto',
					'frmgrp_class'   => 'wt_pklist_hsn_source',
					'width'          => '90%',
					'float'          => 'right',
				),
				array(
					'label'        => '&nbsp;',
					'type'         => 'spacer',
					'frmgrp_class' => 'wt_cst_spacer',
					'width'        => '10%',
				),
				array(
					'label'        => __( 'Meta key', 'print-invoices-packing-slip-labels-for-woocommerce' ),
					'css_prop'     => 'attr-data-hsn-key',
					'trgt_elm'     => $target,
					'default_data' => '',
					'frmgrp_class' => 'wt_pklist_hsn_key',
					'width'        => '90%',
					'float'        => 'right',
				),
			);
		}
	}
}
