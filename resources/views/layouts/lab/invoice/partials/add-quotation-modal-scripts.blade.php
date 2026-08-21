<script>
    $(function () {
        var $modal = $('#add-quotation');

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

        function initModalSelect2() {
            if (!$.fn.select2) {
                return;
            }

            var $parent = $modal.find('.modal-content').first();

            $modal.find('select.ls-select2-multi-dropdown-search-el').each(function () {
                var $el = $(this);
                destroySelect2($el);
                var isSingle = $el.data('ls-single') === 1 || $el.data('ls-single') === '1' || !$el.prop('multiple');
                $el.select2({
                    width: '100%',
                    placeholder: $el.data('placeholder') || 'Select…',
                    allowClear: true,
                    closeOnSelect: isSingle,
                    dropdownParent: $parent,
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

            $modal.find('select.ls-select2-multi-columns-el').each(function () {
                var $el = $(this);
                destroySelect2($el);
                $el.select2({
                    width: '100%',
                    placeholder: $el.data('placeholder') || 'Select…',
                    closeOnSelect: false,
                    dropdownParent: $parent,
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
            if (!hidden) {
                return null;
            }
            return hidden.closest('[data-ls-search-basic]');
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
                return;
            }
            var hidden = document.getElementById(fieldId);
            if (hidden && !keepValue) {
                hidden.value = '';
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

            setSearchBasicOptions('select-client-contact', [{ value: '', label: 'Loading contacts…' }]);

            $.ajax({
                url: url,
                success: function (data) {
                    var options = [];
                    (data || []).forEach(function (s) {
                        var middle = s.middle_name ? s.middle_name + ' ' : '';
                        var label = ((s.first_name || '') + ' ' + middle + (s.last_name || '')).trim();
                        options.push({
                            value: String(s.id),
                            label: label || 'Contact',
                        });
                    });
                    setSearchBasicOptions('select-client-contact', options);
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

        function clearLocationSelects() {
            ['#select-company-unit', '#select-sample-point'].forEach(function (sel) {
                var $el = $(sel);
                $el.empty().append('<option value=""></option>');
                if ($el.data('select2')) {
                    $el.val(null).trigger('change');
                }
            });
        }

        function loadCustomerLocations(clientId) {
            var $unitSelect = $('#select-company-unit');
            var $pointSelect = $('#select-sample-point');

            if (!clientId) {
                clearLocationSelects();
                return;
            }

            $.ajax({
                url: '/billing-quotation-customer-locations/' + encodeURIComponent(clientId),
                beforeSend: function () {
                    $unitSelect.empty().append('<option value="">Loading...</option>');
                    $pointSelect.empty().append('<option value="">Loading...</option>');
                    $unitSelect.trigger('change.select2');
                    $pointSelect.trigger('change.select2');
                },
                success: function (data) {
                    $unitSelect.empty().append('<option value=""></option>');
                    $pointSelect.empty().append('<option value=""></option>');

                    (data.units || []).forEach(function (unit) {
                        $unitSelect.append(
                            $('<option></option>').val(unit.id).text(unit.name || 'Unit')
                        );
                    });

                    (data.sample_points || []).forEach(function (point) {
                        $pointSelect.append(
                            $('<option></option>')
                                .val(point.id)
                                .text(point.name || 'Sample point')
                                .attr('data-unit-id', point.crm_company_unit_id || '')
                        );
                    });

                    $unitSelect.val(null).trigger('change');
                    $pointSelect.val(null).trigger('change');
                },
                error: function () {
                    clearLocationSelects();
                }
            });
        }

        function filterSamplePointsByUnit() {
            var unitId = String($('#select-company-unit').val() || '');
            var $pointSelect = $('#select-sample-point');
            var selected = String($pointSelect.val() || '');
            var keepSelected = false;

            $pointSelect.find('option').each(function () {
                var $opt = $(this);
                if (!$opt.val()) {
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
                $pointSelect.val(null);
            }
            $pointSelect.trigger('change');
        }

        function onClientChange(clientId, currencyId) {
            loadCustomerContacts(clientId);
            loadCustomerLocations(clientId);

            if (currencyId) {
                var currencyData = searchBasicData('select-currency');
                if (currencyData && currencyData.options.some(function (o) { return String(o.value) === String(currencyId); })) {
                    setSearchBasicValue('select-currency', currencyId);
                }
            }
        }

        $modal.on('shown.bs.modal', function () {
            initModalSelect2();
            if (!$('#select-client').val()) {
                setSearchBasicOptions('select-client-contact', []);
                clearLocationSelects();
            }
        });

        $modal.on('ls-search-basic:change', function (event) {
            var detail = event.originalEvent && event.originalEvent.detail
                ? event.originalEvent.detail
                : {};
            if (detail.id !== 'select-client' && detail.name !== 'client') {
                return;
            }
            var clientId = detail.value || '';
            var currencyId = '';
            if (detail.meta && detail.meta.currency_id) {
                currencyId = detail.meta.currency_id;
            }
            onClientChange(clientId, currencyId);
        });

        $('#select-company-unit').on('change', filterSamplePointsByUnit);

        @if (request('open') === 'add')
            $modal.modal('show');
        @endif
    });
</script>
