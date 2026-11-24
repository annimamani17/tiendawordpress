/**
 * GST invoice customizer: "Code from" options of the HSN/SAC column.
 *
 * The column keeps its source in the template as data-hsn-source / data-hsn-key (two customizer inputs write them).
 * "Find automatically" lists the codes on the products of the most recent orders (loaded only when the template has
 * the HSN/SAC column and that source is selected) and explains how to add codes when none are found. "Product meta key" and "Product attribute" only need the key typed in.
 */
(function ($) {
	'use strict';

	const cfg = window.wt_pklist_gst_hsn;
	if (!cfg) {
		return;
	}
	const i18n = cfg.i18n;
	let scan = null;
	let loading = false;
	let failed = false;
	let next_key = '';

	/* Replace %1$s, %2$s... then %s with the given values. */
	const fmt = function (str) {
		const args = Array.prototype.slice.call(arguments, 1);
		let out = str;
		args.forEach(function (arg, i) {
			out = out.split('%' + (i + 1) + '$s').join(arg);
		});
		args.forEach(function (arg) {
			out = out.replace('%s', arg);
		});
		return out;
	};

	const products = function (count) {
		return fmt(1 === count ? i18n.product : i18n.products, count);
	};

	const source_field = function () {
		return $('.wf_side_panel select[data-prop="attr-data-hsn-source"]');
	};
	const key_field = function () {
		return $('.wf_side_panel input[data-prop="attr-data-hsn-key"]');
	};
	const add_styles = function () {
		if ($('#wt_pklist_hsn_css').length) {
			return;
		}
		$('<style id="wt_pklist_hsn_css">' +
			'.wt_pklist_hsn_box{margin-top:6px;padding:10px 12px;background:#f6f7f7;border:1px solid #dcdcde;border-radius:4px;font-size:12px;line-height:18px;}' +
			'.wt_pklist_hsn_box.wt_pklist_hsn_none{background:#fcf9e8;border-color:#f0e2a8;}' +
			'.wt_pklist_hsn_box strong{display:block;margin-bottom:4px;}' +
			'.wt_pklist_hsn_box ul{margin:4px 0 6px 16px;list-style:disc;}' +
			'.wt_pklist_hsn_box li{margin:0;}' +
			'.wt_pklist_hsn_found{display:flex;justify-content:space-between;gap:8px;margin:0 -6px;padding:2px 6px;border-radius:3px;color:inherit;text-decoration:none;cursor:pointer;}' +
			'.wt_pklist_hsn_found:hover,.wt_pklist_hsn_found:focus{background:#e5f0fa;color:#2271b1;}' +
			'.wt_pklist_hsn_hint{margin-top:4px;color:#646970;}' +
			'.wt_pklist_hsn_found span:first-child{min-width:0;overflow-wrap:anywhere;}' +
			'.wt_pklist_hsn_found span:last-child{flex:0 0 auto;white-space:nowrap;}' +
			/* the key group is taller than its spacer; start the next column's row below it */
			'.wf_side_panel_frmgrp.wt_pklist_hsn_key + .wf_side_panel_frmgrp{clear:both;}' +
			'.wt_pklist_hsn_actions{margin-top:6px;display:flex;gap:12px;align-items:center;}' +
			'</style>').appendTo('head');
	};

	/* The "Find automatically" info box lives inside the key input's form group. */
	const ui = function () {
		const group = key_field().closest('.wf_side_panel_frmgrp');
		if (!group.length) {
			return null;
		}
		if (!group.find('.wt_pklist_hsn_box').length) {
			add_styles();
			key_field().after($('<div>', { 'class': 'wt_pklist_hsn_box' }));
		}
		return {
			group: group,
			label: group.children('label'),
			input: key_field(),
			box: group.find('.wt_pklist_hsn_box')
		};
	};

	const empty_state = function (box) {
		box.addClass('wt_pklist_hsn_none').append(
			$('<strong>').text(scan.orders ? i18n.none_title : i18n.no_orders),
			$('<div>').text(i18n.none_text),
			$('<ul>').append(
				$('<li>').text(i18n.none_attr),
				$('<li>').text(i18n.none_plugin),
				$('<li>').text(i18n.none_csv)
			),
			$('<div>', { 'class': 'wt_pklist_hsn_actions' }).append(
				$('<a>', { href: '#', 'class': 'button button-small wt_pklist_hsn_rescan', text: i18n.scan_again }),
				$('<a>', { href: cfg.help, target: '_blank', rel: 'noopener', text: i18n.how_to })
			)
		);
	};

	const render = function () {
		const source_elm = source_field();
		const parts = ui();
		if (!source_elm.length || !parts || !$('.wf_customize_container .wfte_product_table_head_hsn_code').length) {
			return; /* the template has no HSN/SAC column: no scan, nothing to show */
		}
		const source = source_elm.val() || 'auto';

		if ('auto' !== source) {
			/* Product meta key / Product attribute: just the key input. */
			parts.label.text('attr' === source ? i18n.attr_label : i18n.meta_label).show();
			parts.input.attr('placeholder', 'attr' === source ? i18n.attr_placeholder : i18n.meta_placeholder).show();
			parts.box.hide();
			return;
		}

		parts.label.hide();
		parts.input.hide();
		/* Rebuild the list only when it changed, so a background request finishing mid-click does not swallow the click. */
		const state = JSON.stringify([scan, failed]);
		if (scan && parts.box.is(':visible') && state === parts.box.data('state')) {
			return;
		}
		parts.box.removeClass('wt_pklist_hsn_none').empty().show().data('state', state);
		if (!scan) {
			parts.box.text(failed ? i18n.scan_failed : i18n.scanning);
			if (failed) {
				parts.box.append(' ', $('<a>', { href: '#', 'class': 'wt_pklist_hsn_rescan', text: i18n.scan_again }));
			} else {
				load();
			}
			return;
		}
		if (0 === scan.meta.length && 0 === scan.attrs.length) {
			empty_state(parts.box);
		} else {
			/* Each row picks that meta key or attribute as the source. */
			const row = function (source, key, text, count) {
				return $('<a>', { href: '#', 'class': 'wt_pklist_hsn_found', 'data-source': source, 'data-key': key }).append(
					$('<span>').text(text),
					$('<span>').text(products(count))
				);
			};
			scan.meta.forEach(function (item) {
				parts.box.append(row('meta', item.key, fmt(i18n.found_meta, item.key), item.count));
			});
			scan.attrs.forEach(function (item) {
				parts.box.append(row('attr', item.key, fmt(i18n.found_attr, item.label), item.count));
			});
			parts.box.append($('<div>', { 'class': 'wt_pklist_hsn_hint' }).text(i18n.found_hint));
		}
	};

	/* Write the key through the customizer's own input handler, so it lands in the template like any other option. */
	const set_key = function (value) {
		key_field().val(value).trigger('keyup');
	};

	const load = function () {
		if (loading) {
			return;
		}
		loading = true;
		failed = false;
		$.ajax({
			url: cfg.ajaxurl,
			type: 'POST',
			dataType: 'json',
			data: { action: 'wt_pklist_hsn_scan', _wpnonce: cfg.nonce },
			success: function (res) {
				if (res && res.success && res.data) {
					scan = res.data;
				} else {
					failed = true;
				}
			},
			error: function () {
				failed = true;
			},
			complete: function () {
				loading = false;
				render();
			}
		});
	};

	/*
	 * A meta key is not an attribute name: start with an empty key (or the picked one) when the source changes.
	 * Wait for the dropdown to finish closing before touching the panel; changing focus or layout while the native
	 * menu closes can leave the page ignoring clicks.
	 */
	$(document).on('change', '.wf_side_panel select[data-prop="attr-data-hsn-source"]', function () {
		const key = next_key;
		next_key = '';
		setTimeout(function () {
			set_key(key);
			render();
		}, 0);
	});

	$(document).on('click', '.wt_pklist_hsn_found', function (e) {
		e.preventDefault();
		const pick = $(this);
		next_key = pick.attr('data-key');
		source_field().val(pick.attr('data-source')).trigger('change');
	});

	$(document).on('click', '.wt_pklist_hsn_rescan', function (e) {
		e.preventDefault();
		scan = null;
		loading = false;
		const parts = ui();
		if (parts) {
			parts.box.removeClass('wt_pklist_hsn_none').removeData('state').text(i18n.scanning);
		}
		load();
	});

	/* The customizer reloads the panel values after each template load or preview refresh. */
	$(document).ajaxComplete(function () {
		setTimeout(render, 0);
	});
	$(render);
})(jQuery);
