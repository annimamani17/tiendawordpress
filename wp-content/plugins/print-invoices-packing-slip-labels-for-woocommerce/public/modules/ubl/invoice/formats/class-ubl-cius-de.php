<?php
/**
 * German UBL format (XRechnung 3.0, CIUS of EN 16931).
 *
 * @package print-invoices-packing-slip-labels-for-woocommerce
 */

namespace Wtpdf\Ubl\Invoice\Formats;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( '\\Wtpdf\\Ubl\\Invoice\\Formats\\UblCiusDe' ) ) {

	/**
	 * Builds the XRechnung 3.0 UBL invoice.
	 */
	class UblCiusDe extends \Wtpdf\Ubl\Documents\Invoice {

		const INVOICE_TYPE_CODE = '380';
		const CUSTOMIZATION_ID  = 'urn:cen.eu:en16931:2017#compliant#urn:xeinkauf.de:kosit:xrechnung_3.0';
		const PROFILE_ID        = 'urn:fdc:peppol.eu:2017:poacc:billing:01:1.0';

		/**
		 * WooCommerce order.
		 *
		 * @var \WC_Order|null
		 */
		public $order = null;

		/**
		 * Invoice type code (BT-3).
		 *
		 * @var string
		 */
		protected $typcode = self::INVOICE_TYPE_CODE;

		/**
		 * UBL element map.
		 *
		 * @var array
		 */
		public $elements = array();

		/**
		 * Format key as stored in the settings.
		 *
		 * @var string
		 */
		public $ubl_format_name = 'ubl_cius_de';

		/**
		 * Per-line amounts and tax categories, computed once per order.
		 *
		 * @var array
		 */
		private $lines = array();

		/**
		 * Constructor.
		 *
		 * @param \WC_Order|null $order WooCommerce order.
		 */
		public function __construct( $order = null ) {
			parent::__construct();
			$this->order = $order;
			$this->lines = $this->build_lines();

			// Keys are emitted in this order, which must follow the UBL Invoice schema sequence.
			$this->elements = array(
				'specification'                 => $this->element( $this->text_node( 'cbc:CustomizationID', self::CUSTOMIZATION_ID ) ),
				'profile_identifier'            => $this->element( $this->text_node( 'cbc:ProfileID', self::PROFILE_ID ) ),
				'invoice_number'                => $this->element( $this->get_formatted_invoice_number( $this->order, $this->ubl_format_name ) ),
				'issue_date'                    => $this->element( $this->get_formatted_issue_date( $this->order, $this->ubl_format_name ) ),
				'due_date'                      => $this->element( $this->get_formatted_due_date( $this->order, $this->ubl_format_name ) ),
				'invoice_typecode'              => $this->element( $this->text_node( 'cbc:InvoiceTypeCode', $this->typcode ) ),
				'notes'                         => $this->element( $this->get_formatted_notes( $this->order, $this->ubl_format_name ) ),
				'currency_code'                 => $this->element( $this->get_formatted_currency_code( $this->order, $this->ubl_format_name ) ),
				'buyer_accounting_reference'    => $this->element( $this->get_formatted_buyer_accounting_reference( $this->order, $this->ubl_format_name ) ),
				'buyer_reference'               => $this->element( $this->get_formatted_buyer_reference( $this->order, $this->ubl_format_name ) ),
				'order_reference'               => $this->element( $this->get_formatted_order_reference( $this->order, $this->ubl_format_name ) ),
				'originator_document_reference' => $this->element( $this->get_formatted_originator_document_reference( $this->order, $this->ubl_format_name ) ),
				'contract_document_reference'   => $this->element( $this->get_formatted_contract_document_reference( $this->order, $this->ubl_format_name ) ),
				'seller'                        => $this->element( $this->get_formatted_seller() ),
				'buyer'                         => $this->element( $this->get_formatted_buyer() ),
				'delivery'                      => $this->element( $this->get_formatted_delivery() ),
				'payment_means'                 => $this->element( $this->get_formatted_payment_means() ),
				'payment_terms'                 => $this->element( $this->get_formatted_payment_terms() ),
				'tax_subtotal'                  => $this->element( $this->get_formatted_tax_subtotal_details() ),
				'legal_monitory_tax_total'      => $this->element( $this->get_formatted_legal_monitory_tax_total() ),
				'invoice_lines'                 => $this->element( $this->get_formatted_invoice_lines() ),
			);
		}

		/**
		 * Creates a new instance of the class.
		 *
		 * @param \WC_Order|null $order WooCommerce order.
		 * @return self
		 */
		public static function instance( $order = null ) {
			return new self( $order );
		}

		/**
		 * XML namespaces of the UBL Invoice document.
		 *
		 * @return array
		 */
		public function get_document_namespace() {
			return array(
				'xmlns'     => 'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2',
				'xmlns:cac' => 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2',
				'xmlns:cbc' => 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2',
			);
		}

		/**
		 * Elements to render, with empty nodes removed (XRechnung rejects empty elements).
		 *
		 * @return array
		 */
		public function get_formatted_elements() {
			$elements = apply_filters( 'wtpdf_ubl_format_elements', $this->elements, $this->order, 'ublinvoice' );

			foreach ( $elements as $key => $element ) {
				$value = isset( $element['value_arr'] ) ? $this->remove_empty_nodes( $element['value_arr'] ) : null;
				if ( null === $value || array() === $value ) {
					$elements[ $key ]['enabled'] = false;
				} else {
					$elements[ $key ]['value_arr'] = $value;
				}
			}
			return $elements;
		}

		/**
		 * Invoice type code (BT-3).
		 *
		 * @param \WC_Order $order WooCommerce order.
		 * @return string
		 */
		public function get_invoice_typecode( $order ): string {
			return $this->typcode;
		}

		/**
		 * Buyer reference (BT-10), mandatory in XRechnung (BR-DE-15).
		 *
		 * Public-sector buyers expect their Leitweg-ID here; use the filter to supply it.
		 *
		 * @param \WC_Order $order WooCommerce order.
		 * @return string
		 */
		public function get_buyer_reference( $order ) {
			return (string) apply_filters( 'wtpdf_ubl_xrechnung_buyer_reference', $order->get_order_number(), $order );
		}

		/**
		 * Shop country code without the state suffix.
		 *
		 * @return string
		 */
		public function get_shop_country() {
			$country = parent::get_shop_country();
			if ( ! is_string( $country ) ) {
				return '';
			}
			$parts = explode( ':', $country );
			return $parts[0];
		}

		/**
		 * Seller party (BG-4), including electronic address (BT-34) and contact (BG-6).
		 *
		 * @return array
		 */
		public function get_formatted_seller() {
			$email   = $this->get_general_option( 'woocommerce_wf_packinglist_sender_email', get_option( 'woocommerce_email_from_address' ) );
			$name    = $this->get_shop_name();
			$vat     = $this->get_general_option( 'woocommerce_wf_packinglist_sender_vat' );
			$address = array(
				$this->text_node( 'cbc:StreetName', $this->get_address_line() ),
				$this->text_node( 'cbc:AdditionalStreetName', $this->get_general_option( 'woocommerce_wf_packinglist_sender_address_line2', get_option( 'woocommerce_store_address_2' ) ) ),
				$this->text_node( 'cbc:CityName', $this->get_shop_city() ),
				$this->text_node( 'cbc:PostalZone', $this->get_shop_postalcode() ),
				$this->node( 'cac:Country', array( $this->text_node( 'cbc:IdentificationCode', $this->get_shop_country() ) ) ),
			);

			$party = array(
				$this->text_node( 'cbc:EndpointID', $email, array( 'schemeID' => 'EM' ) ),
				$this->node( 'cac:PartyName', array( $this->text_node( 'cbc:Name', $name ) ) ),
				$this->node( 'cac:PostalAddress', $address ),
			);

			if ( '' !== $vat ) {
				// A VAT ID (BT-31) must start with a country code (BR-CO-9); anything else, such as a German Steuernummer, is a tax registration number (BT-32).
				$party[] = $this->node(
					'cac:PartyTaxScheme',
					array(
						$this->text_node( 'cbc:CompanyID', $vat ),
						$this->node( 'cac:TaxScheme', array( $this->text_node( 'cbc:ID', $this->is_vat_id( $vat ) ? 'VAT' : 'FC' ) ) ),
					)
				);
			}

			$party[] = $this->node( 'cac:PartyLegalEntity', array( $this->text_node( 'cbc:RegistrationName', $name ) ) );
			$party[] = $this->node(
				'cac:Contact',
				array(
					$this->text_node( 'cbc:Name', $this->get_general_option( 'woocommerce_wf_packinglist_sender_name', $name ) ),
					$this->text_node( 'cbc:Telephone', $this->get_general_option( 'woocommerce_wf_packinglist_sender_contact_number' ) ),
					$this->text_node( 'cbc:ElectronicMail', $email ),
				)
			);

			return $this->node( 'cac:AccountingSupplierParty', array( $this->node( 'cac:Party', $party ) ) );
		}

		/**
		 * Buyer party (BG-7), including electronic address (BT-49).
		 *
		 * @return array
		 */
		public function get_formatted_buyer() {
			$order     = $this->order;
			$full_name = trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() );
			$company   = $order->get_billing_company();
			$legal     = '' !== $company ? $company : $full_name;
			$vat       = $this->get_buyer_vat_id();

			// Buyer VAT ID (BT-48), needed for reverse charge and intra-community supplies (BR-AE-02, BR-IC-02).
			$buyer_tax_scheme = array();
			if ( $this->is_vat_id( $vat ) ) {
				$buyer_tax_scheme = array(
					$this->text_node( 'cbc:CompanyID', $vat ),
					$this->node( 'cac:TaxScheme', array( $this->text_node( 'cbc:ID', 'VAT' ) ) ),
				);
			}

			$party = array(
				$this->text_node( 'cbc:EndpointID', $order->get_billing_email(), array( 'schemeID' => 'EM' ) ),
				$this->node( 'cac:PartyName', array( $this->text_node( 'cbc:Name', $legal ) ) ),
				$this->node(
					'cac:PostalAddress',
					array(
						$this->text_node( 'cbc:StreetName', $order->get_billing_address_1() ),
						$this->text_node( 'cbc:AdditionalStreetName', $order->get_billing_address_2() ),
						$this->text_node( 'cbc:CityName', $order->get_billing_city() ),
						$this->text_node( 'cbc:PostalZone', $order->get_billing_postcode() ),
						$this->node( 'cac:Country', array( $this->text_node( 'cbc:IdentificationCode', $order->get_billing_country() ) ) ),
					)
				),
				$this->node( 'cac:PartyTaxScheme', $buyer_tax_scheme ),
				$this->node( 'cac:PartyLegalEntity', array( $this->text_node( 'cbc:RegistrationName', $legal ) ) ),
				$this->node(
					'cac:Contact',
					array(
						$this->text_node( 'cbc:Name', $full_name ),
						$this->text_node( 'cbc:Telephone', $order->get_billing_phone() ),
						$this->text_node( 'cbc:ElectronicMail', $order->get_billing_email() ),
					)
				),
			);

			return $this->node( 'cac:AccountingCustomerParty', array( $this->node( 'cac:Party', $party ) ) );
		}

		/**
		 * Delivery information (BG-13). XRechnung expects an actual delivery date (BT-72) or an invoicing period (BR-DE-TMP-32).
		 *
		 * Uses the order's completed date, then its paid date, then its created date.
		 *
		 * @return array
		 */
		public function get_formatted_delivery() {
			$order = $this->order;
			$date  = $order->get_date_completed();
			if ( ! $date ) {
				$date = $order->get_date_paid();
			}
			if ( ! $date ) {
				$date = $order->get_date_created();
			}
			$date = $date ? $date->date( 'Y-m-d' ) : '';
			$date = (string) apply_filters( 'wtpdf_ubl_xrechnung_delivery_date', $date, $order );

			return $this->node( 'cac:Delivery', array( $this->text_node( 'cbc:ActualDeliveryDate', $date ) ) );
		}

		/**
		 * Payment instructions (BG-16), mandatory in XRechnung (BR-DE-1).
		 *
		 * Codes 30/58 require a payee IBAN (BR-DE-19, BR-DE-23), so bank transfer only uses 58 when one is configured.
		 *
		 * @return array
		 */
		public function get_formatted_payment_means() {
			$method  = $this->order->get_payment_method();
			$account = 'bacs' === $method ? $this->get_bacs_account() : array();
			$codes   = array(
				'cod'    => '10',
				'cheque' => '20',
			);

			if ( ! empty( $account['iban'] ) ) {
				$code = '58';
			} else {
				$code = isset( $codes[ $method ] ) ? $codes[ $method ] : 'ZZZ';
			}
			$code = (string) apply_filters( 'wtpdf_ubl_xrechnung_payment_means_code', $code, $method, $this->order );

			$value = array(
				$this->text_node( 'cbc:PaymentMeansCode', $code ),
				$this->text_node( 'cbc:PaymentID', (string) $this->get_invoice_number( $this->order ) ),
			);

			if ( '58' === $code && ! empty( $account['iban'] ) ) {
				$value[] = $this->node(
					'cac:PayeeFinancialAccount',
					array(
						$this->text_node( 'cbc:ID', strtoupper( str_replace( ' ', '', $account['iban'] ) ) ),
						$this->text_node( 'cbc:Name', isset( $account['account_name'] ) ? $account['account_name'] : '' ),
						$this->node(
							'cac:FinancialInstitutionBranch',
							array( $this->text_node( 'cbc:ID', isset( $account['bic'] ) ? str_replace( ' ', '', $account['bic'] ) : '' ) )
						),
					)
				);
			}

			return $this->node( 'cac:PaymentMeans', $value );
		}

		/**
		 * Payment terms (BT-20); satisfies BR-CO-25 when no due date is set.
		 *
		 * @return array
		 */
		public function get_formatted_payment_terms() {
			$title = $this->order->get_payment_method_title();
			if ( '' === trim( (string) $title ) ) {
				$title = __( 'Payable immediately', 'print-invoices-packing-slip-labels-for-woocommerce' );
			}
			return $this->node( 'cac:PaymentTerms', array( $this->text_node( 'cbc:Note', $title ) ) );
		}

		/**
		 * Document-level VAT breakdown (BG-23), one subtotal per category and rate.
		 *
		 * @return array
		 */
		public function get_formatted_tax_subtotal_details() {
			$currency  = $this->order->get_currency();
			$subtotals = array();
			$tax_total = 0.0;

			foreach ( $this->get_tax_breakdown() as $group ) {
				$tax_total  += $group['tax'];
				$subtotals[] = $this->node(
					'cac:TaxSubtotal',
					array(
						$this->amount_node( 'cbc:TaxableAmount', $group['taxable'], $currency ),
						$this->amount_node( 'cbc:TaxAmount', $group['tax'], $currency ),
						$this->node( 'cac:TaxCategory', $this->tax_category_children( $group['category'], true ) ),
					)
				);
			}

			return $this->node(
				'cac:TaxTotal',
				array_merge(
					array( $this->amount_node( 'cbc:TaxAmount', $tax_total, $currency ) ),
					$subtotals
				)
			);
		}

		/**
		 * Document totals (BG-22), derived from the line and VAT amounts so BR-CO-10/13/15 hold.
		 *
		 * @return array
		 */
		public function get_formatted_legal_monitory_tax_total() {
			$currency   = $this->order->get_currency();
			$line_total = 0.0;
			$tax_total  = 0.0;
			foreach ( $this->lines as $line ) {
				$line_total += $line['amount'];
			}
			foreach ( $this->get_tax_breakdown() as $group ) {
				$tax_total += $group['tax'];
			}
			$line_total = round( $line_total, 2 );
			$inclusive  = round( $line_total + $tax_total, 2 );

			// VAT per rate group can differ by a few cents from the per-line VAT WooCommerce charged.
			// Rounding amount (BT-114) absorbs that so the amount due equals the order total. Larger gaps are left visible.
			$rounding = round( (float) $this->order->get_total() - $inclusive, 2 );
			if ( abs( $rounding ) > 0.01 * max( 1, count( $this->lines ) ) ) {
				$rounding = 0.0;
			}
			$payable = round( $inclusive + $rounding, 2 );

			// Already paid orders (card, PayPal, ...) owe nothing: report the payment as prepaid (BT-113).
			$prepaid = $this->order->get_date_paid() ? $payable : 0.0;

			$totals = array(
				$this->amount_node( 'cbc:LineExtensionAmount', $line_total, $currency ),
				$this->amount_node( 'cbc:TaxExclusiveAmount', $line_total, $currency ),
				$this->amount_node( 'cbc:TaxInclusiveAmount', $inclusive, $currency ),
			);
			if ( 0.0 !== $prepaid ) {
				$totals[] = $this->amount_node( 'cbc:PrepaidAmount', $prepaid, $currency );
			}
			if ( 0.0 !== $rounding ) {
				$totals[] = $this->amount_node( 'cbc:PayableRoundingAmount', $rounding, $currency );
			}
			$totals[] = $this->amount_node( 'cbc:PayableAmount', $payable - $prepaid, $currency );

			return $this->node( 'cac:LegalMonetaryTotal', $totals );
		}

		/**
		 * Invoice lines (BG-25). Line-level TaxTotal is not allowed in EN 16931; each item carries its VAT category instead.
		 *
		 * @return array
		 */
		public function get_formatted_invoice_lines() {
			$currency = $this->order->get_currency();
			$data     = array();

			foreach ( $this->lines as $line ) {
				$item      = $line['item'];
				$unit_code = apply_filters( 'wtpdf_ubl_invoice_line_quantity_unit', 'H87', $item, $this->order, $this->ubl_format_name, 'ublinvoice' );

				$data[] = $this->node(
					'cac:InvoiceLine',
					array(
						$this->text_node( 'cbc:ID', (string) $line['id'] ),
						$this->text_node( 'cbc:InvoicedQuantity', $line['quantity'], array( 'unitCode' => $unit_code ) ),
						$this->amount_node( 'cbc:LineExtensionAmount', $line['amount'], $currency ),
						$this->node(
							'cac:Item',
							array(
								$this->text_node( 'cbc:Name', $item->get_name() ),
								$this->node( 'cac:SellersItemIdentification', array( $this->text_node( 'cbc:ID', $this->get_item_sku( $item ) ) ) ),
								$this->node( 'cac:ClassifiedTaxCategory', $this->tax_category_children( $line['category'], false ) ),
							)
						),
						$this->node(
							'cac:Price',
							array(
								array(
									'name'       => 'cbc:PriceAmount',
									'value'      => $line['price'],
									'attributes' => array( 'currencyID' => $currency ),
								),
							)
						),
					)
				);
			}

			return $data;
		}

		/**
		 * Seller's item identifier (BT-155): the product SKU, empty for shipping and fee lines.
		 *
		 * @param \WC_Order_Item $item Order item.
		 * @return string
		 */
		private function get_item_sku( $item ) {
			if ( ! is_a( $item, 'WC_Order_Item_Product' ) ) {
				return '';
			}
			$product = $item->get_product();
			return $product ? (string) $product->get_sku() : '';
		}

		/**
		 * Collects line amounts and the VAT category of each order item.
		 *
		 * @return array
		 */
		private function build_lines() {
			if ( ! is_a( $this->order, 'WC_Order' ) ) {
				return array();
			}

			$tax_rates = $this->get_order_tax_rates();
			$lines     = array();

			foreach ( $this->order->get_items( array( 'line_item', 'fee', 'shipping' ) ) as $item_id => $item ) {
				$amount = round( (float) $item->get_total(), 2 );
				$parts  = $this->get_item_tax_parts( $item, $tax_rates, $amount );

				foreach ( $parts as $index => $part ) {
					// A line split between tax rates is invoiced as one line per rate.
					$quantity = 1 === count( $parts ) ? abs( (float) $item->get_quantity() ) : 1;
					$quantity = $quantity > 0 ? $quantity : 1;

					// BR-27: the net price must not be negative, so negative lines (discount fees) get a negative quantity.
					if ( $part['amount'] < 0 ) {
						$quantity = -$quantity;
					}

					$lines[] = array(
						'id'       => 0 === $index ? $item_id : $item_id . '-' . ( $index + 1 ),
						'item'     => $item,
						'amount'   => $part['amount'],
						'quantity' => $quantity,
						'price'    => round( abs( $part['amount'] ) / abs( $quantity ), 4 ),
						'category' => $part['category'],
					);
				}
			}

			return $lines;
		}

		/**
		 * Order tax rates, using the rate percent WooCommerce stored on each tax item.
		 *
		 * The parent derives the percent from tax / net amount when the stored percent is not used,
		 * which can be off on small amounts; the stored percent is exact.
		 *
		 * @return array
		 */
		private function get_order_tax_rates() {
			$tax_rates = $this->get_current_wc_order_tax_rates( $this->order );

			foreach ( $this->order->get_items( 'tax' ) as $tax_item ) {
				$rate_id = $tax_item->get_rate_id();
				if ( ! isset( $tax_rates[ $rate_id ] ) || ! is_callable( array( $tax_item, 'get_rate_percent' ) ) ) {
					continue;
				}
				$percent = $tax_item->get_rate_percent();
				if ( is_numeric( $percent ) ) {
					$tax_rates[ $rate_id ]['percentage'] = (float) $percent;
				}
			}

			return $tax_rates;
		}

		/**
		 * Net amount and VAT category of an order line per tax rate charged on it.
		 *
		 * One part when the line has one rate, or several rates on the full amount (combined rate). One part per
		 * rate when the amount is split between rates. Otherwise one part at the effective rate charged.
		 *
		 * @param WC_Order_Item $item      Order item.
		 * @param array         $tax_rates Order tax rates by rate ID.
		 * @param float         $amount    Line net amount.
		 * @return array[] Each: array( amount, category ).
		 */
		private function get_item_tax_parts( $item, $tax_rates, $amount ) {
			$taxes   = $item->get_taxes();
			$total   = isset( $taxes['total'] ) && is_array( $taxes['total'] ) ? $taxes['total'] : array();
			$charged = array();

			foreach ( $total as $rate_id => $tax ) {
				if ( is_numeric( $tax ) && isset( $tax_rates[ $rate_id ] ) ) {
					$charged[] = array(
						'rate' => $tax_rates[ $rate_id ],
						'tax'  => (float) $tax,
					);
				}
			}

			$taxed = array_values(
				array_filter(
					$charged,
					function ( $entry ) {
						return 0.0 !== round( $entry['tax'], 4 );
					}
				)
			);

			if ( count( $taxed ) < 2 ) {
				$rate = ! empty( $taxed ) ? $taxed[0]['rate'] : ( ! empty( $charged ) ? $charged[0]['rate'] : array() );
				return array(
					array(
						'amount'   => $amount,
						'category' => $this->normalize_tax_category( $rate ),
					),
				);
			}

			$tolerance = 0.01 * count( $taxed );
			$percents  = array();
			$on_full   = true;
			foreach ( $taxed as $entry ) {
				$percent    = isset( $entry['rate']['percentage'] ) ? (float) $entry['rate']['percentage'] : 0.0;
				$percents[] = $percent;
				if ( abs( $entry['tax'] - $amount * $percent / 100 ) > 0.01 ) {
					$on_full = false;
				}
			}

			if ( $on_full ) {
				return array(
					array(
						'amount'   => $amount,
						'category' => $this->normalize_tax_category( $this->combine_rates( $taxed[0]['rate'], array_sum( $percents ) ) ),
					),
				);
			}

			// Amount split between rates (e.g. shipping taxed in proportion to the items): the part each rate applied to.
			$parts = array();
			$sum   = 0.0;
			foreach ( $taxed as $index => $entry ) {
				if ( $percents[ $index ] <= 0 ) {
					$parts = array();
					break;
				}
				$part    = round( $entry['tax'] * 100 / $percents[ $index ], 2 );
				$sum    += $part;
				$parts[] = array(
					'amount'   => $part,
					'category' => $this->normalize_tax_category( $entry['rate'] ),
				);
			}
			if ( ! empty( $parts ) && abs( $sum - $amount ) <= $tolerance ) {
				$last                     = count( $parts ) - 1;
				$parts[ $last ]['amount'] = round( $parts[ $last ]['amount'] + $amount - $sum, 2 );
				return $parts;
			}

			// Anything else (e.g. compound tax): the effective rate charged on the line.
			$tax = array_sum( array_column( $taxed, 'tax' ) );
			return array(
				array(
					'amount'   => $amount,
					'category' => $this->normalize_tax_category( $this->combine_rates( $taxed[0]['rate'], 0.0 !== $amount ? round( $tax * 100 / $amount, 2 ) : 0.0 ) ),
				),
			);
		}

		/**
		 * One tax rate entry for several rates charged on the same amount.
		 *
		 * @param array $rate    First tax rate entry (category and scheme are kept).
		 * @param float $percent Combined percentage.
		 * @return array
		 */
		private function combine_rates( $rate, $percent ) {
			$rate['percentage'] = $percent;
			unset( $rate['name'] );
			return $rate;
		}

		/**
		 * Normalises a tax rate entry into an EN 16931 VAT category.
		 *
		 * @param array $rate Tax rate data (category, percentage, scheme).
		 * @return array { id, percent, scheme, reason }
		 */
		private function normalize_tax_category( $rate ) {
			if ( empty( $rate ) ) {
				$default = apply_filters(
					'wtpdf_ubl_xrechnung_untaxed_category',
					array(
						'id'     => 'E',
						'reason' => __( 'VAT exempt', 'print-invoices-packing-slip-labels-for-woocommerce' ),
					),
					$this->order
				);
				return array(
					'id'      => ! empty( $default['id'] ) ? strtoupper( $default['id'] ) : 'E',
					'percent' => 0.0,
					'scheme'  => 'VAT',
					'reason'  => isset( $default['reason'] ) ? $default['reason'] : '',
				);
			}

			$percent = isset( $rate['percentage'] ) ? round( (float) $rate['percentage'], 2 ) : 0.0;
			$id      = ! empty( $rate['category'] ) ? strtoupper( $rate['category'] ) : ( $percent > 0 ? 'S' : 'Z' );
			$scheme  = ! empty( $rate['scheme'] ) ? strtoupper( $rate['scheme'] ) : 'VAT';

			return array(
				'id'      => $id,
				'percent' => $percent,
				'scheme'  => $scheme,
				'reason'  => isset( $rate['name'] ) ? $rate['name'] : '',
			);
		}

		/**
		 * Groups lines by VAT category and rate.
		 *
		 * Tax per group is taxable amount x rate (BR-S-09 and siblings), so it can differ by a cent from the
		 * per-line rounded tax WooCommerce charged on orders with many lines.
		 *
		 * @return array
		 */
		private function get_tax_breakdown() {
			$groups = array();
			foreach ( $this->lines as $line ) {
				$category = $line['category'];
				$key      = $category['id'] . '|' . $category['percent'];
				if ( ! isset( $groups[ $key ] ) ) {
					$groups[ $key ] = array(
						'category' => $category,
						'taxable'  => 0.0,
						'tax'      => 0.0,
					);
				}
				$groups[ $key ]['taxable'] += $line['amount'];
			}

			foreach ( $groups as $key => $group ) {
				$groups[ $key ]['taxable'] = round( $group['taxable'], 2 );
				$groups[ $key ]['tax']     = round( $groups[ $key ]['taxable'] * $group['category']['percent'] / 100, 2 );
			}

			return array_values( $groups );
		}

		/**
		 * Children of a TaxCategory / ClassifiedTaxCategory node.
		 *
		 * @param array $category       Normalised category.
		 * @param bool  $with_exemption Whether to add the exemption reason (document level only, BR-E-10 etc.).
		 * @return array
		 */
		private function tax_category_children( $category, $with_exemption ) {
			$children = array( $this->text_node( 'cbc:ID', $category['id'] ) );

			// Category O ("not subject to VAT") must not carry a rate (BR-O-05/BR-O-09).
			if ( 'O' !== $category['id'] ) {
				$children[] = $this->text_node( 'cbc:Percent', $category['percent'] );
			}

			// These categories need an exemption reason (BR-E-10, BR-AE-10, BR-IC-10, BR-G-10, BR-O-10); fall back to a default text when the rate has no label.
			$default_reasons = array(
				'E'  => __( 'VAT exempt', 'print-invoices-packing-slip-labels-for-woocommerce' ),
				'AE' => __( 'Reverse charge', 'print-invoices-packing-slip-labels-for-woocommerce' ),
				'K'  => __( 'Intra-community supply', 'print-invoices-packing-slip-labels-for-woocommerce' ),
				'G'  => __( 'Export outside the EU', 'print-invoices-packing-slip-labels-for-woocommerce' ),
				'O'  => __( 'Not subject to VAT', 'print-invoices-packing-slip-labels-for-woocommerce' ),
			);
			if ( $with_exemption && isset( $default_reasons[ $category['id'] ] ) ) {
				$reason     = '' !== trim( (string) $category['reason'] ) ? $category['reason'] : $default_reasons[ $category['id'] ];
				$children[] = $this->text_node( 'cbc:TaxExemptionReason', $reason );
			}

			$children[] = $this->node( 'cac:TaxScheme', array( $this->text_node( 'cbc:ID', $category['scheme'] ) ) );
			return $children;
		}

		/**
		 * Whether a tax number is a VAT ID, i.e. starts with a two-letter country prefix (BR-CO-9).
		 *
		 * @param string $number Tax number.
		 * @return bool
		 */
		private function is_vat_id( $number ) {
			return 1 === preg_match( '/^[A-Z]{2}[A-Z0-9]/i', (string) $number );
		}

		/**
		 * Buyer VAT ID from the order meta, read from the same keys as the PDF invoice.
		 *
		 * @return string
		 */
		private function get_buyer_vat_id() {
			$vat_fields = apply_filters( 'wt_pklist_add_additional_vat_meta', array( 'vat', 'vat_number', 'eu_vat_number' ), 'invoice' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Established plugin hook prefix.
			foreach ( (array) $vat_fields as $vat_field ) {
				$vat = \Wt_Pklist_Common::get_order_meta( $this->order, '_billing_' . $vat_field, true );
				if ( empty( $vat ) ) {
					$vat = \Wt_Pklist_Common::get_order_meta( $this->order, $vat_field, true );
				}
				if ( ! empty( $vat ) && is_string( $vat ) ) {
					return trim( $vat );
				}
			}
			return '';
		}

		/**
		 * First configured IBAN account of the Direct Bank Transfer gateway.
		 *
		 * @return array
		 */
		private function get_bacs_account() {
			$accounts = get_option( 'woocommerce_bacs_accounts', array() );
			if ( ! is_array( $accounts ) ) {
				return array();
			}
			foreach ( $accounts as $account ) {
				if ( ! empty( $account['iban'] ) ) {
					return $account;
				}
			}
			return array();
		}

		/**
		 * Reads a general plugin option, falling back when it is empty.
		 *
		 * @param string $key      Option key.
		 * @param mixed  $fallback Fallback value.
		 * @return string
		 */
		private function get_general_option( $key, $fallback = '' ) {
			$value = \Wf_Woocommerce_Packing_List::get_option( $key );
			if ( ! is_string( $value ) || '' === trim( $value ) ) {
				$value = is_string( $fallback ) ? $fallback : '';
			}
			return trim( $value );
		}

		/**
		 * Recursively drops nodes whose value is empty.
		 *
		 * @param mixed $node Node or list of nodes.
		 * @return mixed Cleaned node, or null when nothing is left.
		 */
		private function remove_empty_nodes( $node ) {
			if ( ! is_array( $node ) ) {
				return $node;
			}

			if ( isset( $node['name'] ) ) {
				if ( ! array_key_exists( 'value', $node ) ) {
					return null;
				}
				if ( is_array( $node['value'] ) ) {
					$node['value'] = $this->remove_empty_nodes( $node['value'] );
					return empty( $node['value'] ) ? null : $node;
				}
				return ( null === $node['value'] || '' === trim( (string) $node['value'] ) ) ? null : $node;
			}

			$cleaned = array();
			foreach ( $node as $child ) {
				$child = $this->remove_empty_nodes( $child );
				if ( null !== $child && array() !== $child ) {
					$cleaned[] = $child;
				}
			}
			return $cleaned;
		}

		/**
		 * Wraps a node as an enabled element entry.
		 *
		 * @param array $value Node.
		 * @return array
		 */
		private function element( $value ) {
			return array(
				'enabled'   => true,
				'value_arr' => $value,
			);
		}

		/**
		 * Node with child nodes.
		 *
		 * @param string $name     Element name.
		 * @param array  $children Child nodes.
		 * @return array
		 */
		private function node( $name, $children ) {
			return array(
				'name'  => $name,
				'value' => $children,
			);
		}

		/**
		 * Node with a scalar value.
		 *
		 * @param string $name       Element name.
		 * @param mixed  $value      Value.
		 * @param array  $attributes Attributes.
		 * @return array
		 */
		private function text_node( $name, $value, $attributes = array() ) {
			$node = array(
				'name'  => $name,
				'value' => is_string( $value ) ? trim( $value ) : $value,
			);
			if ( ! empty( $attributes ) ) {
				$node['attributes'] = $attributes;
			}
			return $node;
		}

		/**
		 * Monetary amount node, always with two decimals.
		 *
		 * @param string $name     Element name.
		 * @param float  $amount   Amount.
		 * @param string $currency Currency code.
		 * @return array
		 */
		private function amount_node( $name, $amount, $currency ) {
			return $this->text_node( $name, number_format( (float) $amount, 2, '.', '' ), array( 'currencyID' => $currency ) );
		}
	}
}
