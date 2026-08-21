<script>
(function ($) {
	function clampLsSelect2Search($el) {
		var $container = $el.next('.select2-container');
		$container.find('.select2-search--inline .select2-search__field').attr(
			'style',
			'width:0!important;min-width:0!important;max-width:0!important;height:0!important;margin:0!important;padding:0!important;border:0!important;'
		);
		$container.css({ maxWidth: '100%', overflow: 'hidden' });
	}

	function wireLsMultiDropdownSearch($el) {
		$el.off('select2:open.lsDdSearch select2:close.lsDdSearch select2:select.lsDdSearch select2:unselect.lsDdSearch')
			.on('select2:open.lsDdSearch', function () {
				clampLsSelect2Search($el);
				var $dropdown = $('.select2-container--open .select2-dropdown');
				var $existing = $dropdown.find('.ls-dd-search');
				if ($existing.length) {
					$existing.find('input').val('').trigger('focus');
					return;
				}
				var $box = $('<div class="ls-dd-search"><i class="mdi mdi-magnify" aria-hidden="true"></i><input type="search" placeholder="Search…" autocomplete="off"></div>');
				$dropdown.prepend($box);
				var $input = $box.find('input');
				$input.on('input keyup', function () {
					var q = $input.val();
					var $hidden = $el.data('select2') && $el.data('select2').$selection
						? $el.data('select2').$selection.find('.select2-search__field')
						: $();
					if (!$hidden.length) {
						$hidden = $('.select2-container--open .select2-search--inline .select2-search__field');
					}
					$hidden.val(q).trigger('input').trigger('keyup');
				});
				setTimeout(function () {
					$input.trigger('focus');
				}, 0);
			})
			.on('select2:close.lsDdSearch select2:select.lsDdSearch select2:unselect.lsDdSearch', function () {
				clampLsSelect2Search($el);
			});
	}

	function destroySelect2($el) {
		if ($el.data('select2') || $el.hasClass('select2-hidden-accessible')) {
			try { $el.select2('destroy'); } catch (err) {}
		}
	}

	function initQuotationShowSelect2($root) {
		if (!$.fn.select2) {
			return;
		}

		$root.find('select.ls-select2-multi-dropdown-search-el').not('#ls-quote-analysis-line-prototype select').each(function () {
			var $el = $(this);
			destroySelect2($el);
			var isSingle = $el.data('ls-single') === 1 || $el.data('ls-single') === '1' || !$el.prop('multiple');
			$el.select2({
				width: '100%',
				placeholder: $el.data('placeholder') || 'Select…',
				allowClear: true,
				closeOnSelect: isSingle,
				dropdownCssClass: 'ls-select2-dropdown-search',
				templateResult: function (data) {
					if (!data.id) {
						return data.text;
					}
					if (isSingle) {
						return data.text;
					}
					var selected = ($el.val() || []).indexOf(String(data.id)) !== -1;
					var $row = $('<span class="ls-select2-meta-row"><span class="ls-select2-check">' + (selected ? '✓' : '') + '</span><span class="ls-select2-meta-row__label"></span></span>');
					$row.find('.ls-select2-meta-row__label').text(data.text);
					return $row;
				},
				escapeMarkup: function (m) { return m; },
			});
			wireLsMultiDropdownSearch($el);
			clampLsSelect2Search($el);
		});

		$root.find('select.ls-select2-multi-columns-el').each(function () {
			var $el = $(this);
			destroySelect2($el);
			$el.select2({
				width: '100%',
				placeholder: $el.data('placeholder') || 'Select…',
				closeOnSelect: false,
				dropdownCssClass: 'ls-select2-dropdown-search',
				templateResult: function (data) {
					if (!data.id) {
						return data.text;
					}
					var meta = $(data.element).data('meta') || '';
					var selected = ($el.val() || []).indexOf(String(data.id)) !== -1;
					var $row = $(
						'<span class="ls-select2-meta-row ls-select2-meta-row--spread">' +
							'<span class="ls-select2-meta-row__label"></span>' +
							'<span class="ls-select2-meta-row__meta"></span>' +
							(selected ? '<i class="mdi mdi-check" style="color:#2563eb;"></i>' : '') +
						'</span>'
					);
					$row.find('.ls-select2-meta-row__label').text(data.text);
					$row.find('.ls-select2-meta-row__meta').text(meta);
					return $row;
				},
				escapeMarkup: function (m) { return m; },
			});
			wireLsMultiDropdownSearch($el);
			clampLsSelect2Search($el);
		});
	}

	function searchBasicRoot(fieldId) {
		var hidden = document.getElementById(fieldId);
		return hidden ? hidden.closest('[data-ls-search-basic]') : null;
	}

	function searchBasicData(fieldId) {
		var root = searchBasicRoot(fieldId);
		if (!root || !window.Alpine || typeof Alpine.$data !== 'function') {
			return null;
		}
		return Alpine.$data(root);
	}

	function setSearchBasicValue(fieldId, value, label) {
		var data = searchBasicData(fieldId);
		var hidden = document.getElementById(fieldId);
		if (hidden) {
			hidden.value = value == null ? '' : String(value);
		}
		if (!data) {
			return;
		}
		data.selected = value == null || value === '' ? null : String(value);
		data.q = label || (value ? data.labelFor(String(value)) : '');
	}

	function setSearchBasicOptions(fieldId, options, keepValue) {
		var data = searchBasicData(fieldId);
		var normalized = (options || []).map(function (opt) {
			return {
				value: String(opt.value),
				label: String(opt.label || opt.value),
				meta: opt.meta || null,
			};
		});
		if (data) {
			data.options = normalized;
			if (!keepValue) {
				data.selected = null;
				data.q = '';
				data.open = false;
				if (data.$refs && data.$refs.hidden) {
					data.$refs.hidden.value = '';
				}
			}
		}
	}

	function loadCustomerContacts(clientId, selectedContactId) {
		if (!clientId) {
			setSearchBasicOptions('select-client-contact', []);
			return;
		}

		var url = '/fetch-customer-contacts/' + clientId;
		if (selectedContactId) {
			url += '?assigned=' + encodeURIComponent(selectedContactId);
		}

		$.ajax({
			url: url,
			success: function (data) {
				var options = [];
				(data || []).forEach(function (s) {
					var label = [s.first_name, s.middle_name, s.last_name].filter(Boolean).join(' ').trim();
					options.push({
						value: String(s.id),
						label: label || 'Contact',
					});
				});
				setSearchBasicOptions('select-client-contact', options, !!selectedContactId);
				if (selectedContactId) {
					var hit = options.find(function (o) { return o.value === String(selectedContactId); });
					if (hit) {
						setSearchBasicValue('select-client-contact', hit.value, hit.label);
					}
				}
			},
			error: function () {
				setSearchBasicOptions('select-client-contact', []);
			}
		});
	}

	function loadCustomerLocations(clientId) {
		var $unitSelect = $('#quote-company-unit');
		var $pointSelect = $('#quote-sample-point');

		if (!clientId) {
			$unitSelect.empty().append('<option value=""></option>').trigger('change');
			$pointSelect.empty().append('<option value=""></option>').trigger('change');
			return;
		}

		$.ajax({
			url: '/billing-quotation-customer-locations/' + encodeURIComponent(clientId),
			success: function (data) {
				var currentUnit = String($unitSelect.val() || '');
				var currentPoint = String($pointSelect.val() || '');

				$unitSelect.empty().append('<option value=""></option>');
				$pointSelect.empty().append('<option value=""></option>');

				(data.units || []).forEach(function (unit) {
					$unitSelect.append($('<option></option>').val(unit.id).text(unit.name || 'Unit'));
				});

				(data.sample_points || []).forEach(function (point) {
					$pointSelect.append(
						$('<option></option>')
							.val(point.id)
							.text(point.name || 'Sample point')
							.attr('data-unit-id', point.crm_company_unit_id || '')
					);
				});

				if (currentUnit && $unitSelect.find('option[value="' + currentUnit + '"]').length) {
					$unitSelect.val(currentUnit);
				} else {
					$unitSelect.val(null);
				}
				if (currentPoint && $pointSelect.find('option[value="' + currentPoint + '"]').length) {
					$pointSelect.val(currentPoint);
				} else {
					$pointSelect.val(null);
				}

				$unitSelect.trigger('change');
				$pointSelect.trigger('change');
				filterQuoteSamplePointsByUnit();
			}
		});
	}

	function filterQuoteSamplePointsByUnit() {
		var unitId = String($('#quote-company-unit').val() || '');
		var $points = $('#quote-sample-point');
		var selected = String($points.val() || '');
		var keepSelected = false;

		$points.find('option').each(function () {
			var $opt = $(this);
			if (!$opt.val()) {
				$opt.prop('disabled', false);
				return;
			}
			var optUnit = String($opt.data('unit-id') || $opt.attr('data-unit-id') || '');
			var visible = unitId === '' || optUnit === '' || optUnit === unitId;
			$opt.prop('disabled', !visible);
			if (visible && $opt.val() === selected) {
				keepSelected = true;
			}
		});

		if (!keepSelected) {
			$points.val(null);
		}
		$points.trigger('change');
	}

	function onClientChange(clientId, meta) {
		var form = document.getElementById('quotation-header-form');
		var assigned = form ? form.getAttribute('data-assigned-contact') : '';
		var keepAssigned = assigned && String($('#select-client').val()) === String(clientId);

		loadCustomerContacts(clientId, keepAssigned ? assigned : null);
		loadCustomerLocations(clientId);

		var currencyId = meta && meta.currency_id ? meta.currency_id : '';
		if (currencyId) {
			var currencyData = searchBasicData('currency-id-edit');
			if (currencyData && currencyData.options.some(function (o) { return String(o.value) === String(currencyId); })) {
				setSearchBasicValue('currency-id-edit', currencyId);
				$('#display-currency-edit').val(currencyData.labelFor(String(currencyId)));
			}
		}

		var zohoId = meta && meta.zoho_customer_id ? meta.zoho_customer_id : '';
		if (zohoId && $('#select-zoho-customer-edit').length) {
			$('#select-zoho-customer-edit').val(zohoId).trigger('change');
		}
	}

	$(function () {
		var $root = $('.quotation-show-page');
		initQuotationShowSelect2($root);

		// Stamp unit ids onto sample point options rendered by Blade
		var form = document.getElementById('quotation-header-form');
		var unitMap = {};
		try {
			unitMap = form ? JSON.parse(form.getAttribute('data-sample-point-units') || '{}') : {};
		} catch (err) {
			unitMap = {};
		}
		Object.keys(unitMap).forEach(function (pointId) {
			$('#quote-sample-point option[value="' + pointId + '"]').attr('data-unit-id', unitMap[pointId] || '');
		});

		filterQuoteSamplePointsByUnit();

		$root.on('ls-search-basic:change', function (event) {
			var detail = event.originalEvent && event.originalEvent.detail
				? event.originalEvent.detail
				: {};
			if (detail.id === 'select-client' || detail.name === 'client') {
				onClientChange(detail.value || '', detail.meta || {});
			}
			if (detail.id === 'currency-id-edit' || detail.name === 'currency_id') {
				$('#display-currency-edit').val(detail.label || '');
				// Keep terms-of-sale currency select in sync when present
				var $termsCurrency = $('form[action*="add_quotation_detail"] select[name="currency_id"]');
				if ($termsCurrency.length) {
					$termsCurrency.val(detail.value || '').trigger('change');
				}
			}
		});

		$('#quote-company-unit').on('change', filterQuoteSamplePointsByUnit);

		$('select[name="currency_id"]').on('change', function () {
			var currencyId = $(this).val();
			var label = $(this).find('option:selected').text();
			if (this.id === 'currency-id-edit' || $(this).closest('#quotation-header-form').length) {
				return;
			}
			setSearchBasicValue('currency-id-edit', currencyId, label);
			$('#display-currency-edit').val(currencyId ? label : '');
		});
	});
})(jQuery);
</script>
