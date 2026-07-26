<script>
    $(function () {
        var $modal = $('#add-quotation');

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
            var clientCurrencyId = $selectedOption.data('currency-id');

            loadCustomerContacts(client);

            var $currency = $('#select-currency');
            if (clientCurrencyId && $currency.find('option[value="' + clientCurrencyId + '"]').length) {
                $currency.val(String(clientCurrencyId));
            }
        });

        @if (request('open') === 'add')
            $modal.modal('show');
        @endif
    });
</script>
