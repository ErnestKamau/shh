{{-- Shared LS Select2 init for Fill TRF + Direct Registration fill (Edit request details parity). --}}
<script>
(function () {
	const $ = window.jQuery;

	const walkInDropdownParent = ($el) => {
		const $modal = $el.closest('.modal, .ceq-wizard-overlay, .ceq-wizard-dialog, .ceq-wizard-modal');
		if ($modal.length) {
			const $content = $modal.find('.modal-content, .ceq-wizard-modal').first();
			return $content.length ? $content : $modal;
		}

		const $rvModal = $el.closest('.rv-modal');
		if ($rvModal.length) {
			return $rvModal;
		}

		// Prefer the wizard shell; it is position:relative in page mode so Select2
		// absolute dropdowns do not attach to body or inflate #main-container-body.
		const $shell = $el.closest('.walk-in-trf-wizard-shell');
		if ($shell.length) {
			return $shell;
		}

		const $panel = $el.closest('.workflow-board-panel');
		if ($panel.length) {
			return $panel;
		}

		const $theme = $el.closest('.trf-ls-theme');
		if ($theme.length) {
			return $theme;
		}

		return $(document.body);
	};

	const clampLsSelect2Search = ($el) => {
		const $container = $el.next('.select2-container');
		if (! $container.length) {
			return;
		}

		if ($el.prop('multiple')) {
			$container.find('.select2-search--inline .select2-search__field').attr(
				'style',
				'width:0!important;min-width:0!important;max-width:0!important;height:0!important;margin:0!important;padding:0!important;border:0!important;opacity:0!important;position:absolute!important;left:-9999px!important;'
			);
			$container.css({ maxWidth: '100%', overflow: 'hidden' });
			return;
		}

		$container.css({ maxWidth: '100%' });
	};

	const wireLsMultiDropdownSearch = ($el) => {
		$el.off('select2:open.lsDdSearch select2:close.lsDdSearch select2:select.lsDdSearch select2:unselect.lsDdSearch')
			.on('select2:open.lsDdSearch', function () {
				clampLsSelect2Search($el);

				const select2Instance = $el.data('select2');
				let $dropdown = $();
				if (select2Instance?.$dropdown?.length) {
					$dropdown = select2Instance.$dropdown.find('.select2-dropdown');
					if (! $dropdown.length && select2Instance.$dropdown.hasClass('select2-dropdown')) {
						$dropdown = select2Instance.$dropdown;
					}
				}
				if (! $dropdown.length) {
					const $parent = walkInDropdownParent($el);
					$dropdown = ($parent.length ? $parent : $(document.body))
						.find('.select2-container--open .select2-dropdown')
						.last();
				}
				if (! $dropdown.length) {
					return;
				}

				$dropdown.addClass('ls-select2-dropdown-search');
				$dropdown.find('.select2-search--dropdown').attr(
					'style',
					'display:none!important;height:0!important;padding:0!important;margin:0!important;border:0!important;overflow:hidden!important;'
				);

				const $existing = $dropdown.find('.ls-dd-search');
				if ($existing.length) {
					$existing.find('input').val('').trigger('focus');
					return;
				}

				const $box = $(
					'<div class="ls-dd-search">' +
						'<i class="mdi mdi-magnify" aria-hidden="true"></i>' +
						'<input type="search" placeholder="Search…" autocomplete="off">' +
					'</div>'
				);
				$dropdown.prepend($box);

				const $input = $box.find('input');
				$input.on('input keyup', function () {
					const q = $input.val();
					let $hidden = $dropdown.find('.select2-search--dropdown .select2-search__field');
					if (! $hidden.length && select2Instance?.$selection) {
						$hidden = select2Instance.$selection.find('.select2-search__field');
					}
					if (! $hidden.length) {
						$hidden = $('.select2-container--open .select2-search--inline .select2-search__field');
					}
					$hidden.val(q).trigger('input').trigger('keyup');
				});

				window.setTimeout(function () {
					$input.trigger('focus');
				}, 0);
			})
			.on('select2:close.lsDdSearch select2:select.lsDdSearch select2:unselect.lsDdSearch', function () {
				clampLsSelect2Search($el);
			});
	};

	const bindWalkInSelect2LivewireSync = ($el) => {
		if ($el.hasClass('rft-tests-select2')) {
			return;
		}

		$el.off('change.walkInLs').on('change.walkInLs', function () {
			const vals = $el.val() || [];
			const list = Array.isArray(vals) ? vals.map(String) : (vals ? [String(vals)] : []);
			const prev = JSON.stringify($el.data('walkInLsBoundValues') || []);
			const next = JSON.stringify(list);
			if (prev === next) {
				return;
			}
			$el.data('walkInLsBoundValues', list);

			const syncMethod = $el.data('sync-method');
			const syncKey = $el.data('sync-key') || $el.data('wire-field');
			const wireField = $el.data('wire-field');
			const componentEl = $el.closest('[wire\\:id]')[0];
			if (! componentEl || ! window.Livewire) {
				return;
			}
			const component = Livewire.find(componentEl.getAttribute('wire:id'));
			if (! component) {
				return;
			}
			if (syncMethod && syncKey !== undefined && syncKey !== null && syncKey !== '' && typeof component.call === 'function') {
				const firstArg = /^-?\d+$/.test(String(syncKey)) ? parseInt(String(syncKey), 10) : String(syncKey);
				try {
					$el.select2('close');
				} catch (e) {}
				component.call(syncMethod, firstArg, list);
				return;
			}
			if (wireField) {
				component.set(String(wireField), $el.prop('multiple') ? list : (list[0] ?? ''), !!$el.data('select-live'));
			}
		});
	};

	const openRftTestsPickerModal = ($el) => {
		const pickerEl = $el.closest('.rft-tests-picker')[0];
		if (! pickerEl || ! pickerEl._x_dataStack?.length) {
			return;
		}
		const ui = pickerEl._x_dataStack[0];
		if (ui && typeof ui.toggleOpen === 'function') {
			ui.toggleOpen();
		}
	};

	const wireRftTestsDisplayOnlySelect2 = ($el) => {
		$el.off('.rftTestsDisplay');
		$el.data('rftTestsDisplayOnly', true);

		$el.on('select2:opening.rftTestsDisplay select2:unselecting.rftTestsDisplay', function (event) {
			event.preventDefault();
		});

		const $container = $el.next('.select2-container');
		$container.off('.rftTestsDisplay');
		$container.on('mousedown.rftTestsDisplay', '.select2-selection', function (event) {
			event.preventDefault();
			event.stopPropagation();
			openRftTestsPickerModal($el);
		});
	};

	const initOneWalkInSelect2 = ($el) => {
		if (! $ || ! $.fn.select2 || ! $el.length) {
			return;
		}

		if ($el.data('select2')) {
			$el.off('.walkInLs').off('.lsDdSearch').off('.rftTestsDisplay');
			const $prevContainer = $el.next('.select2-container');
			if ($prevContainer.length) {
				$prevContainer.off('.rftTestsDisplay');
			}
			try {
				$el.select2('destroy');
			} catch (e) {}
		}

		const isMultiple = !! $el.prop('multiple');
		const isColumns = isMultiple && (
			$el.data('ls-multi-columns')
			|| $el.hasClass('ls-select2-multi-columns-el')
		);
		const isMultiDdSearchEl = $el.data('ls-multi-dropdown-search')
			|| $el.hasClass('ls-select2-multi-dropdown-search-el');
		const isLsMultiSearch = isMultiple && (isMultiDdSearchEl || isColumns);
		const isLsSingleDdSearch = ! isMultiple && (
			$el.data('ls-single-dropdown-search')
			|| $el.hasClass('ls-select2-single-dropdown-search-el')
			|| isMultiDdSearchEl
		);

		const options = {
			width: '100%',
			placeholder: $el.data('placeholder') || 'Select…',
			allowClear: ! isMultiple,
			closeOnSelect: ! isMultiple,
			dropdownParent: walkInDropdownParent($el),
		};

		if (isLsMultiSearch || isLsSingleDdSearch) {
			options.dropdownCssClass = 'ls-select2-dropdown-search';
		}

		if (isLsSingleDdSearch) {
			options.closeOnSelect = true;
			options.allowClear = true;
		} else if (isMultiple) {
			options.closeOnSelect = false;
			options.allowClear = false;
		}

		if (isColumns) {
			options.escapeMarkup = function (markup) { return markup; };
			options.templateResult = function (data) {
				if (! data.id) {
					return data.text;
				}
				const $opt = $(data.element);
				const analysisType = String($opt.attr('data-meta-analysis-type') || '').trim();
				const method = String($opt.attr('data-meta-method') || $opt.data('meta') || '').trim();
				const lab = String($opt.attr('data-meta-lab') || '').trim();
				const metaParts = [];
				if (analysisType) {
					metaParts.push('<span class="ls-select2-meta-tag">' + $('<div>').text(analysisType).html() + '</span>');
				}
				if (method) {
					metaParts.push('<i class="mdi mdi-flask-outline"></i> ' + $('<div>').text(method).html());
				}
				if (lab) {
					metaParts.push('<i class="mdi mdi-domain"></i> ' + $('<div>').text(lab).html());
				}
				const selected = ($el.val() || []).indexOf(String(data.id)) !== -1;
				const $row = $(
					'<span class="ls-select2-meta-row ls-select2-meta-row--spread">' +
						'<span class="ls-select2-meta-row__label"></span>' +
						'<span class="ls-select2-meta-row__meta"></span>' +
						(selected ? '<i class="mdi mdi-check" style="color:#2563eb;"></i>' : '') +
					'</span>'
				);
				$row.find('.ls-select2-meta-row__label').text(data.text);
				$row.find('.ls-select2-meta-row__meta').html(metaParts.join(' · '));
				return $row;
			};
			options.templateSelection = function (data) {
				if (! data.id) {
					return data.text;
				}
				const $elOpt = $(data.element);
				const analysisType = String($elOpt.attr('data-meta-analysis-type') || '').trim();
				const method = String($elOpt.attr('data-meta-method') || '').trim();
				const $chip = $(
					'<span class="ls-select2-choice-with-meta">' +
						'<span class="ls-select2-choice-with-meta__label"></span>' +
						'<span class="ls-select2-choice-with-meta__meta"></span>' +
					'</span>'
				);
				$chip.find('.ls-select2-choice-with-meta__label').text(data.text);
				const meta = [analysisType, method].filter(Boolean).join(' · ');
				$chip.find('.ls-select2-choice-with-meta__meta').text(meta);
				return $chip;
			};
		} else if (isLsMultiSearch || isLsSingleDdSearch) {
			options.escapeMarkup = function (markup) { return markup; };
			options.templateResult = function (data) {
				if (! data.id) {
					return data.text;
				}
				const currentVal = $el.val();
				const selected = isMultiple
					? (currentVal || []).indexOf(String(data.id)) !== -1
					: String(currentVal || '') === String(data.id);
				const $row = $(
					'<span class="ls-select2-meta-row">' +
						'<span class="ls-select2-check"></span>' +
						'<span class="ls-select2-meta-row__label"></span>' +
					'</span>'
				);
				$row.find('.ls-select2-check').text(selected ? '✓' : '');
				$row.find('.ls-select2-meta-row__label').text(data.text);
				return $row;
			};
		}

		$el.select2(options);

		if (isMultiple) {
			$el.val($el.val() || []).trigger('change.select2');
		} else {
			$el.val($el.val() || '').trigger('change.select2');
		}

		if (isLsMultiSearch || isLsSingleDdSearch) {
			wireLsMultiDropdownSearch($el);
			if (isLsMultiSearch) {
				clampLsSelect2Search($el);
				window.setTimeout(function () { clampLsSelect2Search($el); }, 0);
				window.setTimeout(function () { clampLsSelect2Search($el); }, 50);
			}
		}

		if (isMultiple) {
			const select2Instance = $el.data('select2');
			const isTestsDisplay = $el.hasClass('rft-tests-select2');
			const fitMultipleSelect2 = function () {
				const $container = select2Instance?.$container;
				if (! $container || ! $container.length) {
					return;
				}
				if (isTestsDisplay) {
					$container.css({ height: 'auto', minHeight: 0 });
					window.applyWalkInTestsSelect2Summary($el, $el.val() || []);
					return;
				}
				$container.css({ height: 'auto', minHeight: 0 });
				$container.find('.selection, .select2-selection--multiple, .select2-selection__rendered').css({
					height: 'auto',
					minHeight: 0,
					maxHeight: 'none',
				});
				if (isLsMultiSearch) {
					clampLsSelect2Search($el);
				}
			};
			fitMultipleSelect2();
			$el.on('select2:open.walkInLs select2:close.walkInLs select2:select.walkInLs select2:unselect.walkInLs', fitMultipleSelect2);
			window.setTimeout(fitMultipleSelect2, 0);
			window.setTimeout(fitMultipleSelect2, 50);
		}

		$el.data('walkInLsBoundValues', $el.val() || []);
		$el.data('walkInLsOptionSig', String($el.find('option').length));
		bindWalkInSelect2LivewireSync($el);

		if ($el.hasClass('rft-tests-select2')) {
			wireRftTestsDisplayOnlySelect2($el);
		}
	};

	const walkInSelectSelector = [
		'.ls-select2-multi-dropdown-search-el.walk-in-ls-select2',
		'.ls-select2-multi-columns-el.walk-in-ls-select2',
		'.ls-select2-single-dropdown-search-el.walk-in-ls-select2',
	].join(', ');

	window.initOneWalkInSelect2 = initOneWalkInSelect2;

	window.initWalkInLsSelect2 = function (scope, forceReinit) {
		if (! $ || ! $.fn.select2) {
			return;
		}
		const $scope = scope ? $(scope) : $(document);
		$scope.find(walkInSelectSelector).addBack(walkInSelectSelector).filter(walkInSelectSelector).each(function () {
			const $el = $(this);
			if ($el.hasClass('select2-hidden-accessible')) {
				try {
					const prev = JSON.stringify($el.data('walkInLsBoundValues') || []);
					const next = JSON.stringify($el.val() || []);
					const optsChanged = $el.data('walkInLsOptionSig') !== String($el.find('option').length);
					const storedParent = $el.data('select2')?.options?.options?.dropdownParent;
					const parentDetached = storedParent && storedParent.length && ! storedParent[0].isConnected;
					const parentIsBody = storedParent && storedParent[0] === document.body;
					const shouldKeep = ! forceReinit && prev === next && ! optsChanged && ! parentDetached && ! parentIsBody;
					if (shouldKeep) {
						return;
					}
					$el.off('change.walkInLs').off('.lsDdSearch').select2('destroy');
				} catch (e) {}
			}
			initOneWalkInSelect2($el);
		});
	};

	window.initWalkInLsSelect2ForRow = function (rowIndex) {
		const card = document.querySelector('.rft-sample-row-card[data-trf-sample-index="' + rowIndex + '"]');
		if (card) {
			window.initWalkInLsSelect2(card);
		}
	};

	window.applyWalkInTestsSelect2Summary = function ($el, list) {
		const $container = $el.next('.select2-container');
		if (! $container.length) {
			return;
		}
		const $rendered = $container.find('.select2-selection__rendered');
		const count = Array.isArray(list) ? list.length : 0;
		if (count > 0) {
			$rendered
				.addClass('rft-tests-select2__summary')
				.attr('data-summary', count + ' test' + (count === 1 ? '' : 's') + ' selected');
		} else {
			$rendered.removeClass('rft-tests-select2__summary').removeAttr('data-summary');
		}
	};

	window.syncWalkInTestsSelect2 = function (fieldId, rowIndex, selectedValues) {
		let select = fieldId ? document.getElementById(String(fieldId)) : null;
		if (! select && rowIndex !== undefined && rowIndex !== null) {
			select = walkInFieldSelectId('parameters', rowIndex);
		}
		if (! select || ! $) {
			return;
		}
		const list = Array.isArray(selectedValues) ? selectedValues.map(String) : [];
		const $el = $(select);
		const prev = JSON.stringify($el.data('walkInLsBoundValues') || []);
		const next = JSON.stringify(list);
		if (prev === next) {
			return;
		}
		$el.data('walkInLsBoundValues', list);
		if ($el.data('select2')) {
			$el.val(list).trigger('change.select2');
		} else {
			Array.from(select.options).forEach(function (option) {
				option.selected = list.includes(option.value);
			});
		}
		window.applyWalkInTestsSelect2Summary($el, list);
	};

	const walkInFieldSelectId = function (fieldName, rowIndex) {
		const aliases = {
			sample_type: ['sample_type_id', 'sample_type'],
			sample_type_id: ['sample_type_id', 'sample_type'],
			analysis_type: ['analysis_type_id', 'analysis_type', 'analysis_types'],
			analysis_type_id: ['analysis_type_id', 'analysis_type', 'analysis_types'],
			analysis_types: ['analysis_type_id', 'analysis_type', 'analysis_types'],
			parameter: ['parameters', 'parameter'],
			parameters: ['parameters', 'parameter'],
		};
		const candidates = aliases[fieldName] || [fieldName];
		const card = document.querySelector('.rft-sample-row-card[data-trf-sample-index="' + rowIndex + '"]');

		for (let i = 0; i < candidates.length; i++) {
			const select = document.getElementById('field_' + candidates[i] + '_' + rowIndex);
			if (select) {
				return select;
			}
		}

		if (! card) {
			return null;
		}

		const suffix = '.' + rowIndex;
		const matches = card.querySelectorAll('select.walk-in-ls-select2');
		for (let i = 0; i < matches.length; i++) {
			const select = matches[i];
			const wireField = select.getAttribute('data-wire-field') || select.dataset.wireField || '';
			const syncKey = select.getAttribute('data-sync-key') || select.dataset.syncKey || '';
			for (let j = 0; j < candidates.length; j++) {
				const key = 'formData.' + candidates[j] + suffix;
				if (wireField === key || syncKey === key) {
					return select;
				}
			}
		}

		return null;
	};

	const rebuildWalkInSelectOptions = function (select, options, selectedValues) {
		const isMultiple = select.multiple;
		select.innerHTML = '';

		if (! isMultiple) {
			const emptyOption = document.createElement('option');
			emptyOption.value = '';
			emptyOption.textContent = '— Select —';
			select.appendChild(emptyOption);
		}

		(options || []).forEach(function (option) {
			const node = document.createElement('option');
			node.value = String(option.value ?? '');
			node.textContent = String(option.label ?? option.value ?? '');
			if (option.meta_method) {
				node.setAttribute('data-meta-method', String(option.meta_method));
			}
			if (option.meta_lab) {
				node.setAttribute('data-meta-lab', String(option.meta_lab));
			}
			if (option.meta) {
				node.setAttribute('data-meta', String(option.meta));
			}
			select.appendChild(node);
		});

		const $el = $(select);
		if ($el.data('select2')) {
			$el.val(isMultiple ? selectedValues : (selectedValues[0] ?? '')).trigger('change.select2');
		} else if (isMultiple) {
			Array.from(select.options).forEach(function (option) {
				option.selected = selectedValues.includes(option.value);
			});
		} else {
			select.value = selectedValues[0] ?? '';
		}
		$el.data('walkInLsOptionSig', String($el.find('option').length));
	};

	const refreshWalkInCatalogSelectOptions = function (payload) {
		const detail = payload?.options !== undefined ? payload : (payload?.[0] ?? {});
		const rowIndex = detail.rowIndex;
		const optionsByField = detail.options ?? {};
		const cleared = Array.isArray(detail.cleared) ? detail.cleared : [];

		if (rowIndex === undefined || rowIndex === null) {
			return;
		}

		const applyField = function (fieldName, selectedValues) {
			const select = walkInFieldSelectId(fieldName, rowIndex);
			if (! select) {
				return;
			}
			const fieldOptions = optionsByField[fieldName] ?? optionsByField[fieldName === 'parameters' ? 'parameter' : fieldName] ?? [];
			rebuildWalkInSelectOptions(select, fieldOptions, selectedValues);
			if ($(select).data('select2')) {
				initOneWalkInSelect2($(select));
			}
		};

		cleared.forEach(function (fieldName) {
			applyField(fieldName, []);
		});

		Object.entries(optionsByField).forEach(function ([fieldName, fieldOptions]) {
			if (cleared.includes(fieldName)) {
				return;
			}
			const select = walkInFieldSelectId(fieldName, rowIndex);
			if (! select) {
				return;
			}
			const $el = $(select);
			const current = $el.prop('multiple') ? ($el.val() || []) : [($el.val() || '')];
			rebuildWalkInSelectOptions(select, fieldOptions, current);
			if ($el.data('select2')) {
				initOneWalkInSelect2($el);
			}
		});
	};

	const bootWalkInSelect2Events = function () {
		if (window.__walkInSelect2EventsBooted) {
			return;
		}
		window.__walkInSelect2EventsBooted = true;

		Livewire.on('walk-in-catalog-options-refreshed', function (payload) {
			window.setTimeout(function () {
				refreshWalkInCatalogSelectOptions(payload);
			}, 30);
		});
	};

	document.addEventListener('livewire:initialized', function () {
		bootWalkInSelect2Events();
		window.initWalkInLsSelect2();
	});

	document.addEventListener('DOMContentLoaded', function () {
		window.setTimeout(function () { window.initWalkInLsSelect2(); }, 200);
	});

	window.addEventListener('rft-sample-card-shown', function (event) {
		const rowIndex = event.detail?.row;
		if (rowIndex === undefined || rowIndex === null) {
			window.setTimeout(function () { window.initWalkInLsSelect2(); }, 120);
			return;
		}
		window.setTimeout(function () { window.initWalkInLsSelect2ForRow(rowIndex); }, 80);
	});
})();
</script>
