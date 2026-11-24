<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>
<style type="text/css">
body, html{font-family:"Helvetica Neue", Roboto, Arial, "Droid Sans", sans-serif;line-height: 1.45;}
.clearfix::after{ display:block; clear:both; content:""; }

.wfte_invoice-main{ color:#2e2e2e; font-size:12px; font-weight:400; box-sizing:border-box; width:auto; height:auto; }
.wfte_invoice-main *{ box-sizing:border-box;}
.template_footer{color:#2e2e2e; font-size:12px; font-weight:400; box-sizing:border-box; padding:15px 30px; height:auto;}
.template_footer *{ box-sizing:border-box;}

.wfte_row{ width:100%; display:block; }
.wfte_col-1{ width:100%; display:block;}
.wfte_align_center{ text-align:center; }
.wfte_align_right{ text-align:right; }
.wfte_text_left{ text-align:left; }
.wfte_text_right{ text-align:right; }
.wfte_text_center{ text-align:center; }

.wfte_layout{ width:100%; border-collapse:collapse; table-layout:fixed; }
.wfte_layout td{ padding:0px; vertical-align:top; border:none; font-size:12px; }
.wfte_seller_name{ font-size:16px; }
.wfte_section{ margin-top:14px; }
.wfte_hsn_summary, .wfte_bank_details, .wfte_keep{ page-break-inside:avoid; }
.wfte_hr{ height:1px; font-size:0px; padding:0px; background:#dddee0; margin-top:14px; margin-bottom:14px; }

.wfte_doc_title{ font-size:22px; font-weight:bold; color:#2e2e2e; line-height:1; margin:0px; padding:0px; }
.wfte_header_row{ margin-top:10px; }
.wfte_seller_name{ font-size:16px; font-weight:bold; margin-bottom:4px; }
.wfte_company_logo_img_box{ text-align:right; margin:0px 0px 8px; padding:0px; }
.wfte_company_logo{ margin:0px; padding:0px; }
.wfte_company_logo_img{ width:150px; max-width:100%; vertical-align:top; }
.wfte_invoice_data div span:first-child, .wfte_extra_fields span:first-child{ font-weight:bold; }
.wfte_invoice_data{ font-size:12px; }
.wfte_address-field-header{ font-weight:bold; font-size:12px; margin-bottom:3px; }
.wfte_section_label{ font-weight:bold; margin-bottom:3px; }

.wfte_received_seal{ position:absolute; z-index:10; margin-top:90px; margin-left:340px; width:130px; border-radius:5px; font-size:22px; height:40px; line-height:28px; border:solid 5px #00ccc5; color:#00ccc5; font-weight:900; text-align:center; transform:rotate(-45deg); opacity:.5; }

.wfte_product_table{ width:100%; border-collapse:collapse; margin:0px; border:solid 1px #dcdfe4; }
.wfte_product_table_head th{ white-space:nowrap; padding:7px 6px; font-size:12px; font-weight:bold; color:#2e2e2e; text-align:left; background-color:#f4f4f4; border:solid 1px #dcdfe4; }
.wfte_product_table_head th.wfte_text_center{ text-align:center; }
.wfte_product_table_head th.wfte_text_right{ text-align:right; }
.wfte_product_table_head_product{ width:34%; }
.wfte_product_table_head_serial_no{ width:6%; white-space:nowrap; }
.wfte_product_table_body td{ font-size:12px; padding:7px 6px; border:solid 1px #dcdfe4; vertical-align:top; }
.wfte_product_table_body .product_td b{ font-weight:normal; }

.wfte_words_cell{ width:52%; padding-top:5px; }
.wfte_summary_cell{ width:48%; }
.wfte_payment_summary_table{ width:100%; border-collapse:collapse; }
.wfte_payment_summary_table_body td{ font-size:12px; padding:5px 6px; border:none; }
.wfte_payment_summary_table_body td.wfte_right_column{ text-align:right; }
.wfte_product_table_payment_total td{ font-size:13px; border-top:solid 1px #dddee0; padding-top:10px; }
.wfte_product_table_payment_total td.wfte_right_column{ font-weight:bold; }

/* Size and colour sit on the element, so the values set in the customizer reach the table. */
.wfte_hsn_summary, .wfte_tax_rate_summary{ font-size:11px; }
.wfte_gst_summary_table{ width:100%; border-collapse:collapse; border:solid 1px #dcdfe4; }
.wfte_gst_summary_table th{ white-space:nowrap; font-weight:bold; padding:6px; background-color:#f4f4f4; border:solid 1px #dcdfe4; }
.wfte_gst_summary_table td{ padding:5px 6px; border:solid 1px #dcdfe4; }
.wfte_gst_summary_table tfoot td{ font-weight:bold; }

.wfte_terms_conditions_label, .wfte_order_notes_label, .wfte_bank_details_label{ font-weight:bold; margin-bottom:4px; }
.wfte_bank_hr{ height:1px; font-size:0px; line-height:0px; background-color:#dcdfe4; margin-top:8px; }
.wfte_layout tr.wfte_bank_details td{ font-size:inherit; }
.wfte_bank_line{ line-height:1.6; }
.wfte_bank_line_label{ font-weight:bold; }
.wfte_codes .wfte_qrcode, .wfte_codes .wfte_barcode{ display:inline-block; vertical-align:middle; margin-right:10px; }
.wfte_footer{ width:100%; height:auto; padding:5px 0px; margin-top:5px; font-size:12px; }

/* for mPdf */
.wfte_invoice_data{ border:solid 0px #fff; }
.wfte_invoice_data td, .wfte_extra_fields td{ font-size:12px; padding:0px; }
.wfte_invoice_data tr td:nth-child(1), .wfte_extra_fields tr td:nth-child(1){ font-weight:bold; }
table.wfte_product_table th, table.wfte_product_table td{ line-height:1.45; }
</style>
<div class="wfte_rtl_main wfte_invoice-main wfte_invoice_pro_main" style="padding-top:15px; padding-right:30px; padding-bottom:30px; padding-left:30px; margin-top: 0px; margin-bottom:0px; margin-left:0px; margin-right:0px;">
    <div class="wfte_align_center">
        <div class="wfte_doc_title wfte_template_element" data-hover-id="doc_title">__[Tax Invoice]__</div>
    </div>
    <table class="wfte_layout wfte_header_row">
        <tr>
            <td style="width:60%;">
                <div class="wfte_seller_name">[wfte_company_name]</div>
                <div class="wfte_from_address wfte_template_element" data-hover-id="from_address">
                    <div class="wfte_address-field-header wfte_from_address_label wfte_hidden">__[]__</div>
                    <div class="wfte_from_address_val">
                        [wfte_from_address]
                    </div>
                </div>
                <div class="wfte_invoice_data">
                    <div class="wfte_shop_vat_number wfte_template_element" data-hover-id="shop_vat_number">
                        <span class="wfte_shop_vat_number_label">__[GSTIN:]__ </span>
                        <span class="wfte_shop_vat_number_val">[wfte_shop_vat_number]</span>
                    </div>
                    <div class="wfte_shop_reg_number wfte_template_element" data-hover-id="shop_reg_number">
                        <span class="wfte_shop_reg_number_label">__[PAN:]__ </span>
                        <span class="wfte_shop_reg_number_val">[wfte_shop_reg_number]</span>
                    </div>
                </div>
            </td>
            <td style="width:40%;" class="wfte_align_right">
                <div class="wfte_company_logo wfte_template_element" data-hover-id="company_logo">
                    <div class="wfte_company_logo_img_box">
                        <img src="[wfte_company_logo_url]" class="wfte_company_logo_img">
                    </div>
                    <div class="wfte_company_name wfte_hidden"> [wfte_company_name]</div>
                    <div class="wfte_company_logo_extra_details">__[]__</div>
                </div>
                <div class="wfte_invoice_data">
                    <div class="wfte_invoice_number wfte_template_element" data-hover-id="invoice_number">
                        <span class="wfte_invoice_number_label">__[Tax Invoice No.:]__ </span>
                        <span class="wfte_invoice_number_val">[wfte_invoice_number]</span>
                    </div>
                    <div class="wfte_invoice_date wfte_template_element" data-invoice_date-format="d - M - Y" data-hover-id="invoice_date">
                        <span class="wfte_invoice_date_label">__[Date:]__ </span>
                        <span class="wfte_invoice_date_val">[wfte_invoice_date]</span>
                    </div>
                    <div class="wfte_order_number wfte_template_element" data-hover-id="order_number">
                        <span class="wfte_order_number_label">__[Order No.:]__ </span>
                        <span class="wfte_order_number_val">[wfte_order_number]</span>
                    </div>
                    <div class="wfte_order_date wfte_template_element" data-order_date-format="d - M - Y" data-hover-id="order_date">
                        <span class="wfte_order_date_label">__[Order Date:]__ </span>
                        <span class="wfte_order_date_val">[wfte_order_date]</span>
                    </div>
                </div>
            </td>
        </tr>
    </table>
    <div class="wfte_hr"></div>
    <table class="wfte_layout">
        <tr>
            <td style="width:50%; padding-right:15px;">
                <div class="wfte_billing_address wfte_template_element" data-hover-id="billing_address">
                    <div class="wfte_address-field-header wfte_billing_address_label">__[Buyer (Bill To):]__</div>
                    <div class="wfte_billing_address_val">
                        [wfte_billing_address]
                    </div>
                </div>
                <div class="wfte_invoice_data">
                    <div class="wfte_vat_number wfte_template_element" data-hover-id="vat_number">
                        <span class="wfte_vat_number_label">__[GSTIN:]__ </span>
                        <span class="wfte_vat_number_val">[wfte_vat_number]</span>
                    </div>
                    <div class="wfte_email wfte_template_element" data-hover-id="email">
                        <span class="wfte_email_label">__[Email:]__ </span>
                        <span class="wfte_email_val">[wfte_email]</span>
                    </div>
                    <div class="wfte_tel wfte_template_element" data-hover-id="tel">
                        <span class="wfte_tel_label">__[Contact No.:]__ </span>
                        <span class="wfte_tel_val">[wfte_tel]</span>
                    </div>
                    <div class="wfte_ssn_number wfte_template_element wfte_hidden" data-hover-id="ssn_number">
                        <span class="wfte_ssn_number_label">__[SSN:]__ </span>
                        <span class="wfte_ssn_number_val">[wfte_ssn_number]</span>
                    </div>
                </div>
            </td>
            <td style="width:50%;">
                <div class="wfte_shipping_address wfte_template_element" data-hover-id="shipping_address">
                    <div class="wfte_address-field-header wfte_shipping_address_label">__[Consignee (Ship To):]__</div>
                    <div class="wfte_shipping_address_val">
                        [wfte_shipping_address]
                    </div>
                </div>
                <div class="wfte_invoice_data">
                    <div class="wfte_tracking_number wfte_template_element wfte_hidden" data-hover-id="tracking_number">
                        <span class="wfte_tracking_number_label">__[Tracking No.:]__ </span>
                        <span class="wfte_tracking_number_val">[wfte_tracking_number]</span>
                    </div>
                    <div class="wfte_order_item_meta">[wfte_order_item_meta]</div>
                    [wfte_extra_fields]
                </div>
            </td>
        </tr>
    </table>
    <div class="wfte_invoice_data wfte_section">
        <div class="wfte_place_of_supply wfte_template_element" data-hover-id="place_of_supply">
            <span class="wfte_place_of_supply_label">__[Place of Supply:]__ </span>
            <span class="wfte_place_of_supply_val">[wfte_place_of_supply]</span>
        </div>
    </div>
    <div class="wfte_received_seal wfte_template_element wfte_hidden" data-hover-id="received_seal"><span class="wfte_received_seal_text">__[RECEIVED]__</span>[wfte_received_seal_extra_text]</div>
    <div class="wfte_section">
        [wfte_product_table_start]
        <table class="wfte_product_table wfte_side_padding_table wfte_template_element" data-hover-id="product_table">
            <thead class="wfte_product_table_head wfte_table_head_color wfte_product_table_head_bg">
                <tr>
                    <th class="wfte_product_table_head_serial_no wfte_product_table_head_bg wfte_table_head_color wfte_text_center" col-type="serial_no" data-col-value-font-size="12px" data-col-value-font-weight="normal">__[Sr. No.]__</th>
                    <th class="wfte_product_table_head_image wfte_product_table_head_bg wfte_table_head_color" col-type="-image" data-img-width="30px" data-img-height="30px">__[Image]__</th>
                    <th class="wfte_product_table_head_sku wfte_product_table_head_bg wfte_table_head_color" col-type="-sku" data-col-value-font-size="12px" data-col-value-font-weight="normal">__[SKU]__</th>
                    <th class="wfte_product_table_head_product wfte_product_table_head_bg wfte_table_head_color" col-type="product" data-col-value-font-size="12px" data-col-value-font-weight="normal">__[Product]__</th>
                    <th class="wfte_product_table_head_product_description wfte_product_table_head_bg wfte_table_head_color" col-type="-product_description" data-col-value-font-size="12px" data-col-value-font-weight="normal">__[Description]__</th>
                    <th class="wfte_product_table_head_hsn_code wfte_product_table_head_bg wfte_table_head_color" col-type="hsn_code" data-hsn-source="auto" data-hsn-key="" data-col-value-font-size="12px" data-col-value-font-weight="normal">__[HSN/SAC]__</th>
                    <th class="wfte_product_table_head_quantity wfte_product_table_head_bg wfte_table_head_color wfte_text_center" col-type="quantity" data-col-value-font-size="12px" data-col-value-font-weight="normal">__[Qty]__</th>
                    <th class="wfte_product_table_head_price wfte_product_table_head_bg wfte_table_head_color wfte_text_right" col-type="price" data-col-value-font-size="12px" data-col-value-font-weight="normal">__[Rate]__</th>
                    <th class="wfte_product_table_head_tax_items wfte_product_table_head_bg wfte_table_head_color" col-type="-tax_items" data-ind-tax-display-option="amount" data-col-value-font-size="12px" data-col-value-font-weight="normal">__[Tax]__</th>
                    <th class="wfte_product_table_head_taxable_amount wfte_product_table_head_bg wfte_table_head_color wfte_text_right" col-type="taxable_amount" data-col-value-font-size="12px" data-col-value-font-weight="normal">__[Taxable Amt.]__</th>
                    <th class="wfte_product_table_head_line_amount wfte_product_table_head_bg wfte_table_head_color wfte_text_right" col-type="-line_amount" data-col-value-font-size="12px" data-col-value-font-weight="normal">__[Amount]__</th>
                </tr>
            </thead>
            <tbody class="wfte_product_table_body wfte_table_body_color">
            </tbody>
        </table>
        [wfte_product_table_end]
    </div>
    <table class="wfte_layout wfte_section">
        <tr>
            <td class="wfte_words_cell">
            </td>
            <td class="wfte_summary_cell">
                <table class="wfte_payment_summary_table wfte_side_padding_table">
                    <colgroup><col style="width:0%;" /><col style="width:55%;" /><col style="width:45%;" /></colgroup>
                    <tbody class="wfte_payment_summary_table_body wfte_table_body_color">
                        <tr class="wfte_payment_summary_table_row wfte_product_table_subtotal" data-row-type="wfte_product_table_subtotal">
                            <td class="wfte_left_column"></td>
                            <td class="wfte_product_table_subtotal_label">__[Taxable amount]__</td>
                            <td class="wfte_right_column">[wfte_product_table_subtotal]</td>
                        </tr>
                        <tr class="wfte_payment_summary_table_row wfte_product_table_shipping" data-row-type="wfte_product_table_shipping">
                            <td class="wfte_left_column"></td>
                            <td class="wfte_product_table_shipping_label">__[Shipping]__</td>
                            <td class="wfte_right_column">[wfte_product_table_shipping]</td>
                        </tr>
                        <tr class="wfte_payment_summary_table_row wfte_product_table_cart_discount" data-row-type="wfte_product_table_cart_discount">
                            <td class="wfte_left_column"></td>
                            <td class="wfte_product_table_cart_discount_label">__[Cart discount]__</td>
                            <td class="wfte_right_column">[wfte_product_table_cart_discount]</td>
                        </tr>
                        <tr class="wfte_payment_summary_table_row wfte_product_table_order_discount" data-row-type="wfte_product_table_order_discount">
                            <td class="wfte_left_column"></td>
                            <td class="wfte_product_table_order_discount_label">__[Order discount]__</td>
                            <td class="wfte_right_column">[wfte_product_table_order_discount]</td>
                        </tr>
                        <tr data-row-type="wfte_tax_items" class="wfte_payment_summary_table_row wfte_product_table_tax_item" data-ind-tax-type="tot-combined-tax">
                            <td class="wfte_left_column"></td>
                            <td class="wfte_product_table_tax_item_label">[wfte_product_table_tax_item_label]</td>
                            <td class="wfte_right_column">[wfte_product_table_tax_item]</td>
                        </tr>
                        <tr class="wfte_payment_summary_table_row wfte_product_table_total_tax wfte_hidden" data-row-type="wfte_product_table_total_tax" data-tot-tax-type="tot-combined-tax">
                            <td class="wfte_left_column"></td>
                            <td class="wfte_product_table_total_tax_label">__[Total GST]__</td>
                            <td class="wfte_right_column">[wfte_product_table_total_tax]</td>
                        </tr>
                        <tr class="wfte_payment_summary_table_row wfte_product_table_fee" data-row-type="wfte_product_table_fee">
                            <td class="wfte_left_column"></td>
                            <td class="wfte_product_table_fee_label">__[Fee]__</td>
                            <td class="wfte_right_column">[wfte_product_table_fee]</td>
                        </tr>
                        <tr class="wfte_payment_summary_table_row wfte_product_table_coupon wfte_hidden" data-row-type="wfte_product_table_coupon">
                            <td class="wfte_left_column"></td>
                            <td class="wfte_product_table_coupon_label">__[Coupon used]__</td>
                            <td class="wfte_right_column">[wfte_product_table_coupon]</td>
                        </tr>
                        <tr class="wfte_payment_summary_table_row wfte_product_table_payment_total" data-row-type="wfte_product_table_payment_total">
                            <td class="wfte_left_column"></td>
                            <td class="wfte_product_table_payment_total_label">__[Invoice total]__</td>
                            <td class="wfte_product_table_payment_total_val wfte_right_column">[wfte_product_table_payment_total]<span class="wfte_product_table_incl_tax wfte_hidden"> (<span class="wfte_product_table_incl_tax_label">__[incl. tax]__</span> [wfte_product_table_total_tax])</span></td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>
    </table>
    <div class="wfte_section wfte_hsn_summary wfte_hsn_summary_no_qty wfte_hsn_summary_no_rate wfte_hsn_summary_no_total wfte_template_element" data-hover-id="hsn_summary">
        <div class="wfte_hsn_summary_val">[wfte_hsn_summary]</div>
    </div>
    <table class="wfte_layout wfte_section wfte_keep">
        <tr>
            <td style="width:100%;">
                <div class="wfte_terms_conditions wfte_template_element" data-hover-id="terms_conditions">
                    <div class="wfte_terms_conditions_label">__[Terms &amp; Conditions]__</div>
                    <div class="wfte_terms_conditions_val">[wfte_terms_conditions]</div>
                </div>
            </td>
        </tr>
        <tr class="wfte_bank_details wfte_template_element" data-hover-id="bank_details">
            <td style="padding-top:14px;">
                <div class="wfte_bank_details_label">__[Bank Details]__</div>
                <div class="wfte_bank_details_val">[wfte_bank_details]</div>
                <div class="wfte_bank_hr">&nbsp;</div>
            </td>
        </tr>
    </table>
    <div class="wfte_row clearfix wfte_section">
        <div class="wfte_col-1">
            <div class="wfte_invoice_data">
                <div class="wfte_product_table_payment_method wfte_template_element" data-hover-id="payment_method">
                    <span class="wfte_product_table_payment_method_label">__[Payment Method:]__ </span>
                    <span class="wfte_product_table_payment_method_val">[wfte_product_table_payment_method]</span>
                </div>
                <div class="wfte_customer_note wfte_template_element" data-hover-id="customer_note">
                    <span class="wfte_customer_note_label">__[Customer Note:]__ </span>
                    <span class="wfte_customer_note_val">[wfte_customer_note]</span>
                </div>
            </div>
            <div class="wfte_codes">
                <div class="wfte_qrcode wfte_template_element wfte_hidden" data-hover-id="qrcode">
                    <img src="[wfte_qrcode_url]" class="wfte_img_qrcode">
                </div>
                <div class="wfte_barcode wfte_template_element wfte_hidden" data-hover-id="barcode">
                    <img src="[wfte_barcode_url]" class="wfte_img_barcode">
                </div>
            </div>
        </div>
    </div>
    <div class="clearfix"></div>
</div>
<div class="template_footer clearfix">
    <div class="wfte_col-1">
        <div class="wfte_footer clearfix wfte_template_element" data-hover-id="footer">
            [wfte_footer]
        </div>
    </div>
</div>
