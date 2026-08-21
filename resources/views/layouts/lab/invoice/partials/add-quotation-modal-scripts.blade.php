<script>
    $(function () {
        var $modal = $('#add-quotation');

        function initModalSelect2() {
            if (typeof window.initLsSelect2 === 'function') {
                window.initLsSelect2($modal);
                return;
            }

            $modal.find('select.ls-select2').each(function () {
                var $el = $(this);
                if ($el.data('select2')) {
                    try { $el.select2('destroy'); } catch (err) {}
                }

                $el.select2({
                    placeholder: $el.data('placeholder') || 'Select an option',
                    width: '100%',
                    allowClear: true,
                    dropdownParent: $modal.find('.modal-content').first()
                });
            });
        }

        function loadCustomerContacts(clientId, selectedContactId) {
            var $contactSelect = $('#select-client-contact');

            if (!clientId) {
                $contactSelect.empty().append('<option value=""></option>');
                if ($contactSelect.data('select2')) {
                    $contactSelect.trigger('change.select2');
                }
                return;
            }

            var url = '/fetch-customer-contacts/' + clientId;
            if (selectedContactId) {
                url += '?assigned=' + encodeURIComponent(selectedContactId);
            }

            $.ajax({
                url: url,
                beforeSend: function () {
                    $contactSelect.empty().append('<option value="">Loading contacts...</option>');
                    if ($contactSelect.data('select2')) {
                        $contactSelect.trigger('change.select2');
                    }
                },
                success: function (data) {
                    $contactSelect.empty().append('<option value=""></option>');
                    if (!data || data.length === 0) {
                        if ($contactSelect.data('select2')) {
                            $contactSelect.trigger('change.select2');
                        }
                        return;
                    }

                    $.each(data, function (j, s) {
                        var middle = s.middle_name ? s.middle_name + ' ' : '';
                        var label = ((s.first_name || '') + ' ' + middle + (s.last_name || '')).trim();
                        var isSelected = selectedContactId && String(s.id) === String(selectedContactId);
                        $contactSelect.append(
                            $('<option></option>')
                                .val(s.id)
                                .text(label || 'Contact')
                                .prop('selected', isSelected)
                        );
                    });

                    if ($contactSelect.data('select2')) {
                        $contactSelect.trigger('change.select2');
                    }
                },
                error: function () {
                    $contactSelect.empty().append('<option value=""></option>');
                    if ($contactSelect.data('select2')) {
                        $contactSelect.trigger('change.select2');
                    }
                }
            });
        }

        function clearLocationSelects() {
            $('#select-company-unit, #select-sample-point').each(function () {
                var $el = $(this);
                $el.empty().append('<option value=""></option>');
                if ($el.data('select2')) {
                    $el.trigger('change.select2');
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
                    if ($unitSelect.data('select2')) {
                        $unitSelect.trigger('change.select2');
                    }
                    if ($pointSelect.data('select2')) {
                        $pointSelect.trigger('change.select2');
                    }
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

                    if ($unitSelect.data('select2')) {
                        $unitSelect.trigger('change.select2');
                    }
                    if ($pointSelect.data('select2')) {
                        $pointSelect.trigger('change.select2');
                    }
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
                $pointSelect.val('');
            }
            if ($pointSelect.data('select2')) {
                $pointSelect.trigger('change.select2');
            }
        }

        $modal.on('shown.bs.modal', function () {
            initModalSelect2();
            $('#select-quotation-type').val('Analysis');
            if (!$('#select-client').val()) {
                $('#select-client-contact').empty().append('<option value=""></option>');
                if ($('#select-client-contact').data('select2')) {
                    $('#select-client-contact').trigger('change.select2');
                }
                clearLocationSelects();
            }
        });

        $('#select-client').on('change', function () {
            var client = $(this).val();
            var $selectedOption = $(this).find('option:selected');
            var clientCurrencyId = $selectedOption.data('currency-id');

            loadCustomerContacts(client);
            loadCustomerLocations(client);

            var $currency = $('#select-currency');
            if (clientCurrencyId && $currency.find('option[value="' + clientCurrencyId + '"]').length) {
                $currency.val(String(clientCurrencyId)).trigger('change');
            }
        });

        $('#select-company-unit').on('change', filterSamplePointsByUnit);

        @if (request('open') === 'add')
            $modal.modal('show');
        @endif
    });
</script>
