<script>
    $(function () {
        var $modal = $('#add-quotation');

        function setCurrencyFields(currencyId, code, description) {
            if (currencyId) {
                var label = code || '';
                if (description) {
                    label = label ? (label + ' - ' + description) : description;
                }
                $('#display-currency').val(label);
                $('#currency-id').val(currencyId);
            } else {
                $('#display-currency').val('');
                $('#currency-id').val('');
            }
        }

        function setCurrencyFromClientOption($option) {
            setCurrencyFields(
                $option.data('currency-id') || '',
                $option.data('currency-code') || '',
                $option.data('currency-description') || ''
            );
        }

        function loadCustomerContacts(clientId, selectedContactId) {
            var $contactSelect = $('#select-client-contact');

            if (!clientId) {
                $contactSelect.empty().append('<option value="" disabled selected>Select client first...</option>');
                return;
            }

            var url = '/fetch-customer-contacts/' + clientId;
            if (selectedContactId) {
                url += '?assigned=' + encodeURIComponent(selectedContactId);
            }

            $.ajax({
                url: url,
                beforeSend: function () {
                    $contactSelect.empty().append('<option value="" disabled selected>Loading contacts...</option>');
                },
                success: function (data) {
                    $contactSelect.empty();
                    if (!data || data.length === 0) {
                        $contactSelect.append('<option value="" disabled selected>No active contacts found</option>');
                        return;
                    }

                    var hasSelected = false;
                    $.each(data, function (j, s) {
                        var middle = s.middle_name ? s.middle_name + ' ' : '';
                        var label = ((s.first_name || '') + ' ' + middle + (s.last_name || '')).trim();
                        var isSelected = selectedContactId && String(s.id) === String(selectedContactId);
                        if (isSelected) {
                            hasSelected = true;
                        }
                        $contactSelect.append(
                            $('<option></option>')
                                .val(s.id)
                                .text(label || 'Contact')
                                .prop('selected', isSelected)
                        );
                    });

                    if (!hasSelected) {
                        $contactSelect.prepend('<option value="" disabled selected>Select Client Contact</option>');
                    }
                },
                error: function () {
                    $contactSelect.empty().append('<option value="" disabled selected>Unable to load contacts</option>');
                }
            });
        }

        $modal.on('shown.bs.modal', function () {
            $('#select-quotation-type').val('Analysis');
            if (!$('#select-client').val()) {
                $('#select-client-contact').empty().append('<option value="" disabled selected>Select client first...</option>');
            }
        });

        $('#select-client').on('change', function () {
            var client = $(this).val();
            var $selectedOption = $(this).find('option:selected');
            var zohoCustomerId = $selectedOption.data('zoho-customer-id');

            loadCustomerContacts(client);
            setCurrencyFromClientOption($selectedOption);

            if (zohoCustomerId) {
                $('#select-zoho-customer').val(String(zohoCustomerId)).trigger('change');
                $('#zoho-customer-info').show();
            } else {
                $('#select-zoho-customer').val('');
                $('#zoho-customer-info').hide();
            }
        });

        $('#select-zoho-customer').on('change', function () {
            var zohoCustomerId = $(this).val();
            var $selectedOption = $(this).find('option:selected');
            var currencyCode = $selectedOption.data('currency-code');

            if (!zohoCustomerId) {
                var $clientOption = $('#select-client').find('option:selected');
                setCurrencyFromClientOption($clientOption);
                $('#zoho-customer-info').hide();
                return;
            }

            if (currencyCode) {
                $.ajax({
                    url: '/api/get-currency-by-code',
                    method: 'POST',
                    data: {
                        currency_code: currencyCode,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (currency) {
                        if (currency && currency.id) {
                            setCurrencyFields(currency.id, currency.code, currency.description);
                            $('#zoho-customer-info').show();
                        } else {
                            setCurrencyFields('', '', '');
                        }
                    },
                    error: function () {
                        $('#display-currency').val('Error loading currency');
                        $('#currency-id').val('');
                    }
                });
            } else {
                $('#zoho-customer-info').show();
            }
        });

        @if (request('open') === 'add')
            $modal.modal('show');
        @endif
    });
</script>
