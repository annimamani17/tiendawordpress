<?php
/**
 * Business details for documents: shop VAT number, company registration number and bank details.
 *
 * @package Wf_Woocommerce_Packing_List
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Wt_Pklist_Business_Details' ) ) {

	/**
	 * Adds the business detail settings to General > Company details and fills the [wfte_shop_vat_number],
	 * [wfte_shop_reg_number] and [wfte_bank_details] placeholders. Bank details and terms and conditions are Pro
	 * features; their settings are not offered and their elements stay empty and hidden without it.
	 */
	class Wt_Pklist_Business_Details {

		/**
		 * Settings keys (stored in the main General settings).
		 */
		const OPT_SENDER_VAT      = 'woocommerce_wf_packinglist_sender_vat';
		const OPT_SHOP_REG        = 'wt_pklist_shop_registration_number';
		const OPT_SHOW_BANK       = 'wt_pklist_show_bank_details';
		const OPT_BANK_HOLDER     = 'wt_pklist_bank_account_holder';
		const OPT_BANK_NAME       = 'wt_pklist_bank_name';
		const OPT_BANK_IBAN       = 'wt_pklist_bank_iban';
		const OPT_BANK_BIC        = 'wt_pklist_bank_bic';
		const OPT_BANK_ADDITIONAL = 'wt_pklist_bank_additional_details';
		const OPT_TERMS           = 'wt_pklist_terms_conditions';

		/**
		 * Register hooks.
		 */
		public static function register_hooks() {
			add_filter( 'wt_pklist_add_fields_to_settings', array( __CLASS__, 'settings_fields' ), 20, 4 );
			add_filter( 'wf_module_default_settings', array( __CLASS__, 'default_settings' ), 20, 2 );
			add_filter( 'wf_module_single_checkbox_fields', array( __CLASS__, 'single_checkbox_fields' ), 20, 3 );
			add_filter( 'wt_pklist_intl_alter_validation_rule', array( __CLASS__, 'validation_rule' ), 20, 2 );

			add_filter( 'wf_pklist_alter_find_replace', array( __CLASS__, 'find_replace' ), 15, 6 );
			add_filter( 'wf_pklist_alter_hide_empty', array( __CLASS__, 'hide_on_empty' ), 20, 2 );
			add_filter( 'wf_pklist_alter_dummy_data_for_customize', array( __CLASS__, 'dummy_data' ), 15, 3 );
			add_filter( 'wf_module_customizable_items', array( __CLASS__, 'customizable_items' ), 30, 2 );
			add_filter( 'wf_pklist_alter_customize_inputs', array( __CLASS__, 'customize_inputs' ), 25, 3 );
		}

		/**
		 * Whether the Pro add-on is active; bank details and terms and conditions need it.
		 *
		 * @return bool
		 */
		public static function is_pro_active() {
			return class_exists( 'Wt_Pklist_Additional_Doc_Fields' );
		}

		/**
		 * Invoice elements filled by this class: element key => customizer label.
		 *
		 * @return array
		 */
		public static function get_elements() {
			return array(
				'shop_vat_number'  => __( 'Shop VAT number', 'print-invoices-packing-slip-labels-for-woocommerce' ),
				'shop_reg_number'  => __( 'Shop registration number', 'print-invoices-packing-slip-labels-for-woocommerce' ),
				'bank_details'     => __( 'Bank details', 'print-invoices-packing-slip-labels-for-woocommerce' ),
			);
		}

		/**
		 * List the elements in the invoice customizer side panel.
		 *
		 * @param array  $settings Items.
		 * @param string $base_id  Module ID.
		 * @return array
		 */
		public static function customizable_items( $settings, $base_id ) {
			if ( ! is_array( $settings ) || ! class_exists( 'Wf_Woocommerce_Packing_List' ) || Wf_Woocommerce_Packing_List::get_module_id( 'invoice' ) !== $base_id ) {
				return $settings;
			}
			$pro_text = '<span class="wt_customizer_pro_text" style="color:red;"> (' . __( 'Pro version', 'print-invoices-packing-slip-labels-for-woocommerce' ) . ')</span>';
			foreach ( self::get_elements() as $key => $label ) {
				// Without Pro, bank details is listed as a locked pro item.
				if ( 'bank_details' === $key && ! self::is_pro_active() ) {
					$key    = 'bank_details_pro_element';
					$label .= $pro_text;
				}
				if ( ! isset( $settings[ $key ] ) ) {
					$settings[ $key ] = $label;
				}
			}
			if ( ! self::is_pro_active() && ! isset( $settings['terms_conditions_pro_element'] ) ) {
				$settings['terms_conditions_pro_element'] = __( 'Terms and conditions', 'print-invoices-packing-slip-labels-for-woocommerce' ) . $pro_text;
			}
			return $settings;
		}

		/**
		 * Panel inputs for the elements: title, font size and text colour.
		 *
		 * @param array  $fields        Panel fields.
		 * @param string $type          Panel item.
		 * @param string $template_type Document type.
		 * @return array
		 */
		public static function customize_inputs( $fields, $type, $template_type ) {
			if ( 'invoice' !== $template_type || ! array_key_exists( $type, self::get_elements() ) || ( is_array( $fields ) && ! empty( $fields ) ) ) {
				return $fields;
			}
			return array(
				array(
					'label'    => __( 'Title', 'print-invoices-packing-slip-labels-for-woocommerce' ),
					'css_prop' => 'html',
					'trgt_elm' => $type . '_label',
				),
				array(
					'label'    => __( 'Font size', 'print-invoices-packing-slip-labels-for-woocommerce' ),
					'type'     => 'text_inputgrp',
					'css_prop' => 'font-size',
					'trgt_elm' => $type,
					'unit'     => 'px',
				),
				array(
					'label'       => __( 'Text color', 'print-invoices-packing-slip-labels-for-woocommerce' ),
					'type'        => 'color',
					'css_prop'    => 'color',
					'trgt_elm'    => $type,
					'event_class' => 'wf_cst_click',
				),
			);
		}

		/**
		 * Bank detail fields shown on the document, in order: option key => label.
		 *
		 * @return array
		 */
		public static function get_bank_fields() {
			return array(
				self::OPT_BANK_HOLDER => __( 'Account holder name', 'print-invoices-packing-slip-labels-for-woocommerce' ),
				self::OPT_BANK_NAME   => __( 'Bank name', 'print-invoices-packing-slip-labels-for-woocommerce' ),
				self::OPT_BANK_IBAN   => __( 'IBAN', 'print-invoices-packing-slip-labels-for-woocommerce' ),
				self::OPT_BANK_BIC    => __( 'BIC / SWIFT', 'print-invoices-packing-slip-labels-for-woocommerce' ),
			);
		}

		/**
		 * Read one of the settings.
		 *
		 * @param string $key Option key.
		 * @return string
		 */
		public static function get_setting( $key ) {
			if ( ! class_exists( 'Wf_Woocommerce_Packing_List' ) ) {
				return '';
			}
			$value = Wf_Woocommerce_Packing_List::get_option( $key );
			return is_string( $value ) ? trim( stripslashes( $value ) ) : '';
		}

		/**
		 * Plain text to safe HTML with line breaks.
		 *
		 * @param string $text Text.
		 * @return string
		 */
		public static function text_to_html( $text ) {
			return nl2br( esc_html( wp_strip_all_tags( (string) $text ) ) );
		}

		/**
		 * Bank details entered in General settings as label / value pairs: the bank fields, then each
		 * "Label: value" line of the additional bank details. Empty when "Show bank details" is off.
		 *
		 * @return array[] Each: array( label, value ).
		 */
		public static function get_bank_pairs() {
			if ( ! self::is_pro_active() ) {
				return array();
			}
			if ( 'Yes' !== self::get_setting( self::OPT_SHOW_BANK ) ) {
				return array();
			}
			$pairs = array();
			foreach ( self::get_bank_fields() as $key => $label ) {
				$value = self::get_setting( $key );
				if ( '' !== $value ) {
					$pairs[] = array( $label, $value );
				}
			}
			foreach ( preg_split( '/\r\n|\r|\n/', self::get_setting( self::OPT_BANK_ADDITIONAL ) ) as $line ) {
				$line = trim( wp_strip_all_tags( (string) $line ) );
				if ( '' === $line ) {
					continue;
				}
				$parts   = explode( ':', $line, 2 );
				$pairs[] = 2 === count( $parts ) ? array( trim( $parts[0] ), trim( $parts[1] ) ) : array( '', $line );
			}
			return (array) apply_filters( 'wt_pklist_bank_details_pairs', $pairs ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Established plugin hook prefix.
		}

		/**
		 * Label / value pairs as HTML lines. The label and value are in their own spans
		 * (wfte_bank_line_label, wfte_bank_line_value) so each template can style them.
		 *
		 * @param array[] $pairs Pairs.
		 * @return string
		 */
		public static function pairs_html( $pairs ) {
			$html = '';
			foreach ( (array) $pairs as $pair ) {
				$label = '' === $pair[0] ? '' : '<span class="wfte_bank_line_label">' . esc_html( $pair[0] ) . ':</span> ';
				$html .= '<div class="wfte_bank_line">' . $label . '<span class="wfte_bank_line_value">' . esc_html( $pair[1] ) . '</span></div>';
			}
			return $html;
		}

		/**
		 * Bank details entered in General settings, as HTML lines. Empty when "Show bank details" is off.
		 *
		 * @return string
		 */
		public static function settings_bank_details() {
			return self::pairs_html( self::get_bank_pairs() );
		}

		/**
		 * Bank details: the details from General settings, or the WooCommerce "Direct bank transfer" accounts
		 * for BACS orders when "Show bank details" is on but no details are entered.
		 *
		 * @param WC_Order $order Order.
		 * @return string
		 */
		public static function bank_details( $order ) {
			if ( ! self::is_pro_active() || 'Yes' !== self::get_setting( self::OPT_SHOW_BANK ) ) {
				return apply_filters( 'wt_pklist_bank_details_html', '', $order ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Established plugin hook prefix.
			}
			$html = self::settings_bank_details();
			if ( '' === $html && is_object( $order ) && 'bacs' === $order->get_payment_method() ) {
				$labels = array(
					'bank_name'      => __( 'Bank', 'print-invoices-packing-slip-labels-for-woocommerce' ),
					'account_name'   => __( 'Account name', 'print-invoices-packing-slip-labels-for-woocommerce' ),
					'account_number' => __( 'Account number', 'print-invoices-packing-slip-labels-for-woocommerce' ),
					'sort_code'      => __( 'Sort code', 'print-invoices-packing-slip-labels-for-woocommerce' ),
					'iban'           => __( 'IBAN', 'print-invoices-packing-slip-labels-for-woocommerce' ),
					'bic'            => __( 'BIC / SWIFT', 'print-invoices-packing-slip-labels-for-woocommerce' ),
				);
				$blocks = array();
				foreach ( (array) get_option( 'woocommerce_bacs_accounts', array() ) as $account ) {
					$pairs = array();
					foreach ( $labels as $key => $label ) {
						if ( ! empty( $account[ $key ] ) ) {
							$pairs[] = array( $label, $account[ $key ] );
						}
					}
					if ( $pairs ) {
						$blocks[] = self::pairs_html( $pairs );
					}
				}
				$html = implode( '<br>', $blocks );
			}
			return apply_filters( 'wt_pklist_bank_details_html', $html, $order ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Established plugin hook prefix.
		}

		/**
		 * Placeholder values for a document.
		 *
		 * @param array    $find_replace  Placeholders.
		 * @param string   $template_type Document type.
		 * @param WC_Order $order         Order.
		 * @param mixed    $box_packing   Box packing.
		 * @param mixed    $order_package Package.
		 * @param string   $html          Template HTML.
		 * @return array
		 */
		public static function find_replace( $find_replace, $template_type, $order = null, $box_packing = null, $order_package = null, $html = '' ) {
			if ( ! is_string( $html ) || ! is_object( $order ) || ! is_a( $order, 'WC_Order' ) ) {
				return $find_replace;
			}
			if ( false !== strpos( $html, '[wfte_shop_vat_number]' ) ) {
				$find_replace['[wfte_shop_vat_number]'] = esc_html( self::get_setting( self::OPT_SENDER_VAT ) );
				$find_replace                           = self::remove_sender_vat_from_address( $find_replace );
			}
			if ( false !== strpos( $html, '[wfte_shop_reg_number]' ) ) {
				$find_replace['[wfte_shop_reg_number]'] = esc_html( self::get_setting( self::OPT_SHOP_REG ) );
			}
			if ( false !== strpos( $html, '[wfte_bank_details]' ) ) {
				$find_replace['[wfte_bank_details]'] = self::bank_details( $order );
			}
			return self::hide_pro_elements( $find_replace, $html );
		}

		/**
		 * Bank details and terms and conditions are Pro features: without the Pro add-on, empty their placeholders
		 * and hide their elements.
		 *
		 * @param array  $find_replace Placeholders.
		 * @param string $html         Template HTML.
		 * @return array
		 */
		public static function hide_pro_elements( $find_replace, $html ) {
			if ( self::is_pro_active() || ! is_string( $html ) ) {
				return $find_replace;
			}
			foreach ( array( 'terms_conditions', 'bank_details' ) as $element ) {
				if ( false !== strpos( $html, '[wfte_' . $element . ']' ) ) {
					$find_replace[ '[wfte_' . $element . ']' ]        = '';
					$find_replace[ 'class="wfte_' . $element . ' ' ] = 'class="wfte_hidden wfte_' . $element . ' ';
				}
			}
			return $find_replace;
		}

		/**
		 * Remove the sender VAT line from the from address when the template shows the shop VAT number as its own field.
		 *
		 * @param array $find_replace Placeholders.
		 * @return array
		 */
		public static function remove_sender_vat_from_address( $find_replace ) {
			$vat = self::get_setting( self::OPT_SENDER_VAT );
			if ( '' === $vat || empty( $find_replace['[wfte_from_address]'] ) || ! is_string( $find_replace['[wfte_from_address]'] ) ) {
				return $find_replace;
			}
			$quoted                              = preg_quote( $vat, '#' );
			$address                             = preg_replace( '#\s*<br\s*/?>\s*' . $quoted . '\s*(?=<br|$)#i', '', $find_replace['[wfte_from_address]'] );
			$address                             = preg_replace( '#^\s*' . $quoted . '\s*<br\s*/?>#i', '', $address );
			$find_replace['[wfte_from_address]'] = $address;
			return $find_replace;
		}

		/**
		 * Hide the elements when their value is empty.
		 *
		 * @param array  $fields        Class names.
		 * @param string $template_type Document type.
		 * @return array
		 */
		public static function hide_on_empty( $fields, $template_type = '' ) {
			foreach ( array( 'wfte_shop_vat_number', 'wfte_shop_reg_number', 'wfte_bank_details', 'wfte_terms_conditions' ) as $class ) {
				$fields[] = $class;
			}
			return array_values( array_unique( (array) $fields ) );
		}

		/**
		 * Sample values for the customizer preview.
		 *
		 * @param array  $find_replace  Placeholders.
		 * @param string $template_type Document type.
		 * @param string $html          Template HTML.
		 * @return array
		 */
		public static function dummy_data( $find_replace, $template_type, $html = '' ) {
			$samples = array(
				'[wfte_shop_vat_number]'  => '' !== self::get_setting( self::OPT_SENDER_VAT ) ? esc_html( self::get_setting( self::OPT_SENDER_VAT ) ) : 'GB123456789',
				'[wfte_shop_reg_number]'  => '' !== self::get_setting( self::OPT_SHOP_REG ) ? esc_html( self::get_setting( self::OPT_SHOP_REG ) ) : '12345678',
				'[wfte_bank_details]'     => '' !== self::settings_bank_details() ? self::settings_bank_details() : self::pairs_html(
					array(
						array( __( 'Account holder name', 'print-invoices-packing-slip-labels-for-woocommerce' ), 'Your Company Ltd' ),
						array( __( 'Bank name', 'print-invoices-packing-slip-labels-for-woocommerce' ), 'Example Bank' ),
						array( 'IBAN', 'GB33 BUKB 2020 1555 5555 55' ),
						array( __( 'BIC / SWIFT', 'print-invoices-packing-slip-labels-for-woocommerce' ), 'BUKBGB22' ),
					)
				),
			);
			foreach ( $samples as $key => $value ) {
				if ( ! isset( $find_replace[ $key ] ) || '' === $find_replace[ $key ] ) {
					$find_replace[ $key ] = $value;
				}
			}
			$find_replace = self::hide_pro_elements( $find_replace, $html );
			if ( is_string( $html ) && false !== strpos( $html, '[wfte_shop_vat_number]' ) ) {
				$find_replace = self::remove_sender_vat_from_address( $find_replace );
			}
			return $find_replace;
		}

		/**
		 * Add the fields to the Company details section of the General settings, below Company Tax ID.
		 *
		 * @param array  $settings      Settings sections.
		 * @param string $target_id     Tab.
		 * @param string $template_type Document type.
		 * @param string $base_id       Module ID.
		 * @return array
		 */
		public static function settings_fields( $settings, $target_id, $template_type = '', $base_id = '' ) {
			if ( 'general' !== $target_id || '' !== $base_id || ! isset( $settings['general_company_details'] ) || ! is_array( $settings['general_company_details'] ) ) {
				return $settings;
			}
			$bank_child = array(
				'type' => 'child',
				'id'   => 'wt_pklist_bank_details_fields',
				'val'  => 'Yes',
			);
			$fields     = array(
				self::OPT_SHOP_REG  => array(
					'type'      => 'wt_text',
					'label'     => __( 'Company registration number', 'print-invoices-packing-slip-labels-for-woocommerce' ),
					'name'      => self::OPT_SHOP_REG,
					'class'     => self::OPT_SHOP_REG,
					'help_text' => __( 'Your company register / Chamber of Commerce number (e.g. KvK, Companies House, SIREN, CIN). Shown by the "Shop registration number" element.', 'print-invoices-packing-slip-labels-for-woocommerce' ),
				),
			);
			// Bank details is a Pro feature: the settings are only offered with the Pro add-on.
			if ( self::is_pro_active() ) {
				$fields[ self::OPT_SHOW_BANK ] = array(
					'type'            => 'wt_single_checkbox',
					'label'           => __( 'Show bank details', 'print-invoices-packing-slip-labels-for-woocommerce' ),
					'id'              => self::OPT_SHOW_BANK,
					'name'            => self::OPT_SHOW_BANK,
					'value'           => 'Yes',
					'checkbox_fields' => array( 'Yes' => __( 'Enable to show your bank details on the invoice', 'print-invoices-packing-slip-labels-for-woocommerce' ) ),
					'class'           => self::OPT_SHOW_BANK,
					'col'             => 3,
					'form_toggler'    => array(
						'type'   => 'parent',
						'target' => 'wt_pklist_bank_details_fields',
					),
					'help_text'       => __( 'Shown by the "Bank details" element. If no details are entered, your WooCommerce "Direct bank transfer" accounts are shown on bank-transfer orders.', 'print-invoices-packing-slip-labels-for-woocommerce' ),
				);
				foreach ( self::get_bank_fields() as $key => $label ) {
					$fields[ $key ] = array(
						'type'         => 'wt_text',
						'label'        => $label,
						'name'         => $key,
						'class'        => $key,
						'form_toggler' => $bank_child,
					);
				}
				$fields[ self::OPT_BANK_ADDITIONAL ] = array(
					'type'         => 'wt_textarea',
					'label'        => __( 'Additional bank details', 'print-invoices-packing-slip-labels-for-woocommerce' ),
					'name'         => self::OPT_BANK_ADDITIONAL,
					'class'        => self::OPT_BANK_ADDITIONAL,
					'form_toggler' => $bank_child,
					'help_text'    => __( 'Any other details your customers need, one per line. For e.g., account number, IFSC code and branch name (India), sort code (UK), routing number (US) or BSB (Australia).', 'print-invoices-packing-slip-labels-for-woocommerce' ),
				);
			}

			$section = array();
			foreach ( $settings['general_company_details'] as $key => $field ) {
				$section[ $key ] = $field;
				if ( self::OPT_SENDER_VAT === $key ) {
					$section += $fields;
				}
			}
			if ( count( $section ) === count( $settings['general_company_details'] ) ) {
				$section += $fields;
			}
			$settings['general_company_details'] = $section;
			return $settings;
		}

		/**
		 * Defaults (required so the values are saved).
		 *
		 * @param array  $settings Defaults.
		 * @param string $base_id  Module ID.
		 * @return array
		 */
		public static function default_settings( $settings, $base_id ) {
			if ( 'main' === $base_id ) {
				$settings[ self::OPT_SHOP_REG ]        = '';
				$settings[ self::OPT_SHOW_BANK ]       = 'No';
				$settings[ self::OPT_BANK_ADDITIONAL ] = '';
				$settings[ self::OPT_TERMS ]           = '';
				foreach ( array( self::OPT_BANK_HOLDER, self::OPT_BANK_NAME, self::OPT_BANK_IBAN, self::OPT_BANK_BIC ) as $key ) {
					$settings[ $key ] = '';
				}
			}
			return $settings;
		}

		/**
		 * Value saved for "Show bank details" when it is unchecked.
		 *
		 * @param array  $fields   Single checkbox fields per tab.
		 * @param string $base_id  Module ID.
		 * @param string $tab_name Tab.
		 * @return array
		 */
		public static function single_checkbox_fields( $fields, $base_id, $tab_name = '' ) {
			if ( 'main' === $base_id ) {
				$fields['wt_main_general'][ self::OPT_SHOW_BANK ] = 'No';
			}
			return $fields;
		}

		/**
		 * Keep line breaks in the additional bank details and the terms (saved by the Pro add-on).
		 *
		 * @param array  $rules   Validation rules.
		 * @param string $base_id Module ID.
		 * @return array
		 */
		public static function validation_rule( $rules, $base_id ) {
			if ( '' === $base_id ) {
				$rules[ self::OPT_BANK_ADDITIONAL ] = array( 'type' => 'textarea' );
				$rules[ self::OPT_TERMS ]           = array( 'type' => 'textarea' );
			}
			return $rules;
		}
	}
}
