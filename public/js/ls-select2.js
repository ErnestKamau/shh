/**
 * Lab-surface Select2 helper — modal-safe init/destroy for Livewire.
 * Opt-in via class: .ls-select2 or .livewire-select2
 * Skip via: .no-select2
 */
(function (window, $) {
    'use strict';

    if (!$ || !$.fn || !$.fn.select2) {
        window.initLsSelect2 = function () {};
        window.destroyLsSelect2 = function () {};
        return;
    }

    function resolveDropdownParent($el) {
        var $modal = $el.closest('.modal');
        if ($modal.length) {
            var $content = $modal.find('.modal-content').first();
            return $content.length ? $content : $modal;
        }

        var $wizard = $el.closest('.acc-wizard-modal, .acc-wizard-root');
        if ($wizard.length) {
            return $wizard;
        }

        return $(document.body);
    }

    function shouldInit($el) {
        if (!$el || !$el.length) {
            return false;
        }

        if ($el.hasClass('no-select2') && !$el.hasClass('ls-select2') && !$el.hasClass('livewire-select2')) {
            return false;
        }

        return $el.hasClass('ls-select2') || $el.hasClass('livewire-select2');
    }

    function destroyOne($el) {
        if ($el.hasClass('select2-hidden-accessible') || $el.data('select2')) {
            try {
                $el.select2('destroy');
            } catch (e) {
                // already destroyed
            }
        }
    }

    function initOne($el) {
        if (!shouldInit($el)) {
            return;
        }

        destroyOne($el);

        var options = {
            width: '100%',
            placeholder: $el.attr('placeholder') || $el.data('placeholder') || 'Select an option',
            allowClear: $el.data('allow-clear') !== undefined ? !!$el.data('allow-clear') : true,
            dropdownParent: resolveDropdownParent($el),
        };

        if ($el.prop('multiple')) {
            options.closeOnSelect = false;
        }

        $el.select2(options);
        $el.css('width', '100%');
    }

    window.destroyLsSelect2 = function (scope) {
        var $scope = $(scope || document);
        $scope.find('select.ls-select2, select.livewire-select2').each(function () {
            destroyOne($(this));
        });
    };

    window.initLsSelect2 = function (scope) {
        var $scope = $(scope || document);
        $scope.find('select.ls-select2, select.livewire-select2').each(function () {
            initOne($(this));
        });
    };
})(window, window.jQuery);
