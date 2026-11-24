<?php
/**
 * GST invoice fields: HSN/SAC product code, taxable amount and amount columns,
 * place of supply, and the rate-wise and HSN-wise tax summary tables.
 *
 * @package Wf_Woocommerce_Packing_List
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Wt_Pklist_Gst_Fields' ) ) {

	/**
	 * Registers the GST placeholders, product table columns and customizer items.
	 */
	class Wt_Pklist_Gst_Fields {

		/**
		 * GST state codes for the WooCommerce India state keys.
		 *
		 * @var string[]
		 */
		public static $state_codes = array(
			'JK' => '01',
			'HP' => '02',
			'PB' => '03',
			'CH' => '04',
			'UK' => '05',
			'HR' => '06',
			'DL' => '07',
			'RJ' => '08',
			'UP' => '09',
			'BR' => '10',
			'SK' => '11',
			'AR' => '12',
			'NL' => '13',
			'MN' => '14',
			'MZ' => '15',
			'TR' => '16',
			'ML' => '17',
			'AS' => '18',
			'WB' => '19',
			'JH' => '20',
			'OD' => '21',
			'CT' => '22',
			'MP' => '23',
			'GJ' => '24',
			'DD' => '26',
			'DN' => '26',
			'DH' => '26',
			'MH' => '27',
			'KA' => '29',
			'GA' => '30',
			'LD' => '31',
			'KL' => '32',
			'TN' => '33',
			'PY' => '34',
			'AN' => '35',
			'TS' => '36',
			'AP' => '37',
			'LA' => '38',
		);

		/**
		 * Placeholder fields: key => customizer label.
		 *
		 * @return array
		 */
		public static function get_fields() {
			return array(
				'place_of_supply'  => __( 'Place of supply', 'print-invoices-packing-slip-labels-for-woocommerce' ),
				'tax_rate_summary' => __( 'Tax rate summary', 'print-invoices-packing-slip-labels-for-woocommerce' ),
				'hsn_summary'      => __( 'HSN/SAC summary', 'print-invoices-packing-slip-labels-for-woocommerce' ),
			);
		}

		/**
		 * Product table columns: key => customizer label.
		 *
		 * @return array
		 */
		public static function get_columns() {
			return array(
				'hsn_code'       => __( 'HSN/SAC', 'print-invoices-packing-slip-labels-for-woocommerce' ),
				'taxable_amount' => __( 'Taxable amt', 'print-invoices-packing-slip-labels-for-woocommerce' ),
				'line_amount'    => __( 'Amt incl. GST', 'print-invoices-packing-slip-labels-for-woocommerce' ),
			);
		}

		/**
		 * Register hooks.
		 */
		public static function register_hooks() {
			add_filter( 'wf_pklist_alter_find_replace', array( __CLASS__, 'find_replace' ), 25, 6 );
			add_filter( 'wf_pklist_alter_hide_empty_from_pro', array( __CLASS__, 'hide_on_empty' ) );
			add_filter( 'wf_pklist_alter_hide_empty', array( __CLASS__, 'hide_on_empty' ) );
			add_filter( 'wt_pklist_add_product_table_additional_column_value', array( __CLASS__, 'column_value' ), 25, 7 );
			add_filter( 'wf_pklist_product_table_additional_column_val', array( __CLASS__, 'column_value' ), 25, 7 );

			add_filter( 'wf_pklist_alter_dummy_data_for_customize_pro', array( __CLASS__, 'dummy_data' ), 25, 3 );
			add_filter( 'wf_pklist_alter_dummy_data_for_customize', array( __CLASS__, 'dummy_data' ), 25, 3 );
			add_filter( 'wt_pklist_alter_dummy_col_value', array( __CLASS__, 'dummy_col_value' ), 25, 2 );
			add_filter( 'wf_module_customizable_items', array( __CLASS__, 'customizable_items' ), 35, 2 );
			add_filter( 'wf_pklist_alter_customize_inputs', array( __CLASS__, 'customize_inputs' ), 30, 3 );

			add_filter( 'wt_pklist_alter_final_order_template_html', array( __CLASS__, 'mpdf_footer_to_bottom' ), 20, 6 );
		}

		/* ------------------------------------------------------------------
		 * Tax calculation
		 * ------------------------------------------------------------------ */

		/**
		 * Order tax rates: rate_id => array( label, rate ).
		 *
		 * @param WC_Order $order Order.
		 * @return array
		 */
		public static function get_order_rates( $order ) {
			$rates = array();
			foreach ( (array) $order->get_items( 'tax' ) as $tax_item ) {
				$rate_id = (int) $tax_item->get_rate_id();
				$percent = method_exists( $tax_item, 'get_rate_percent' ) ? $tax_item->get_rate_percent() : null;
				if ( ( null === $percent || '' === $percent ) && class_exists( 'WC_Tax' ) && method_exists( 'WC_Tax', 'get_rate_percent_value' ) ) {
					$percent = WC_Tax::get_rate_percent_value( $rate_id );
				}
				$rates[ $rate_id ] = array(
					'label' => (string) $tax_item->get_label(),
					'rate'  => (float) $percent,
				);
			}
			return $rates;
		}

		/**
		 * Tax columns of the summaries: the order's taxes under their own names, as the order edit screen lists them.
		 * Taxes that share a name (e.g. CGST at 9% and CGST at 2.5%) share one column in the summaries.
		 *
		 * @param WC_Order $order Order.
		 * @return string[] rate_id => tax name.
		 */
		public static function get_tax_columns( $order ) {
			$columns = array();
			foreach ( self::get_order_rates( $order ) as $rate_id => $rate ) {
				$columns[ $rate_id ] = '' !== $rate['label'] ? $rate['label'] : __( 'Tax', 'print-invoices-packing-slip-labels-for-woocommerce' );
			}
			return $columns;
		}

		/**
		 * Tax of one summary row for a column: the taxes of that name charged on the row, added together.
		 *
		 * @param array  $row     Summary row.
		 * @param array  $columns Tax columns from get_tax_columns().
		 * @param string $name    Column name.
		 * @return array|null { amount, rate }, or null when none of them is charged on the row.
		 */
		private static function row_tax( $row, $columns, $name ) {
			$tax = null;
			foreach ( $columns as $rate_id => $column ) {
				if ( $column !== $name || ! isset( $row['taxes'][ $rate_id ] ) ) {
					continue;
				}
				$tax            = null === $tax ? array(
					'amount' => 0.0,
					'rate'   => 0.0,
				) : $tax;
				$tax['amount'] += $row['taxes'][ $rate_id ];
				$tax['rate']   += isset( $row['rates'][ $rate_id ] ) ? $row['rates'][ $rate_id ] : 0.0;
			}
			return $tax;
		}

		/**
		 * Taxable amount, combined rate and taxes of one order line (product, shipping or fee). The taxes are the
		 * ones WooCommerce charged on the line, kept by tax rate and never split or renamed.
		 *
		 * @param WC_Order_Item $item          Order item.
		 * @param array         $rates         Order rates from get_order_rates().
		 * @param string        $discount_type before_discount or after_discount (products only).
		 * @return array { taxable, rate, tax, taxes: rate_id => amount, rates: rate_id => percent }
		 */
		public static function get_line( $item, $rates, $discount_type = 'after_discount' ) {
			$is_product = is_a( $item, 'WC_Order_Item_Product' );
			$taxes      = $item->get_taxes();
			$key        = ( $is_product && 'before_discount' === $discount_type ) ? 'subtotal' : 'total';
			$amounts    = isset( $taxes[ $key ] ) && is_array( $taxes[ $key ] ) ? $taxes[ $key ] : array();
			// WooCommerce rounds each line unless "Round tax at subtotal level" is on; match it so the summary
			// totals equal the order's totals and the lines of the product table.
			$round_per_line = 'yes' !== get_option( 'woocommerce_tax_round_at_subtotal' ) && function_exists( 'wc_round_tax_total' );
			$taxable        = (float) ( 'subtotal' === $key ? $item->get_subtotal() : $item->get_total() );
			$line           = array(
				'taxable' => $round_per_line ? round( $taxable, wc_get_price_decimals() ) : $taxable,
				'rate'    => 0.0,
				'tax'     => 0.0,
				'taxes'   => array(),
				'rates'   => array(),
			);
			foreach ( $amounts as $rate_id => $amount ) {
				if ( '' === $amount || null === $amount ) {
					continue;
				}
				$amount                     = $round_per_line ? (float) wc_round_tax_total( (float) $amount ) : (float) $amount;
				$percent                    = isset( $rates[ $rate_id ] ) ? (float) $rates[ $rate_id ]['rate'] : 0.0;
				$line['rate']              += $percent;
				$line['tax']               += $amount;
				$line['taxes'][ $rate_id ]  = $amount;
				$line['rates'][ $rate_id ]  = $percent;
			}
			return $line;
		}

		/**
		 * Summary rows grouped by rate (group = 'rate'), by HSN/SAC code and taxes (group = 'hsn'), or by HSN/SAC
		 * code alone (group = 'code'), so products that share a code are added together in one row. Rows are
		 * labelled by the HSN/SAC code, a dash for a product without one, or the line name for shipping and fees
		 * without a SAC code. Includes products, shipping and fees.
		 *
		 * @param WC_Order $order Order.
		 * @param string   $group rate, hsn or code.
		 * @return array[] Rows: { label, qty, rate, taxable, tax, taxes: rate_id => amount, rates: rate_id => percent }.
		 */
		public static function get_summary_rows( $order, $group ) {
			$rates = self::get_order_rates( $order );
			$rows  = array();
			foreach ( (array) $order->get_items( array( 'line_item', 'shipping', 'fee' ) ) as $item ) {
				$line = self::get_line( $item, $rates );
				if ( is_a( $item, 'WC_Order_Item_Product' ) ) {
					$label = Wt_Pklist_Gst_Hsn::get_code( $item->get_product() );
					$qty   = (float) $item->get_quantity();
				} else {
					/**
					 * HSN/SAC code shown for shipping and fee lines in the HSN summary.
					 *
					 * @param string        $code Code (empty shows the line name).
					 * @param WC_Order_Item $item Shipping or fee item.
					 */
					$label = (string) apply_filters( 'wt_pklist_gst_non_product_sac', '', $item ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Established plugin hook prefix.
					$qty   = null;
				}
				// A shipping or fee line without a SAC code is listed under its name, after the coded rows, and never
				// merges with a product whose code happens to read the same.
				$group_by = $label;
				if ( '' === $label && ! is_a( $item, 'WC_Order_Item_Product' ) ) {
					$label    = $item->get_name();
					$group_by = '~' . $item->get_type() . '|' . $label;
				}
				if ( 0.0 === $line['taxable'] && 0.0 === $line['tax'] ) {
					continue;
				}
				$rate_key = self::format_rate( $line['rate'] );
				$rate_ids = array_keys( $line['rates'] );
				sort( $rate_ids );
				if ( 'rate' === $group ) {
					$key = $rate_key;
				} else {
					$key = 'code' === $group ? $group_by : $group_by . '|' . $rate_key . '|' . implode( ',', $rate_ids );
				}
				if ( ! isset( $rows[ $key ] ) ) {
					$rows[ $key ] = array(
						// A product without an HSN/SAC code shows a dash, as in the product table.
						'label'   => 'rate' === $group ? $rate_key : ( '' === $label ? '—' : $label ),
						'qty'     => null,
						'rate'    => $line['rate'],
						'taxable' => 0.0,
						'tax'     => 0.0,
						'taxes'   => array(),
						'rates'   => array(),
					);
				}
				if ( null !== $qty ) {
					$rows[ $key ]['qty'] = (float) $rows[ $key ]['qty'] + $qty;
				}
				$rows[ $key ]['taxable'] += $line['taxable'];
				$rows[ $key ]['tax']     += $line['tax'];
				foreach ( $line['taxes'] as $rate_id => $amount ) {
					$rows[ $key ]['taxes'][ $rate_id ] = ( isset( $rows[ $key ]['taxes'][ $rate_id ] ) ? $rows[ $key ]['taxes'][ $rate_id ] : 0.0 ) + $amount;
					$rows[ $key ]['rates'][ $rate_id ] = $line['rates'][ $rate_id ];
				}
			}
			if ( 'rate' === $group ) {
				uasort(
					$rows,
					function ( $a, $b ) {
						return $a['rate'] <=> $b['rate'];
					}
				);
			} else {
				ksort( $rows, SORT_NATURAL );
			}
			return array_values( (array) apply_filters( 'wt_pklist_gst_summary_rows', $rows, $group, $order ) ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Established plugin hook prefix.
		}

		/* ------------------------------------------------------------------
		 * Formatting
		 * ------------------------------------------------------------------ */

		/**
		 * Rate as a percentage without trailing zeros, e.g. 18 -> "18%", 2.5 -> "2.5%".
		 *
		 * @param float $rate Rate percent.
		 * @return string
		 */
		public static function format_rate( $rate ) {
			$formatted = rtrim( rtrim( number_format( (float) $rate, 4, '.', '' ), '0' ), '.' );
			return ( '' === $formatted || '-0' === $formatted ? '0' : $formatted ) . '%';
		}

		/**
		 * Plain number with the store decimal settings (used in the summary tables).
		 *
		 * @param float $amount Amount.
		 * @return string
		 */
		public static function format_number( $amount ) {
			if ( ! function_exists( 'wc_get_price_decimals' ) ) {
				return number_format( (float) $amount, 2 );
			}
			return number_format( (float) $amount, wc_get_price_decimals(), wc_get_price_decimal_separator(), wc_get_price_thousand_separator() );
		}

		/**
		 * Amount in the order currency, like the other price columns.
		 *
		 * @param float    $amount Amount.
		 * @param WC_Order $order  Order.
		 * @return string
		 */
		public static function format_price( $amount, $order ) {
			if ( class_exists( 'Wf_Woocommerce_Packing_List_Admin' ) && method_exists( 'Wf_Woocommerce_Packing_List_Admin', 'wf_display_price' ) ) {
				return Wf_Woocommerce_Packing_List_Admin::wf_display_price( $order->get_currency(), $order, (float) $amount );
			}
			return wc_price( (float) $amount, array( 'currency' => $order->get_currency() ) );
		}

		/**
		 * Rate-wise summary table.
		 *
		 * @param array[] $rows Rows from get_summary_rows( $order, 'rate' ).
		 * @return string
		 */
		public static function rate_summary_html( $rows ) {
			if ( empty( $rows ) ) {
				return '';
			}
			$heads  = array(
				__( 'Tax rate', 'print-invoices-packing-slip-labels-for-woocommerce' ),
				__( 'Taxable amount', 'print-invoices-packing-slip-labels-for-woocommerce' ),
				__( 'Tax amount', 'print-invoices-packing-slip-labels-for-woocommerce' ),
				__( 'Total', 'print-invoices-packing-slip-labels-for-woocommerce' ),
			);
			$totals = array(
				'taxable' => 0.0,
				'tax'     => 0.0,
			);
			$html   = '<table class="wfte_gst_summary_table wfte_tax_rate_summary_table"><thead><tr>';
			foreach ( $heads as $i => $head ) {
				$html .= '<th class="' . ( 0 === $i ? 'wfte_text_left' : 'wfte_text_right' ) . '">' . esc_html( $head ) . '</th>';
			}
			$html .= '</tr></thead><tbody>';
			foreach ( $rows as $row ) {
				$totals['taxable'] += $row['taxable'];
				$totals['tax']     += $row['tax'];
				$html              .= '<tr><td class="wfte_text_left">@' . esc_html( $row['label'] ) . '</td>'
					. '<td class="wfte_text_right">' . esc_html( self::format_number( $row['taxable'] ) ) . '</td>'
					. '<td class="wfte_text_right">' . esc_html( self::format_number( $row['tax'] ) ) . '</td>'
					. '<td class="wfte_text_right">' . esc_html( self::format_number( $row['taxable'] + $row['tax'] ) ) . '</td></tr>';
			}
			$html .= '</tbody><tfoot><tr><td class="wfte_text_left">' . esc_html__( 'Total', 'print-invoices-packing-slip-labels-for-woocommerce' ) . '</td>'
				. '<td class="wfte_text_right">' . esc_html( self::format_number( $totals['taxable'] ) ) . '</td>'
				. '<td class="wfte_text_right">' . esc_html( self::format_number( $totals['tax'] ) ) . '</td>'
				. '<td class="wfte_text_right">' . esc_html( self::format_number( $totals['taxable'] + $totals['tax'] ) ) . '</td></tr></tfoot></table>';
			return $html;
		}

		/**
		 * HSN/SAC-wise summary table with one amount column per tax of the order.
		 *
		 * @param array[]  $rows      Rows from get_summary_rows( $order, 'hsn' ).
		 * @param bool     $total_row Add the totals row.
		 * @param array    $hidden    Optional columns to leave out: 'qty', 'rate'.
		 * @param string[] $columns   Tax columns, rate_id => tax name, from get_tax_columns().
		 * @return string
		 */
		public static function hsn_summary_html( $rows, $total_row = true, $hidden = array(), $columns = array() ) {
			if ( empty( $rows ) ) {
				return '';
			}
			$columns   = (array) $columns;
			$show_qty  = ! in_array( 'qty', (array) $hidden, true );
			$show_rate = ! in_array( 'rate', (array) $hidden, true );
			$heads     = array( __( 'HSN/SAC', 'print-invoices-packing-slip-labels-for-woocommerce' ) );
			if ( $show_qty ) {
				$heads[] = __( 'Qty', 'print-invoices-packing-slip-labels-for-woocommerce' );
			}
			$left_cols = count( $heads );
			if ( $show_rate ) {
				$heads[] = __( 'GST rate', 'print-invoices-packing-slip-labels-for-woocommerce' );
			}
			$label_cols = count( $heads );
			$heads[]    = __( 'Taxable Amt', 'print-invoices-packing-slip-labels-for-woocommerce' );
			$names      = array_values( array_unique( $columns ) );
			foreach ( $names as $name ) {
				$heads[] = $name;
			}
			$heads[] = __( 'Total tax', 'print-invoices-packing-slip-labels-for-woocommerce' );
			$heads[] = __( 'Total', 'print-invoices-packing-slip-labels-for-woocommerce' );
			$totals  = array(
				'taxable' => 0.0,
				'tax'     => 0.0,
				'taxes'   => array_fill_keys( $names, 0.0 ),
			);
			$html    = '<table class="wfte_gst_summary_table wfte_hsn_summary_table"><thead><tr>';
			foreach ( $heads as $i => $head ) {
				$html .= '<th class="' . ( $i < $left_cols ? 'wfte_text_left' : 'wfte_text_right' ) . '">' . esc_html( $head ) . '</th>';
			}
			$html .= '</tr></thead><tbody>';
			foreach ( $rows as $row ) {
				$totals['taxable'] += $row['taxable'];
				$totals['tax']     += $row['tax'];
				$html              .= '<tr><td class="wfte_text_left">' . esc_html( $row['label'] ) . '</td>';
				if ( $show_qty ) {
					$html .= '<td class="wfte_text_left">' . ( null === $row['qty'] ? '-' : esc_html( wc_stock_amount( $row['qty'] ) ) ) . '</td>';
				}
				if ( $show_rate ) {
					$html .= '<td class="wfte_text_right">' . esc_html( self::format_rate( $row['rate'] ) ) . '</td>';
				}
				$html .= '<td class="wfte_text_right">' . esc_html( self::format_number( $row['taxable'] ) ) . '</td>';
				foreach ( $names as $name ) {
					// A tax that is not charged on the row shows a dash, as on the order edit screen.
					$tax = self::row_tax( $row, $columns, $name );
					if ( null !== $tax ) {
						$totals['taxes'][ $name ] += $tax['amount'];
					}
					$html .= '<td class="wfte_text_right">' . ( null === $tax ? '-' : esc_html( self::format_number( $tax['amount'] ) ) ) . '</td>';
				}
				$html .= '<td class="wfte_text_right">' . esc_html( self::format_number( $row['tax'] ) ) . '</td>'
					. '<td class="wfte_text_right">' . esc_html( self::format_number( $row['taxable'] + $row['tax'] ) ) . '</td></tr>';
			}
			if ( ! $total_row ) {
				return $html . '</tbody></table>';
			}
			$html .= '</tbody><tfoot><tr><td class="wfte_text_left"' . ( $label_cols > 1 ? ' colspan="' . $label_cols . '"' : '' ) . '>' . esc_html__( 'Total', 'print-invoices-packing-slip-labels-for-woocommerce' ) . '</td>'
				. '<td class="wfte_text_right">' . esc_html( self::format_number( $totals['taxable'] ) ) . '</td>';
			foreach ( $totals['taxes'] as $amount ) {
				$html .= '<td class="wfte_text_right">' . esc_html( self::format_number( $amount ) ) . '</td>';
			}
			$html .= '<td class="wfte_text_right">' . esc_html( self::format_number( $totals['tax'] ) ) . '</td>'
				. '<td class="wfte_text_right">' . esc_html( self::format_number( $totals['taxable'] + $totals['tax'] ) ) . '</td></tr></tfoot></table>';
			return $html;
		}

		/**
		 * HSN/SAC-wise summary with each tax of the order as a Rate / Amount column group.
		 *
		 * @param array[]  $rows      Rows from get_summary_rows( $order, 'hsn' ).
		 * @param bool     $total_row Add the totals row.
		 * @param string[] $columns   Tax columns, rate_id => tax name, from get_tax_columns().
		 * @return string
		 */
		public static function hsn_summary_grouped_html( $rows, $total_row = true, $columns = array() ) {
			if ( empty( $rows ) ) {
				return '';
			}
			$columns = (array) $columns;
			$names   = array_values( array_unique( $columns ) );
			$sums    = array(
				'taxable' => 0.0,
				'tax'     => 0.0,
				'taxes'   => array_fill_keys( $names, 0.0 ),
			);
			$html    = '<table class="wfte_gst_summary_table wfte_hsn_summary_table wfte_hsn_summary_grouped_table"><thead><tr>'
				. '<th class="wfte_text_left" rowspan="2">' . esc_html__( 'HSN/SAC', 'print-invoices-packing-slip-labels-for-woocommerce' ) . '</th>'
				. '<th class="wfte_text_right" rowspan="2">' . esc_html__( 'Taxable Amount', 'print-invoices-packing-slip-labels-for-woocommerce' ) . '</th>';
			foreach ( $names as $name ) {
				$html .= '<th class="wfte_text_center" colspan="2">' . esc_html( $name ) . '</th>';
			}
			$html .= '<th class="wfte_text_right" rowspan="2">' . esc_html__( 'Total Tax Amount', 'print-invoices-packing-slip-labels-for-woocommerce' ) . '</th></tr><tr>';
			foreach ( $names as $name ) {
				$html .= '<th class="wfte_text_left">' . esc_html__( 'Rate', 'print-invoices-packing-slip-labels-for-woocommerce' ) . '</th><th class="wfte_text_right">' . esc_html__( 'Amount', 'print-invoices-packing-slip-labels-for-woocommerce' ) . '</th>';
			}
			$html .= '</tr></thead><tbody>';
			foreach ( $rows as $row ) {
				$sums['taxable'] += $row['taxable'];
				$sums['tax']     += $row['tax'];
				$html            .= '<tr><td class="wfte_text_left">' . esc_html( $row['label'] ) . '</td><td class="wfte_text_right">' . esc_html( self::format_number( $row['taxable'] ) ) . '</td>';
				foreach ( $names as $name ) {
					// A tax that is not charged on the row shows dashes, as on the order edit screen.
					$tax = self::row_tax( $row, $columns, $name );
					if ( null === $tax ) {
						$html .= '<td class="wfte_text_left">-</td><td class="wfte_text_right">-</td>';
						continue;
					}
					$sums['taxes'][ $name ] += $tax['amount'];
					$html                   .= '<td class="wfte_text_left">' . esc_html( self::format_rate( $tax['rate'] ) ) . '</td><td class="wfte_text_right">' . esc_html( self::format_number( $tax['amount'] ) ) . '</td>';
				}
				$html .= '<td class="wfte_text_right">' . esc_html( self::format_number( $row['tax'] ) ) . '</td></tr>';
			}
			$html .= '</tbody>';
			if ( $total_row ) {
				$html .= '<tfoot><tr><td class="wfte_text_left">' . esc_html__( 'Total', 'print-invoices-packing-slip-labels-for-woocommerce' ) . '</td><td class="wfte_text_right">' . esc_html( self::format_number( $sums['taxable'] ) ) . '</td>';
				foreach ( $sums['taxes'] as $amount ) {
					$html .= '<td></td><td class="wfte_text_right">' . esc_html( self::format_number( $amount ) ) . '</td>';
				}
				$html .= '<td class="wfte_text_right">' . esc_html( self::format_number( $sums['tax'] ) ) . '</td></tr></tfoot>';
			}
			return $html . '</table>';
		}

		/**
		 * HSN summary for a template: the grouped layout when the template asks for it (wfte_hsn_summary_grouped),
		 * without the totals row when the template asks for that (wfte_hsn_summary_no_total), and without the
		 * Qty or GST rate column when it asks for that (wfte_hsn_summary_no_qty, wfte_hsn_summary_no_rate).
		 *
		 * @param array[]  $rows    Rows.
		 * @param string   $html    Template HTML.
		 * @param string[] $columns Tax columns, rate_id => tax name, from get_tax_columns().
		 * @return string
		 */
		public static function hsn_summary_for_template( $rows, $html, $columns = array() ) {
			$html      = (string) $html;
			$total_row = false === strpos( $html, 'wfte_hsn_summary_no_total' );
			if ( false !== strpos( $html, 'wfte_hsn_summary_grouped' ) ) {
				return self::hsn_summary_grouped_html( $rows, $total_row, $columns );
			}
			$hidden = array();
			foreach ( array( 'qty', 'rate' ) as $column ) {
				if ( false !== strpos( $html, 'wfte_hsn_summary_no_' . $column ) ) {
					$hidden[] = $column;
				}
			}
			return self::hsn_summary_html( $rows, $total_row, $hidden, $columns );
		}

		/**
		 * Put the font size and text colour set on a summary element in the customizer on its table as well.
		 * PDF libraries do not always pass these from the element to a table inside it.
		 *
		 * @param string $table   Summary table HTML.
		 * @param string $html    Template HTML.
		 * @param string $element Class of the summary element, e.g. wfte_hsn_summary.
		 * @return string
		 */
		public static function style_summary_table( $table, $html, $element ) {
			if ( '' === $table || ! preg_match( '/<[a-z]+\b[^>]*\bclass="(?:[^"]*\s)?' . preg_quote( $element, '/' ) . '(?:\s[^"]*)?"[^>]*>/i', (string) $html, $tag ) || ! preg_match( '/\bstyle="([^"]*)"/i', $tag[0], $style ) ) {
				return $table;
			}
			$css = '';
			foreach ( explode( ';', html_entity_decode( $style[1], ENT_QUOTES ) ) as $declaration ) {
				$parts = explode( ':', $declaration, 2 );
				if ( 2 === count( $parts ) && in_array( strtolower( trim( $parts[0] ) ), array( 'font-size', 'color' ), true ) && '' !== trim( $parts[1] ) ) {
					$css .= strtolower( trim( $parts[0] ) ) . ':' . trim( $parts[1] ) . ';';
				}
			}
			return '' === $css ? $table : preg_replace( '/<table\b/i', '<table style="' . esc_attr( $css ) . '"', $table, 1 );
		}

		/**
		 * Bank details as label / value pairs: the bank fields from the General settings, then each
		 * "Label: value" line of the additional bank details. Empty when the bank details toggle is off.
		 *
		 * @return array[] Each: array( label, value ).
		 */
		public static function get_bank_pairs() {
			return class_exists( 'Wt_Pklist_Business_Details' ) ? Wt_Pklist_Business_Details::get_bank_pairs() : array();
		}

		/**
		 * Bank details as one "Label: value" line per field, with the label in bold.
		 *
		 * @param array[] $pairs Pairs from get_bank_pairs().
		 * @return string
		 */
		public static function bank_grid_html( $pairs ) {
			return class_exists( 'Wt_Pklist_Business_Details' ) ? Wt_Pklist_Business_Details::pairs_html( $pairs ) : '';
		}

		/**
		 * Whole number in words, Title Case, e.g. 968 -> "Nine Hundred Sixty-eight".
		 *
		 * @param int  $number Number (0 or more).
		 * @param bool $indian Use the Indian system (Thousand, Lakh, Crore).
		 * @return string
		 */
		public static function number_to_words( $number, $indian = false ) {
			$number = (int) $number;
			$ones   = array( '', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen' );
			$tens   = array( '', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety' );
			if ( 0 === $number ) {
				return 'Zero';
			}
			$below_thousand = function ( $n ) use ( $ones, $tens ) {
				$words = array();
				if ( $n >= 100 ) {
					$words[] = $ones[ intdiv( $n, 100 ) ] . ' Hundred';
					$n      %= 100;
				}
				if ( $n >= 20 ) {
					$words[] = $tens[ intdiv( $n, 10 ) ] . ( $n % 10 ? '-' . strtolower( $ones[ $n % 10 ] ) : '' );
				} elseif ( $n > 0 ) {
					$words[] = $ones[ $n ];
				}
				return implode( ' ', $words );
			};
			$scales = $indian
				? array( 10000000 => 'Crore', 100000 => 'Lakh', 1000 => 'Thousand' )
				: array( 1000000000 => 'Billion', 1000000 => 'Million', 1000 => 'Thousand' );
			$words  = array();
			foreach ( $scales as $size => $name ) {
				if ( $number >= $size ) {
					$count   = intdiv( $number, $size );
					$words[] = ( $count >= 1000 ? self::number_to_words( $count, $indian ) : $below_thousand( $count ) ) . ' ' . $name;
					$number %= $size;
				}
			}
			if ( $number > 0 ) {
				$words[] = $below_thousand( $number );
			}
			return implode( ' ', $words );
		}

		/**
		 * Amount in words with the currency symbol, e.g. "₹ Nine Hundred Sixty-eight And Zero Paisa Only".
		 *
		 * @param float  $amount   Amount.
		 * @param string $currency Currency code.
		 * @return string
		 */
		public static function amount_in_words( $amount, $currency ) {
			$amount   = abs( (float) $amount );
			$whole    = (int) floor( $amount );
			$fraction = (int) round( ( $amount - $whole ) * 100 );
			if ( 100 === $fraction ) {
				++$whole;
				$fraction = 0;
			}
			$indian   = 'INR' === $currency;
			$symbol   = function_exists( 'get_woocommerce_currency_symbol' ) ? html_entity_decode( wp_strip_all_tags( get_woocommerce_currency_symbol( $currency ) ), ENT_QUOTES, 'UTF-8' ) : $currency;
			/**
			 * Name of the currency sub-unit used in the amount in words.
			 *
			 * @param string $sub_unit Sub-unit name.
			 * @param string $currency Currency code.
			 */
			$sub_unit = apply_filters( 'wt_pklist_amount_in_words_sub_unit', $indian ? __( 'Paisa', 'print-invoices-packing-slip-labels-for-woocommerce' ) : __( 'Cents', 'print-invoices-packing-slip-labels-for-woocommerce' ), $currency ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Established plugin hook prefix.
			$text     = sprintf(
				/* translators: 1: currency symbol, 2: whole amount in words, 3: fraction in words, 4: currency sub-unit */
				__( '%1$s %2$s And %3$s %4$s Only', 'print-invoices-packing-slip-labels-for-woocommerce' ),
				$symbol,
				self::number_to_words( $whole, $indian ),
				self::number_to_words( $fraction, $indian ),
				$sub_unit
			);
			return (string) apply_filters( 'wt_pklist_amount_in_words', $text, $amount, $currency ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Established plugin hook prefix.
		}

		/**
		 * Amount in words as HTML. The currency symbol is wrapped like the other prices in the document when the
		 * "additional currency font support" setting wraps it in <span class="wt_pdf_currency_symbol">.
		 *
		 * @param float  $amount   Amount.
		 * @param string $currency Currency code.
		 * @return string
		 */
		public static function amount_in_words_html( $amount, $currency ) {
			$text   = esc_html( self::amount_in_words( $amount, $currency ) );
			$markup = function_exists( 'get_woocommerce_currency_symbol' ) ? get_woocommerce_currency_symbol( $currency ) : '';
			if ( false === strpos( (string) $markup, 'wt_pdf_currency_symbol' ) ) {
				return $text;
			}
			$symbol = esc_html( html_entity_decode( wp_strip_all_tags( $markup ), ENT_QUOTES, 'UTF-8' ) );
			if ( '' === $symbol || 0 !== strpos( $text, $symbol ) ) {
				return $text;
			}
			return '<span class="wt_pdf_currency_symbol">' . $symbol . '</span>' . substr( $text, strlen( $symbol ) );
		}

		/**
		 * Country and state of the place of supply: the shipping address, or the billing address when there is none.
		 *
		 * @param WC_Order $order Order.
		 * @return string[] array( country, state ).
		 */
		public static function supply_location( $order ) {
			if ( $order->get_shipping_country() ) {
				return array( $order->get_shipping_country(), $order->get_shipping_state() );
			}
			return array( $order->get_billing_country(), $order->get_billing_state() );
		}

		/**
		 * Place of supply text, e.g. "Delhi (07)".
		 *
		 * @param WC_Order $order Order.
		 * @return string
		 */
		public static function place_of_supply( $order ) {
			list( $country, $state ) = self::supply_location( $order );
			$text                    = '';
			if ( '' !== (string) $state ) {
				$states = function_exists( 'WC' ) && WC()->countries ? WC()->countries->get_states( $country ) : array();
				$text   = is_array( $states ) && isset( $states[ $state ] ) ? $states[ $state ] : $state;
				if ( 'IN' === $country && isset( self::$state_codes[ $state ] ) ) {
					$text .= ' (' . self::$state_codes[ $state ] . ')';
				}
			} elseif ( '' !== (string) $country && function_exists( 'WC' ) && WC()->countries ) {
				$countries = WC()->countries->get_countries();
				$text      = isset( $countries[ $country ] ) ? $countries[ $country ] : $country;
			}
			return (string) apply_filters( 'wt_pklist_place_of_supply', $text, $order ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Established plugin hook prefix.
		}

		/* ------------------------------------------------------------------
		 * Documents
		 * ------------------------------------------------------------------ */

		/**
		 * Placeholder values for an invoice.
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
			if ( 'invoice' !== $template_type || ! is_object( $order ) || ! is_a( $order, 'WC_Order' ) || ! is_string( $html ) ) {
				return $find_replace;
			}
			if ( false !== strpos( $html, '[wfte_place_of_supply]' ) ) {
				$find_replace['[wfte_place_of_supply]'] = esc_html( self::place_of_supply( $order ) );
			}
			if ( false !== strpos( $html, '[wfte_tax_rate_summary]' ) ) {
				$find_replace['[wfte_tax_rate_summary]'] = self::style_summary_table( self::rate_summary_html( self::get_summary_rows( $order, 'rate' ) ), $html, 'wfte_tax_rate_summary' );
			}
			if ( false !== strpos( $html, '[wfte_hsn_summary]' ) ) {
				// Without the GST rate column a code gets one row; with it, one row per rate of the code.
				$group = false === strpos( $html, 'wfte_hsn_summary_grouped' ) && false !== strpos( $html, 'wfte_hsn_summary_no_rate' ) ? 'code' : 'hsn';
				// Shown for orders without tax as well, with the taxable amounts and no tax columns.
				$find_replace['[wfte_hsn_summary]'] = self::style_summary_table( self::hsn_summary_for_template( self::get_summary_rows( $order, $group ), $html, self::get_tax_columns( $order ) ), $html, 'wfte_hsn_summary' );
			}
			if ( false !== strpos( $html, '[wfte_bank_grid]' ) && class_exists( 'Wt_Pklist_Business_Details' ) ) {
				// Older templates: same output as [wfte_bank_details].
				$find_replace['[wfte_bank_grid]'] = Wt_Pklist_Business_Details::bank_details( $order );
			}
			if ( false !== strpos( $html, 'wfte_total_in_words_currency' ) ) {
				$find_replace['[wfte_total_in_words]'] = self::amount_in_words_html( $order->get_total(), $order->get_currency() );
			}
			if ( false !== strpos( $html, '[wfte_product_table_total_qty]' ) && ( ! isset( $find_replace['[wfte_product_table_total_qty]'] ) || '' === $find_replace['[wfte_product_table_total_qty]'] ) ) {
				$find_replace['[wfte_product_table_total_qty]'] = apply_filters( 'wf_pklist_alter_total_quantity', self::total_qty( $order, $template_type ), $template_type, $order ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Established plugin hook prefix.
			}
			return $find_replace;
		}

		/**
		 * Total item quantity, less refunded quantities when the document shows details after refund.
		 *
		 * @param WC_Order $order         Order.
		 * @param string   $template_type Document type.
		 * @return float
		 */
		public static function total_qty( $order, $template_type ) {
			$after_refund = (bool) apply_filters( 'wt_pklist_show_details_after_refund_' . $template_type, false, $template_type ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Established plugin hook prefix.
			$qty          = 0;
			foreach ( (array) $order->get_items() as $item ) {
				$qty += (float) $item->get_quantity();
				if ( $after_refund ) {
					$qty -= abs( (float) $order->get_qty_refunded_for_item( $item->get_id() ) );
				}
			}
			return wc_stock_amount( $qty );
		}

		/**
		 * Hide the elements when their value is empty.
		 *
		 * @param array $fields Class names.
		 * @return array
		 */
		public static function hide_on_empty( $fields ) {
			foreach ( array_keys( self::get_fields() ) as $key ) {
				$fields[] = 'wfte_' . $key;
			}
			$fields[] = 'wfte_bank_grid';
			return array_values( array_unique( $fields ) );
		}

		/**
		 * Product table cell values for the GST columns.
		 *
		 * @param string $column_data   Value from earlier filters.
		 * @param string $template_type Document type.
		 * @param string $columns_key   Column key.
		 * @param mixed  $product       Product.
		 * @param mixed  $item          Order item.
		 * @param mixed  $order         Order.
		 * @param array  $options       Column options.
		 * @return string
		 */
		public static function column_value( $column_data, $template_type, $columns_key, $product = null, $item = null, $order = null, $options = array() ) {
			$column = ltrim( (string) $columns_key, '-' );
			if ( ! array_key_exists( $column, self::get_columns() ) ) {
				return $column_data;
			}
			if ( Wt_Pklist_Gst_Hsn::COLUMN === $column ) {
				$code = Wt_Pklist_Gst_Hsn::get_code( $product, Wt_Pklist_Gst_Hsn::current_source( $options, $columns_key ) );
				return '' === $code ? '—' : esc_html( $code );
			}
			if ( ! is_object( $item ) || ! is_a( $item, 'WC_Order_Item_Product' ) || ! is_object( $order ) || ! is_a( $order, 'WC_Order' ) ) {
				return '-';
			}
			/**
			 * Whether the GST columns use the line amounts after (default) or before the coupon discount.
			 *
			 * @param string $discount_type after_discount or before_discount.
			 * @param string $column        Column key.
			 */
			$discount_type = 'before_discount' === apply_filters( 'wt_pklist_gst_column_discount_type', 'after_discount', $column ) ? 'before_discount' : 'after_discount'; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Established plugin hook prefix.
			$line          = self::get_line( $item, self::get_order_rates( $order ), $discount_type );
			if ( 'line_amount' === $column ) {
				return self::format_price( $line['taxable'] + $line['tax'], $order );
			}
			return self::format_price( $line['taxable'], $order );
		}

		/**
		 * mPDF positions absolute/fixed blocks only when they are direct children of <body>, so the footer, which sits
		 * inside the template wrapper, stays in the content flow. Move it to the end of <body> and fix it to the bottom
		 * of the page, for the cases where the footer is meant to be at the bottom.
		 *
		 * @param string $html             Template HTML.
		 * @param string $template_type    Document type.
		 * @param mixed  $order            Order.
		 * @param mixed  $box_packing      Box packing.
		 * @param mixed  $order_package    Package.
		 * @param bool   $template_for_pdf Rendering for PDF.
		 * @return string
		 */
		public static function mpdf_footer_to_bottom( $html, $template_type, $order = null, $box_packing = null, $order_package = null, $template_for_pdf = false ) {
			if ( ! $template_for_pdf || ! is_string( $html ) || ! class_exists( 'Wf_Woocommerce_Packing_List' ) || 'mpdf' !== Wf_Woocommerce_Packing_List::get_option( 'active_pdf_library' ) ) {
				return $html;
			}
			$footer_text = (string) Wf_Woocommerce_Packing_List::get_option( 'woocommerce_wf_packinglist_footer' );
			if ( ! apply_filters( 'wt_pklist_footer_to_the_bottom', str_word_count( $footer_text ) <= 40, $order, $template_type ) ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Established plugin hook prefix.
				return $html;
			}
			$start = strpos( $html, '<div class="template_footer' );
			if ( false === $start ) {
				return $html;
			}
			// Find the matching closing </div>.
			$depth = 0;
			$pos   = $start;
			$end   = false;
			while ( preg_match( '#<div\b|</div>#i', $html, $match, PREG_OFFSET_CAPTURE, $pos ) ) {
				$depth += ( '</div>' === strtolower( $match[0][0] ) ) ? -1 : 1;
				$pos    = $match[0][1] + strlen( $match[0][0] );
				if ( 0 === $depth ) {
					$end = $pos;
					break;
				}
			}
			if ( false === $end ) {
				return $html;
			}
			$footer = substr( $html, $start, $end - $start );
			$html   = substr( $html, 0, $start ) . substr( $html, $end );
			$footer = '<div class="wt_pklist_mpdf_footer" style="position:fixed; left:5mm; right:5mm; bottom:5mm;">' . $footer . '</div>';
			$body   = strripos( $html, '</body>' );
			return false === $body ? $html . $footer : substr( $html, 0, $body ) . $footer . substr( $html, $body );
		}

		/* ------------------------------------------------------------------
		 * Customizer
		 * ------------------------------------------------------------------ */

		/**
		 * Sample rows for the customizer preview.
		 *
		 * @return array[]
		 */
		private static function sample_rows() {
			return array(
				array(
					'label'   => '9405',
					'qty'     => 5,
					'rate'    => 2,
					'taxable' => 100,
					'tax'     => 2,
					'taxes'   => array(
						1 => 1,
						2 => 1,
					),
					'rates'   => array(
						1 => 1,
						2 => 1,
					),
				),
			);
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
			if ( 'invoice' !== $template_type ) {
				return $find_replace;
			}
			$rate_rows = self::sample_rows();
			foreach ( $rate_rows as $i => $row ) {
				$rate_rows[ $i ]['label'] = self::format_rate( $row['rate'] );
			}
			$samples = array(
				'place_of_supply'  => 'Kerala (32)',
				'tax_rate_summary' => self::rate_summary_html( $rate_rows ),
				'hsn_summary'      => self::hsn_summary_for_template(
					self::sample_rows(),
					$html,
					array(
						1 => 'CGST',
						2 => 'SGST',
					)
				),
				'bank_grid'        => self::bank_grid_html(
					self::get_bank_pairs() ? self::get_bank_pairs() : array(
						array( __( 'Bank name', 'print-invoices-packing-slip-labels-for-woocommerce' ), 'Example Bank' ),
						array( __( 'A/C No.', 'print-invoices-packing-slip-labels-for-woocommerce' ), '1234567890' ),
						array( 'IFSC', 'EXMP0001234' ),
					)
				),
			);
			foreach ( $samples as $key => $value ) {
				if ( empty( $find_replace[ '[wfte_' . $key . ']' ] ) ) {
					$find_replace[ '[wfte_' . $key . ']' ] = $value;
				}
			}
			if ( false !== strpos( (string) $html, '[wfte_product_table_total_qty]' ) && empty( $find_replace['[wfte_product_table_total_qty]'] ) ) {
				$find_replace['[wfte_product_table_total_qty]'] = '5';
			}
			if ( false !== strpos( (string) $html, 'wfte_total_in_words_currency' ) ) {
				// The customizer sample prices are in US dollars.
				$find_replace['[wfte_total_in_words]'] = esc_html( self::amount_in_words( 102, 'USD' ) );
			}
			return $find_replace;
		}

		/**
		 * Sample product table cells for the customizer preview.
		 *
		 * @param string $column_val  Value.
		 * @param string $columns_key Column key.
		 * @return string
		 */
		public static function dummy_col_value( $column_val, $columns_key ) {
			$samples = array(
				'hsn_code'       => '9405',
				'taxable_amount' => '$100.00',
				'line_amount'    => '$102.00',
			);
			$column  = ltrim( (string) $columns_key, '-' );
			return isset( $samples[ $column ] ) ? $samples[ $column ] : $column_val;
		}

		/**
		 * Add the fields to the invoice customizer side panel.
		 *
		 * @param array  $settings Items.
		 * @param string $base_id  Module ID.
		 * @return array
		 */
		public static function customizable_items( $settings, $base_id ) {
			if ( ! is_array( $settings ) || ! class_exists( 'Wf_Woocommerce_Packing_List' ) || Wf_Woocommerce_Packing_List::get_module_id( 'invoice' ) !== $base_id ) {
				return $settings;
			}
			foreach ( self::get_fields() as $key => $label ) {
				if ( ! isset( $settings[ $key ] ) ) {
					$settings[ $key ] = $label;
				}
			}
			return $settings;
		}

		/**
		 * Panel inputs for the fields and the product table columns.
		 *
		 * @param array  $fields        Panel fields.
		 * @param string $type          Panel item.
		 * @param string $template_type Document type.
		 * @return array
		 */
		public static function customize_inputs( $fields, $type, $template_type ) {
			if ( 'invoice' !== $template_type ) {
				return $fields;
			}
			if ( array_key_exists( $type, self::get_fields() ) && ( ! is_array( $fields ) || empty( $fields ) ) ) {
				$fields = array();
				if ( 'place_of_supply' === $type ) {
					$fields[] = array(
						'label'    => __( 'Title', 'print-invoices-packing-slip-labels-for-woocommerce' ),
						'css_prop' => 'html',
						'trgt_elm' => $type . '_label',
					);
				}
				$fields[] = array(
					'label'    => __( 'Font size', 'print-invoices-packing-slip-labels-for-woocommerce' ),
					'type'     => 'text_inputgrp',
					'css_prop' => 'font-size',
					'trgt_elm' => $type,
					'unit'     => 'px',
				);
				$fields[] = array(
					'label'       => __( 'Text color', 'print-invoices-packing-slip-labels-for-woocommerce' ),
					'type'        => 'color',
					'css_prop'    => 'color',
					'trgt_elm'    => $type,
					'event_class' => 'wf_cst_click',
				);
				return $fields;
			}
			if ( 'product_table' !== $type || ! is_array( $fields ) ) {
				return $fields;
			}
			$align_options = class_exists( 'Wf_Woocommerce_Packing_List_Customizer_Ipc_PRO' ) && method_exists( 'Wf_Woocommerce_Packing_List_Customizer_Ipc_PRO', 'get_customizer_presets' )
				? Wf_Woocommerce_Packing_List_Customizer_Ipc_PRO::get_customizer_presets( 'text-align' )
				: array(
					'left'   => __( 'Left', 'print-invoices-packing-slip-labels-for-woocommerce' ),
					'right'  => __( 'Right', 'print-invoices-packing-slip-labels-for-woocommerce' ),
					'center' => __( 'Center', 'print-invoices-packing-slip-labels-for-woocommerce' ),
				);
			foreach ( self::get_columns() as $column => $label ) {
				$target   = 'product_table_head_' . $column;
				$fields[] = array(
					'label'       => '&nbsp;',
					'type'        => 'checkbox',
					'trgt_elm'    => $target,
					'event_class' => 'wf_cst_toggler',
					'width'       => '10%',
				);
				$fields[] = array(
					/* translators: %s: column name */
					'label'    => sprintf( __( '%s label', 'print-invoices-packing-slip-labels-for-woocommerce' ), $label ),
					'css_prop' => 'html',
					'trgt_elm' => $target,
					'width'    => '44%',
				);
				$fields[] = array(
					/* translators: %s: column name */
					'label'          => sprintf( __( '%s text align', 'print-invoices-packing-slip-labels-for-woocommerce' ), $label ),
					'type'           => 'select',
					'select_options' => $align_options,
					'css_prop'       => 'text-align',
					'trgt_elm'       => $target,
					'event_class'    => 'wf_cst_change',
					'width'          => '44%',
					'float'          => 'right',
				);
				if ( Wt_Pklist_Gst_Hsn::COLUMN === $column ) {
					$fields = array_merge( $fields, Wt_Pklist_Gst_Hsn::customize_fields( $target ) );
				}
			}
			return $fields;
		}
	}
}
