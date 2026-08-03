<script>
(() => {
    const selector = '[data-submission-form-conditional]';

    const decodeConditions = (element) => {
        try {
            return JSON.parse(atob(element.dataset.submissionFormConditional || '')) || [];
        } catch (error) {
            console.warn('Unable to decode submission form conditional logic.', error);
            return [];
        }
    };

    const normalizeConditions = (logic) => {
        if (!logic || typeof logic !== 'object') {
            return [];
        }

        if (!Array.isArray(logic)) {
            return [logic];
        }

        return logic;
    };

    const baseFieldName = (name) => String(name || '').split('[')[0];

    const controlsForField = (wrapper, fieldName) => {
        const row = wrapper.closest('tr');
        const form = wrapper.closest('form') || document;
        const scopes = row ? [row, form] : [form];

        for (const scope of scopes) {
            const controls = Array.from(scope.querySelectorAll('input[name], select[name], textarea[name]'))
                .filter((control) => baseFieldName(control.name) === fieldName);

            if (controls.length > 0) {
                return controls;
            }
        }

        return [];
    };

    const fieldValue = (wrapper, fieldName) => {
        const controls = controlsForField(wrapper, fieldName);
        if (controls.length === 0) {
            return '';
        }

        const first = controls[0];
        if (first.type === 'radio') {
            return controls.find((control) => control.checked)?.value ?? '';
        }

        if (first.type === 'checkbox') {
            const checkedValues = controls
                .filter((control) => control.checked)
                .map((control) => control.value || '1');

            return controls.length === 1 ? (checkedValues[0] ?? '') : checkedValues;
        }

        if (first instanceof HTMLSelectElement && first.multiple) {
            return Array.from(first.selectedOptions).map((option) => option.value);
        }

        return first.value ?? '';
    };

    const matches = (actual, operator, expected) => {
        const actualValues = Array.isArray(actual) ? actual.map(String) : [String(actual ?? '')];
        const expectedValue = String(expected ?? '');

        if (operator === '==' || operator === 'equals') {
            return actualValues.includes(expectedValue);
        }

        if (operator === '!=' || operator === 'not_equals') {
            return !actualValues.includes(expectedValue);
        }

        if (operator === 'not_empty') {
            return actualValues.some((value) => value !== '');
        }

        if (operator === 'empty') {
            return actualValues.every((value) => value === '');
        }

        if (operator === 'contains') {
            return actualValues.some((value) => value.includes(expectedValue));
        }

        return true;
    };

    const resolveParentName = (condition) => {
        if (condition.depends_on || condition.field) {
            return condition.depends_on || condition.field;
        }

        if (!condition.field_id) {
            return null;
        }

        return document.querySelector(`[data-submission-form-element-id="${condition.field_id}"]`)
            ?.dataset.submissionFormElementName ?? null;
    };

    const setVisible = (wrapper, isVisible) => {
        wrapper.hidden = !isVisible;
        wrapper.classList.toggle('d-none', !isVisible);

        wrapper.querySelectorAll('input, select, textarea, button').forEach((control) => {
            if (!isVisible && !control.disabled) {
                control.dataset.conditionalLogicDisabled = '1';
                control.disabled = true;
            } else if (isVisible && control.dataset.conditionalLogicDisabled === '1') {
                control.disabled = false;
                delete control.dataset.conditionalLogicDisabled;
            }
        });
    };

    const evaluate = () => {
        document.querySelectorAll(selector).forEach((wrapper) => {
            const conditions = normalizeConditions(decodeConditions(wrapper));
            const isVisible = conditions.every((condition) => {
                if (!condition || typeof condition !== 'object') {
                    return true;
                }

                const parentName = resolveParentName(condition);
                if (!parentName) {
                    return true;
                }

                return matches(
                    fieldValue(wrapper, parentName),
                    condition.operator || 'equals',
                    condition.value ?? ''
                );
            });

            setVisible(wrapper, isVisible);
        });

        document.dispatchEvent(new CustomEvent('submission-form:conditional-logic-updated'));
    };

    document.addEventListener('input', evaluate);
    document.addEventListener('change', evaluate);
    document.addEventListener('DOMContentLoaded', evaluate);

    new MutationObserver((mutations) => {
        const addedConditionalFields = mutations.some((mutation) =>
            Array.from(mutation.addedNodes).some((node) =>
                node instanceof Element
                && (node.matches(selector) || node.querySelector(selector))
            )
        );

        if (addedConditionalFields) {
            evaluate();
        }
    }).observe(document.documentElement, {
        childList: true,
        subtree: true,
    });
})();
</script>
