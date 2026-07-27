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

        $modal.on('shown.bs.modal', function () {
            initModalSelect2();
            $('#select-quotation-type').val('Analysis');
            if (!$('#select-client').val()) {
                $('#select-client-contact').empty().append('<option value=""></option>');
                if ($('#select-client-contact').data('select2')) {
                    $('#select-client-contact').trigger('change.select2');
                }
            }
        });

        $('#select-client').on('change', function () {
            var client = $(this).val();
            var $selectedOption = $(this).find('option:selected');
            var clientCurrencyId = $selectedOption.data('currency-id');

            loadCustomerContacts(client);

            var $currency = $('#select-currency');
            if (clientCurrencyId && $currency.find('option[value="' + clientCurrencyId + '"]').length) {
                $currency.val(String(clientCurrencyId)).trigger('change');
            }
        });

        @if (request('open') === 'add')
            $modal.modal('show');
        @endif
    });
</script>
