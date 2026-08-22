{{-- Shared Alpine.data('rftParamPickerUi') for TRF / planner test pickers --}}
    Alpine.data('rftParamPickerUi', (config = {}) => {
        const nestCache = { key: '', groupsRef: null, value: null };
        const flatCache = { key: '', groupsRef: null, value: null };

        const toTest = (raw) => {
            if (raw && typeof raw === 'object') {
                return {
                    id: String(raw.id ?? ''),
                    name: String(raw.name ?? raw.id ?? ''),
                    analysis_type_id: String(raw.analysis_type_id ?? ''),
                    analysis_type: String(raw.analysis_type ?? ''),
                    lab_section_code: String(raw.lab_section_code ?? ''),
                    reporting_unit: String(raw.reporting_unit ?? ''),
                    lod: String(raw.lod ?? ''),
                    loq: String(raw.loq ?? ''),
                    mu: String(raw.mu ?? ''),
                    tat: String(raw.tat ?? ''),
                    method: String(raw.method ?? ''),
                };
            }

            const value = String(raw ?? '');
            return { id: value, name: value, analysis_type_id: '', analysis_type: '', lab_section_code: '', reporting_unit: '', lod: '', loq: '', mu: '', tat: '', method: '' };
        };
        const normalizeOptions = (list) => (Array.isArray(list) ? list.map(toTest).filter((item) => item.id !== '') : []);
        const normalizeGroups = (list) => (Array.isArray(list) ? list.map((group) => ({
            ...group,
            tests: normalizeOptions(group?.tests),
        })) : []);

        const mountTestsLottie = () => {};

        return {
        open: false,
        openUp: false,
        search: '',
        filtersOpen: false,
        filterAnalysisTypes: [],
        filterLabSections: [],
        filterMethods: [],
        syncing: false,
        dirty: false,
        analysisLoading: false,
        rowIndex: config.rowIndex ?? 0,
        flat: !!config.flat,
        useModal: config.useModal !== false,
        fieldId: config.fieldId ?? '',
        syncMethod: config.syncMethod ?? 'setWalkInParameters',
        stateMethod: config.stateMethod ?? 'walkInParameterPickerState',
        livewireComponentId: config.livewireComponentId ?? null,
        options: normalizeOptions(config.options),
        persistedSelected: Array.isArray(config.selected) ? config.selected.map(String) : [],
        persistedParamNames: Array.isArray(config.paramNames) ? config.paramNames.map(String) : [],
        selected: Array.isArray(config.selected) ? config.selected.map(String) : [],
        groups: normalizeGroups(config.groups),
        hydrating: false,
        init() {
            if (!this.livewireComponentId) {
                this.livewireComponentId = this.$el?.closest?.('[wire\\:id]')?.getAttribute('wire:id') ?? null;
            }

            if (!window.flushWalkInParameterPickers) {
                window.flushWalkInParameterPickers = async function () {
                    const pickers = document.querySelectorAll('.rft-tests-picker');
                    const tasks = [];
                    pickers.forEach((el) => {
                        const ui = el._x_dataStack?.[0];
                        if (!ui) {
                            return;
                        }
                        if (ui.dirty && typeof ui.flushIfDirty === 'function') {
                            tasks.push(ui.flushIfDirty());
                            return;
                        }
                        if (typeof ui.persistToServer === 'function') {
                            tasks.push(ui.persistToServer());
                        }
                    });
                    await Promise.all(tasks);
                };
            }

            this.$watch('search', () => {
                if (this.open) {
                    this.scheduleAnalyteTableRender();
                }
            });

            this.$watch('open', (isOpen) => {
                if (isOpen) {
                    this.scheduleAnalyteTableRender();
                }
            });

            this.$watch('isLoading', (loading) => {
                if (!loading && this.open) {
                    this.scheduleAnalyteTableRender();
                }
            });
        },
        setPersistedSelected(next, names = null) {
            this.persistedSelected = Array.isArray(next) ? next.map(String) : [];
            if (Array.isArray(names)) {
                this.persistedParamNames = names.map(String);
            } else if (names === null) {
                const labels = this.optionLabelMap();
                this.persistedParamNames = this.persistedSelected.map((id) => labels[String(id)] || String(id));
            }
            flatCache.value = null;
            nestCache.value = null;
        },
        setSelected(next) {
            this.selected = Array.isArray(next) ? next.map(String) : [];
        },
        invalidateFilterCaches() {
            nestCache.value = null;
            flatCache.value = null;
            this.scheduleAnalyteTableRender();
        },
        toggleFilters() {
            this.filtersOpen = !this.filtersOpen;
        },
        closeFilters() {
            this.filtersOpen = false;
        },
        toggleFilterValue(key, value) {
            const token = String(value);
            const list = Array.isArray(this[key]) ? this[key].slice() : [];
            const index = list.indexOf(token);
            if (index === -1) {
                list.push(token);
            } else {
                list.splice(index, 1);
            }
            this[key] = list;
            this.invalidateFilterCaches();
        },
        clearFilters() {
            this.search = '';
            this.filterAnalysisTypes = [];
            this.filterLabSections = [];
            this.filterMethods = [];
            this.filtersOpen = false;
            this.invalidateFilterCaches();
        },
        get activeFilterCount() {
            return (this.filterAnalysisTypes?.length || 0)
                + (this.filterLabSections?.length || 0)
                + (this.filterMethods?.length || 0);
        },
        get filterOptions() {
            const analysisTypes = {};
            const labSections = {};
            const methods = {};

            (this.groups || []).forEach((group) => {
                const label = String(group.analysis_type || '').trim();
                const id = String(group.analysis_type_id || label);
                if (label) {
                    analysisTypes[id] = label;
                }
                (group.tests || []).forEach((test) => {
                    const lab = String(test.lab_section_code || '').trim();
                    if (lab) {
                        labSections[lab] = lab;
                    }
                    const method = String(test.method || '').trim();
                    if (method) {
                        methods[method] = method;
                    }
                });
            });

            const toList = (map) => Object.entries(map)
                .map(([value, label]) => ({ value, label }))
                .sort((a, b) => a.label.localeCompare(b.label));

            return {
                analysisTypes: toList(analysisTypes),
                labSections: toList(labSections),
                methods: toList(methods),
            };
        },
        testMatchesStructuredFilters(test) {
            if (this.filterAnalysisTypes.length) {
                const typeId = String(test.analysis_type_id || test.analysis_type || '');
                const typeLabel = String(test.analysis_type || '');
                const matches = this.filterAnalysisTypes.some((token) => token === typeId || token === typeLabel);
                if (!matches) {
                    return false;
                }
            }
            if (this.filterLabSections.length) {
                const lab = String(test.lab_section_code || '');
                if (!this.filterLabSections.includes(lab)) {
                    return false;
                }
            }
            if (this.filterMethods.length) {
                const method = String(test.method || '');
                if (!this.filterMethods.includes(method)) {
                    return false;
                }
            }

            return true;
        },
        normalizeSelectedTokens(tokens) {
            const list = Array.isArray(tokens) ? tokens.map(String) : [];
            if (!list.length) {
                return [];
            }

            const knownIds = {};
            const idsByName = {};
            const register = (test) => {
                const id = String(test?.id ?? '');
                const name = String(test?.name ?? '').trim().toLowerCase();
                if (!id) {
                    return;
                }
                knownIds[id] = true;
                if (name) {
                    if (!idsByName[name]) {
                        idsByName[name] = [];
                    }
                    idsByName[name].push(id);
                }
            };

            (this.options || []).forEach(register);
            (this.groups || []).forEach((group) => {
                (group.tests || []).forEach(register);
            });

            const out = [];
            const seen = {};
            list.forEach((token) => {
                const value = String(token).trim();
                if (!value) {
                    return;
                }
                if (knownIds[value]) {
                    if (!seen[value]) {
                        seen[value] = true;
                        out.push(value);
                    }
                    return;
                }
                (idsByName[value.toLowerCase()] || []).forEach((id) => {
                    if (!seen[id]) {
                        seen[id] = true;
                        out.push(id);
                    }
                });
            });

            return out;
        },
        optionLabelMap() {
            const map = {};
            (this.options || []).forEach((option) => {
                map[String(option.id)] = String(option.name || option.id);
            });
            (this.groups || []).forEach((group) => {
                (group.tests || []).forEach((test) => {
                    map[String(test.id)] = String(test.name || test.id);
                });
            });
            return map;
        },
        get nestedFilteredGroups() {
            const query = String(this.search || '').trim().toLowerCase();
            const filterKey = [
                this.filterAnalysisTypes.join('|'),
                this.filterLabSections.join('|'),
                this.filterMethods.join('|'),
            ].join('::');
            const key = `${query}::${filterKey}::${this.groups.length}`;
            if (
                nestCache.value
                && nestCache.key === key
                && nestCache.groupsRef === this.groups
            ) {
                return nestCache.value;
            }

            const nested = [];
            const map = {};
            const groups = Array.isArray(this.groups) ? this.groups : [];

            groups.forEach((group) => {
                const tests = normalizeOptions(group.tests);
                let filteredTests = tests.filter((test) => this.testMatchesStructuredFilters({
                    ...test,
                    analysis_type_id: test.analysis_type_id || group.analysis_type_id,
                    analysis_type: test.analysis_type || group.analysis_type,
                }));

                if (query) {
                    filteredTests = filteredTests.filter((test) => {
                        const hay = [
                            test.name,
                            test.method,
                            test.analysis_type || group.analysis_type,
                            test.lab_section_code,
                            test.reporting_unit,
                        ].join(' ').toLowerCase();
                        return hay.includes(query);
                    });
                }

                if (!filteredTests.length && (query || this.activeFilterCount > 0)) {
                    return;
                }

                const sampleType = String(group.sample_type || 'Sample type');
                if (!map[sampleType]) {
                    map[sampleType] = {
                        sample_type: sampleType,
                        groups: [],
                    };
                    nested.push(map[sampleType]);
                }

                map[sampleType].groups.push({
                    analysis_type_id: group.analysis_type_id,
                    analysis_type: group.analysis_type,
                    sample_type: sampleType,
                    tests,
                    filteredTests,
                });
            });

            nestCache.value = nested;
            nestCache.key = key;
            nestCache.groupsRef = this.groups;

            return nested;
        },
        get flatTableRows() {
            if (!this.open) {
                return [];
            }
            const query = String(this.search || '').trim().toLowerCase();
            const filterKey = [
                this.filterAnalysisTypes.join('|'),
                this.filterLabSections.join('|'),
                this.filterMethods.join('|'),
            ].join('::');
            const cacheKey = `${query}::${filterKey}::${this.groups.length}`;
            if (
                flatCache.value
                && flatCache.key === cacheKey
                && flatCache.groupsRef === this.groups
            ) {
                return flatCache.value;
            }

            const rows = [];
            const nested = this.nestedFilteredGroups;

            nested.forEach((sampleBlock) => {
                if (nested.length > 1) {
                    rows.push({
                        type: 'sample',
                        key: `s-${sampleBlock.sample_type}`,
                        label: sampleBlock.sample_type || 'Sample type',
                    });
                }

                sampleBlock.groups.forEach((group) => {
                    const tests = this.groupTests(group);
                    if (!tests.length) {
                        return;
                    }

                    const groupRowKey = `${sampleBlock.sample_type}::${this.groupKey(group)}`;
                    rows.push({
                        type: 'group',
                        key: `g-${groupRowKey}`,
                        group,
                        label: group.analysis_type || 'Analysis type',
                        count: this.groupSelectedCount(group),
                        total: tests.length,
                    });

                    tests.forEach((test) => {
                        rows.push({
                            type: 'test',
                            key: `t-${groupRowKey}::${test.id}`,
                            test,
                            group,
                        });
                    });
                });
            });

            flatCache.value = rows;
            flatCache.key = cacheKey;
            flatCache.groupsRef = this.groups;

            return rows;
        },
        get allSelected() {
            return this.options.length > 0 && this.selected.length === this.options.length;
        },
        groupKey(group) {
            return String(group?.analysis_type_id || group?.analysis_type || 'group');
        },
        groupTests(group) {
            const list = Array.isArray(group?.filteredTests)
                ? group.filteredTests
                : (Array.isArray(group?.tests) ? group.tests : []);

            return normalizeOptions(list);
        },
        groupSelectedCount(group) {
            return this.groupTests(group).reduce(
                (count, test) => count + (this.isSelected(test.id) ? 1 : 0),
                0
            );
        },
        groupSelectionState(group) {
            const tests = this.groupTests(group);
            if (!tests.length) {
                return 'none';
            }
            const count = this.groupSelectedCount(group);
            if (count === 0) {
                return 'none';
            }
            if (count === tests.length) {
                return 'all';
            }

            return 'partial';
        },
        toggleGroupCheckbox(group) {
            const tests = this.groupTests(group);
            if (!tests.length) {
                return;
            }

            if (this.groupSelectionState(group) === 'all') {
                const remove = Object.create(null);
                tests.forEach((test) => {
                    remove[String(test.id)] = true;
                });
                this.setSelected(this.selected.filter((id) => !remove[id]));
            } else {
                const merge = Object.create(null);
                this.selected.forEach((id) => {
                    merge[String(id)] = true;
                });
                tests.forEach((test) => {
                    merge[String(test.id)] = true;
                });
                this.setSelected(Object.keys(merge));
            }
            this.markDirty();
            this.scheduleAnalyteTableRender();
        },
        toggleSelectAllSwitch() {
            if (this.allSelected) {
                this.clearAll();
                return;
            }
            this.selectAll();
        },
        get isLoading() {
            return this.analysisLoading || this.hydrating;
        },
        get visibleChips() {
            const labels = this.optionLabelMap();
            return this.persistedSelected.map((id, index) => ({
                id: String(id),
                name: labels[String(id)] || this.persistedParamNames[index] || String(id),
            }));
        },
        get sampleTypeHeading() {
            const fromGroup = (this.groups || []).find((group) => String(group?.sample_type || '').trim() !== '');
            if (fromGroup) {
                return String(fromGroup.sample_type);
            }

            return this.persistedSelected.length ? 'Selected tests' : 'Tests';
        },
        resolveLivewire() {
            const componentId = this.livewireComponentId
                ?? this.$el?.closest?.('[wire\\:id]')?.getAttribute?.('wire:id')
                ?? null;

            if (componentId && window.Livewire) {
                const component = window.Livewire.find(componentId);

                return component ?? null;
            }

            return null;
        },
        async callLivewireMethod(method, ...args) {
            const methodName = String(method ?? '').trim();
            if (methodName === '' || methodName === '$wire') {
                console.error('Invalid Livewire method name for picker call', method);

                return null;
            }

            const componentId = this.livewireComponentId
                ?? this.$el?.closest?.('[wire\\:id]')?.getAttribute?.('wire:id')
                ?? null;

            if (componentId && window.Livewire) {
                const component = window.Livewire.find(componentId);
                if (component && typeof component.call === 'function') {
                    return component.call(methodName, ...args);
                }
            }

            const wire = this.resolveLivewire();
            if (wire && typeof wire[methodName] === 'function') {
                return wire[methodName](...args);
            }

            if (this.$wire && typeof this.$wire[methodName] === 'function') {
                return this.$wire[methodName](...args);
            }

            return null;
        },
        syncMethodArgs(selected) {
            if (this.syncMethod === 'setEditingRowParameters') {
                return [selected];
            }

            const row = this.flat ? -1 : Number(this.rowIndex);

            return [row, selected];
        },
        stateMethodArgs() {
            if (this.stateMethod === 'editingRowParameterPickerState') {
                return [];
            }

            return [this.flat ? null : this.rowIndex];
        },
        applyPersistedPickerState(result, fallbackSelected = null) {
            const fallback = Array.isArray(fallbackSelected) ? fallbackSelected.map(String) : this.selected.slice();
            const fromServer = (result && Array.isArray(result.selected)) ? result.selected.map(String) : [];
            const saved = fromServer.length > 0 ? fromServer : fallback;
            const names = (result && Array.isArray(result.paramNames) && result.paramNames.length)
                ? result.paramNames.map(String)
                : null;

            this.setPersistedSelected(saved, names);
            this.setSelected(saved);
            this.dirty = false;
            this.dispatchTestsSavedEvent(result, saved);
        },
        dispatchTestsSavedEvent(result, savedIds = null) {
            const labels = this.optionLabelMap();
            const ids = Array.isArray(savedIds) ? savedIds : this.persistedSelected;
            const paramNames = (Array.isArray(result?.paramNames) && result.paramNames.length)
                ? result.paramNames.map(String)
                : ids.map((id) => labels[String(id)] || String(id));
            const analysisLabel = String(result?.analysisLabel || '');
            const count = Number(result?.count ?? ids.length);

            window.dispatchEvent(new CustomEvent('rft-trf-tests-saved', {
                detail: {
                    rowIndex: Number(this.rowIndex),
                    count,
                    preview: paramNames.slice(0, 2).join(', '),
                    analysisLabel,
                    paramNames,
                },
            }));
        },
        isSelected(id) {
            return this.selected.includes(String(id));
        },
        setAnalysisLoading(payload) {
            const detail = Array.isArray(payload) ? (payload[0] || {}) : (payload || {});
            const wireKey = String(detail.wireKey || '');
            const rowMatch = wireKey.match(/\.(\d+)$/);
            const targetRowIndex = rowMatch ? Number(rowMatch[1]) : -1;

            if (targetRowIndex !== Number(this.rowIndex)) {
                return;
            }

            this.analysisLoading = detail.loading === true && this.open;
            if (!detail.loading && this.open) {
                this.$nextTick(() => this.hydrateCatalogFromWire());
            }
        },
        toggleOpen() {
            if (this.open) {
                this.cancelPanel();
                return;
            }
            this.openPanel();
        },
        openPanel() {
            this.selected = this.persistedSelected.slice();
            this.dirty = false;
            this.open = true;
            void this.hydrateCatalogFromWire();
            this.$nextTick(() => {
                this.decideDirection();
                this.scheduleAnalyteTableRender();
            });
        },
        cancelPanel() {
            this.selected = this.persistedSelected.slice();
            this.dirty = false;
            this.open = false;
            this.search = '';
            this.filtersOpen = false;
        },
        async savePanel() {
            if (this.dirty) {
                await this.flushIfDirty();
            }
            this.cancelPanel();
        },
        decideDirection() {
            const rect = this.$el.getBoundingClientRect();
            this.openUp = (window.innerHeight - rect.bottom) < 320;
        },
        markDirty() {
            this.dirty = true;
        },
        toggle(id) {
            const value = String(id);
            if (this.isSelected(value)) {
                this.setSelected(this.selected.filter((item) => item !== value));
            } else {
                this.setSelected(this.selected.concat([value]));
            }
            this.markDirty();
            this.scheduleAnalyteTableRender();
        },
        removeChip(id) {
            const value = String(id);
            this.setSelected(this.selected.filter((item) => item !== value));
            this.markDirty();
        },
        selectAll() {
            this.setSelected(this.options.map((option) => String(option.id)));
            this.markDirty();
            this.scheduleAnalyteTableRender();
        },
        clearAll() {
            this.setSelected([]);
            this.search = '';
            this.markDirty();
            this.scheduleAnalyteTableRender();
        },
        syncTestsSelect2Display() {
            this.dispatchTestsSavedEvent(null);
        },
        async persistToServer() {
            if (this.dirty) {
                await this.flushIfDirty();

                return this.persistedSelected.length > 0 ? { selected: this.persistedSelected.slice() } : null;
            }

            if (!this.persistedSelected.length) {
                return null;
            }

            const selected = this.persistedSelected.slice();

            try {
                const result = await this.callLivewireMethod(this.syncMethod, ...this.syncMethodArgs(selected));
                this.applyPersistedPickerState(result, selected);

                return result;
            } catch (error) {
                console.error('Failed to persist walk-in parameters to server', error);

                return null;
            }
        },
        async flushIfDirty() {
            if (!this.dirty || this.syncing) {
                return;
            }

            if (typeof this.callLivewireMethod !== 'function') {
                return;
            }

            this.syncing = true;
            const selectedBefore = this.selected.slice();

            try {
                const result = await this.callLivewireMethod(this.syncMethod, ...this.syncMethodArgs(selectedBefore));
                this.applyPersistedPickerState(result, selectedBefore);
            } catch (error) {
                console.error('Failed to persist walk-in parameters', error);
            } finally {
                this.syncing = false;
            }
        },
        flattenWireIds(raw) {
            if (!Array.isArray(raw)) {
                return raw !== null && raw !== undefined && raw !== '' ? [String(raw)] : [];
            }

            const out = [];
            raw.forEach((item) => {
                if (Array.isArray(item)) {
                    out.push(...this.flattenWireIds(item));
                    return;
                }
                if (item !== null && item !== undefined && typeof item === 'object') {
                    const id = item.id ?? item.value ?? null;
                    if (id !== null && id !== undefined && String(id) !== '') {
                        out.push(String(id));
                    }
                    return;
                }
                if (item !== null && item !== undefined && String(item) !== '') {
                    out.push(String(item));
                }
            });

            return out;
        },
        async hydrateCatalogFromWire() {
            if (this.hydrating || this.syncing) {
                return;
            }

            if (this.groups.length > 0 && this.options.length > 0) {
                this.scheduleAnalyteTableRender();

                return;
            }

            if (!this.livewireComponentId && !this.resolveLivewire()) {
                return;
            }

            this.hydrating = true;
            try {
                const state = await Promise.race([
                    this.callLivewireMethod(this.stateMethod, ...this.stateMethodArgs()),
                    new Promise((_, reject) => {
                        window.setTimeout(() => reject(new Error('Parameter catalog hydrate timed out')), 12000);
                    }),
                ]);

                if (state && Array.isArray(state.options)) {
                    this.options = normalizeOptions(state.options);
                }
                if (state && Array.isArray(state.groups)) {
                    this.groups = normalizeGroups(state.groups);
                    nestCache.value = null;
                    flatCache.value = null;
                }

                if (!this.open && !this.dirty && state && Array.isArray(state.selected)) {
                    this.setPersistedSelected(state.selected.map((value) => String(value)));
                    this.setSelected(this.persistedSelected.slice());
                    this.syncTestsSelect2Display();
                }
            } catch (error) {
                console.error('Failed to hydrate walk-in parameter catalog', error);
            } finally {
                this.hydrating = false;
                this.scheduleAnalyteTableRender();
            }
        },
        scheduleAnalyteTableRender(attempt = 0) {
            this.$nextTick(() => {
                this.$nextTick(() => {
                    const rendered = this.renderAnalyteTable();
                    if (!rendered && this.open && attempt < 10) {
                        window.requestAnimationFrame(() => this.scheduleAnalyteTableRender(attempt + 1));
                    }
                });
            });
        },
        resolveAnalyteTableBody() {
            if (this.$refs.analyteTableBody) {
                return this.$refs.analyteTableBody;
            }

            const root = this.$el?.querySelector?.('.rft-trf-params-modal__dialog[aria-label="Choose tests"]')
                ?? document.querySelector('.rft-trf-params-modal__dialog[aria-label="Choose tests"]');

            return root?.querySelector('tbody.rft-trf-analyte-tbody') ?? null;
        },
        renderAnalyteTable() {
            const tbody = this.resolveAnalyteTableBody();
            if (!tbody || !this.open) {
                return false;
            }

            if (this.isLoading && !this.groups.length) {
                return false;
            }

            const rows = this.flatTableRows;
            const fragment = document.createDocumentFragment();

            rows.forEach((row) => {
                if (row.type === 'sample') {
                    const tr = document.createElement('tr');
                    tr.className = 'rft-trf-analyte-sample';
                    const td = document.createElement('td');
                    td.colSpan = 7;
                    td.textContent = row.label || 'Sample type';
                    tr.appendChild(td);
                    fragment.appendChild(tr);
                    return;
                }

                if (row.type === 'group') {
                    const tr = document.createElement('tr');
                    tr.className = 'ls-quote-analyte-group';
                    const td = document.createElement('td');
                    td.colSpan = 7;

                    const inner = document.createElement('div');
                    inner.className = 'rft-trf-analyte-group__inner';

                    const label = document.createElement('span');
                    label.className = 'rft-trf-analyte-group__label';
                    label.textContent = row.label || 'Analysis type';

                    const count = document.createElement('span');
                    count.className = 'rft-trf-analyte-group__count';
                    const selectedInGroup = this.groupSelectedCount(row.group);
                    const totalInGroup = this.groupTests(row.group).length;
                    count.textContent = `(${selectedInGroup}/${totalInGroup})`;

                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'rft-trf-analyte-group__toggle';
                    button.textContent = this.groupSelectionState(row.group) === 'all' ? 'Clear group' : 'Select group';
                    button.addEventListener('click', (event) => {
                        event.preventDefault();
                        event.stopPropagation();
                        this.toggleGroupCheckbox(row.group);
                    });

                    inner.append(label, count, button);
                    td.appendChild(inner);
                    tr.appendChild(td);
                    fragment.appendChild(tr);
                    return;
                }

                if (row.type !== 'test' || !row.test) {
                    return;
                }

                const test = row.test;
                const tr = document.createElement('tr');
                tr.className = 'ls-quote-analyte-row';
                if (this.isSelected(test.id)) {
                    tr.classList.add('is-selected');
                }

                tr.addEventListener('click', () => {
                    this.toggle(test.id);
                });

                const tdSelect = document.createElement('td');
                tdSelect.className = 'ls-quote-analyte-row__select';
                tdSelect.addEventListener('click', (event) => event.stopPropagation());

                const label = document.createElement('label');
                label.className = 'ls-quote-check';
                label.addEventListener('click', (event) => event.stopPropagation());

                const input = document.createElement('input');
                input.type = 'checkbox';
                input.className = 'rft-trf-analyte-check';
                input.checked = this.isSelected(test.id);
                input.addEventListener('change', (event) => {
                    event.preventDefault();
                    event.stopPropagation();
                    this.toggle(test.id);
                });

                const box = document.createElement('span');
                box.className = 'ls-quote-check__box';
                box.setAttribute('aria-hidden', 'true');

                label.append(input, box);
                tdSelect.appendChild(label);

                const tdName = document.createElement('td');
                tdName.className = 'ls-quote-analyte-row__name';
                const nameInner = document.createElement('div');
                nameInner.className = 'ls-quote-analyte-row__name-inner';

                const nameLabel = document.createElement('span');
                nameLabel.className = 'ls-quote-analyte-row__label';
                nameLabel.textContent = test.name || test.id || '';
                nameLabel.title = test.name || test.id || '';

                const meta = document.createElement('span');
                meta.className = 'ls-quote-analyte-row__meta';

                if (test.lab_section_code) {
                    const labPill = document.createElement('span');
                    labPill.className = 'ls-quote-meta-pill ls-quote-meta-pill--lab';
                    labPill.textContent = test.lab_section_code;
                    labPill.title = `Lab section: ${test.lab_section_code}`;
                    meta.appendChild(labPill);
                }

                if (test.method) {
                    const methodPill = document.createElement('span');
                    methodPill.className = 'ls-quote-meta-pill ls-quote-meta-pill--method';
                    methodPill.textContent = test.method;
                    methodPill.title = `Method: ${test.method}`;
                    meta.appendChild(methodPill);
                }

                nameInner.append(nameLabel, meta);
                tdName.appendChild(nameInner);

                const metricCells = [
                    { className: 'ls-quote-analyte-row__metric text-center', value: this.formatMetric(test.reporting_unit), metricClass: 'ls-quote-metric' },
                    { className: 'ls-quote-analyte-row__metric text-center', value: this.formatMetric(test.lod), metricClass: 'ls-quote-metric' },
                    { className: 'ls-quote-analyte-row__metric text-center', value: this.formatMetric(test.loq), metricClass: 'ls-quote-metric' },
                    { className: 'ls-quote-analyte-row__mu text-center', value: this.formatMetric(test.mu), metricClass: 'ls-quote-metric' },
                    { className: 'ls-quote-analyte-row__tat text-center', value: this.formatTat(test.tat), metricClass: 'ls-quote-tat', title: 'Turnaround time (days)' },
                ];

                tr.appendChild(tdSelect);
                tr.appendChild(tdName);

                metricCells.forEach((cell) => {
                    const td = document.createElement('td');
                    td.className = cell.className;
                    if (cell.title) {
                        td.title = cell.title;
                    }
                    const span = document.createElement('span');
                    span.className = cell.metricClass;
                    span.textContent = cell.value;
                    td.appendChild(span);
                    tr.appendChild(td);
                });

                fragment.appendChild(tr);
            });

            tbody.replaceChildren(fragment);

            return rows.length > 0;
        },
        formatMetric(value) {
            const text = String(value ?? '').trim();
            return text !== '' ? text : '—';
        },
        formatTat(value) {
            const text = String(value ?? '').trim();
            return text !== '' ? `${text}d` : '—';
        },
        };
    });
