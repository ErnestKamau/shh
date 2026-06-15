<script>
    $(function () {
        var $modal = $('#add-quotation');

        function initQuotationModalSelect2() {
            if (!$.fn.select2) {
                return;
            }

            $modal.find('.quotation-modal-select').each(function () {
                var $select = $(this);
                if ($select.hasClass('select2-hidden-accessible')) {
                    try {
                        $select.select2('destroy');
                    } catch (e) {
                        // ignore stale instances
                    }
                }

                $select.select2({
                    width: '100%',
                    dropdownParent: $modal,
                    placeholder: $select.find('option:first').text() || 'Select...',
                });
            });
        }

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

        function loadCustomerContacts(clientId) {
            if (!clientId) {
                $('#select-client-contact').empty().append('<option value="" disabled selected>Select Client Contact</option>');
                $('#select-client-contact').trigger('change.select2');
                return;
            }

            $.ajax({
                url: '/fetch-customer-contacts/' + clientId,
                beforeSend: function () {
                    $('#select-client-contact').empty().append('<option value="" disabled selected>Loading...</option>');
                    $('#select-client-contact').trigger('change.select2');
                },
                success: function (data) {
                    $('#select-client-contact').empty().append('<option value="" disabled selected>Select Client Contact</option>');
                    $.each(data, function (j, s) {
                        var middle = s.middle_name ? s.middle_name + ' ' : '';
                        var label = (s.first_name || '') + ' ' + middle + (s.last_name || '');
                        $('#select-client-contact').append(
                            $('<option></option>').val(s.id).text(label.trim())
                        );
                    });
                    $('#select-client-contact').trigger('change.select2');
                },
                error: function () {
                    $('#select-client-contact').empty().append('<option value="" disabled selected>Unable to load contacts</option>');
                    $('#select-client-contact').trigger('change.select2');
                }
            });
        }

        $modal.on('shown.bs.modal', function () {
            initQuotationModalSelect2();
        });

        $('#select-client').on('change', function () {
            var client = $(this).val();
            var $selectedOption = $(this).find('option:selected');
            var zohoCustomerId = $selectedOption.data('zoho-customer-id');

            loadCustomerContacts(client);
            setCurrencyFromClientOption($selectedOption);

            if (zohoCustomerId) {
                $('#select-zoho-customer').val(String(zohoCustomerId)).trigger('change.select2').trigger('change');
                $('#zoho-customer-info').show();
            } else {
                $('#select-zoho-customer').val('').trigger('change.select2');
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
