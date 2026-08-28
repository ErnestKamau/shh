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

        const ensureLottieScript = () => {
            if (typeof lottie !== 'undefined') {
                return Promise.resolve();
            }
            if (window.__rftTestsLottiePromise) {
                return window.__rftTestsLottiePromise;
            }
            window.__rftTestsLottiePromise = new Promise((resolve) => {
                if (window.__rftTestsLottieLoaded) {
                    const wait = () => {
                        if (typeof lottie !== 'undefined') {
                            resolve();
                            return;
                        }
                        setTimeout(wait, 50);
                    };
                    wait();
                    return;
                }
                window.__rftTestsLottieLoaded = true;
                const script = document.createElement('script');
                script.src = 'https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.12.2/lottie.min.js';
                script.onload = () => resolve();
                script.onerror = () => resolve();
                document.head.appendChild(script);
            });
            return window.__rftTestsLottiePromise;
        };

        const mountTestsLottie = (el) => {
            if (!el || el.getAttribute('data-rft-lottie-ready') === '1') {
                return;
            }
            const src = el.getAttribute('data-rft-tests-lottie') || el.getAttribute('data-ls-lottie');
            if (!src) {
                return;
            }
            if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                el.setAttribute('data-rft-lottie-ready', '1');
                return;
            }

            const fallback = el.querySelector('[data-rft-lottie-fallback]');
            ensureLottieScript().then(() => {
                if (typeof lottie === 'undefined') {
                    return;
                }
                if (el.getAttribute('data-rft-lottie-ready') === '1') {
                    return;
                }
                try {
                    el.setAttribute('data-rft-lottie-ready', '1');
                    const anim = lottie.loadAnimation({
                        container: el,
                        renderer: 'svg',
                        loop: true,
                        autoplay: true,
                        path: src,
                    });
                    anim.addEventListener('DOMLoaded', () => {
                        if (fallback) {
                            fallback.style.display = 'none';
                        }
                    });
                    anim.addEventListener('data_failed', () => {
                        el.removeAttribute('data-rft-lottie-ready');
                        if (fallback) {
                            fallback.style.display = '';
                        }
                    });
                } catch (e) {
                    el.removeAttribute('data-rft-lottie-ready');
                    if (fallback) {
                        fallback.style.display = '';
                    }
                }
            });
        };

        return {
        open: false,
        openUp: false,
        search: '',
        filtersOpen: false,
        filterAnalysisTypes: [],
        filterLabSections: [],
        labSectionFilterQuery: '',
        labSectionFilterOpen: false,
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
        mountTestsLottie,
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

            this.$watch('groups', () => {
                nestCache.value = null;
                flatCache.value = null;
            });
        },
        applyParamsRowReset(rawPayload) {
            const payload = Array.isArray(rawPayload) ? (rawPayload[0] || {}) : (rawPayload || {});
            if (!this.flat && payload.rowIndex !== undefined && Number(payload.rowIndex) !== Number(this.rowIndex)) {
                return;
            }

            const preserveDraft = this.open && this.dirty;
            const freezeCatalog = this.open && this.dirty && (this.groups.length > 0 || this.options.length > 0);

            // While Choose tests is open with a draft, keep the in-memory catalog stable.
            if (payload.deferCatalog) {
                if (this.open) {
                    if (!freezeCatalog) {
                        void this.hydrateCatalogFromWire(true);
                    }
                    return;
                }
                this.groups = [];
                this.options = [];
                this.setSelected([]);
                this.setPersistedSelected([]);
                this.dirty = false;
                return;
            }

            if (!freezeCatalog && Array.isArray(payload.options)) {
                this.options = normalizeOptions(payload.options);
            }
            if (!freezeCatalog && Array.isArray(payload.groups)) {
                this.groups = normalizeGroups(payload.groups);
                nestCache.value = null;
                flatCache.value = null;
            }
            if (!preserveDraft && 'selected' in payload && Array.isArray(payload.selected)) {
                const ids = payload.selected.map((value) => String(value));
                this.setSelected(ids);
                this.setPersistedSelected(ids, Array.isArray(payload.paramNames) ? payload.paramNames : null);
            } else if (!preserveDraft && 'paramNames' in payload && Array.isArray(payload.paramNames)) {
                this.persistedParamNames = payload.paramNames.map((value) => String(value));
            }

            if (!preserveDraft) {
                this.dirty = false;
            }
            this.syncTestsSelect2Display();
            if (this.open && !freezeCatalog && (!this.groups.length || !this.options.length)) {
                void this.hydrateCatalogFromWire(true);
            }
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
        },
        toggleFilters() {
            this.filtersOpen = !this.filtersOpen;
            if (!this.filtersOpen) {
                this.labSectionFilterOpen = false;
            }
        },
        closeFilters() {
            this.filtersOpen = false;
            this.labSectionFilterOpen = false;
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
            if (key === 'filterAnalysisTypes') {
                this.pruneCascadedFilters();
            }
            this.invalidateFilterCaches();
        },
        pruneCascadedFilters() {
            const labSet = Object.create(null);
            this.filterOptions.labSections.forEach((item) => {
                labSet[item.value] = true;
            });
            this.filterLabSections = (this.filterLabSections || []).filter((token) => labSet[token]);
        },
        clearFilters() {
            this.search = '';
            this.filterAnalysisTypes = [];
            this.filterLabSections = [];
            this.labSectionFilterQuery = '';
            this.labSectionFilterOpen = false;
            this.filtersOpen = false;
            this.invalidateFilterCaches();
        },
        get activeFilterCount() {
            return (this.filterAnalysisTypes?.length || 0)
                + (this.filterLabSections?.length || 0);
        },
        get hasActiveViewFilter() {
            return this.activeFilterCount > 0 || String(this.search || '').trim() !== '';
        },
        get filterOptions() {
            const analysisTypes = {};
            const labSections = {};

            (this.groups || []).forEach((group) => {
                const label = String(group.analysis_type || '').trim();
                const id = String(group.analysis_type_id || label);
                if (label) {
                    analysisTypes[id] = label;
                }

                const matchesAnalysis = !this.filterAnalysisTypes.length
                    || this.filterAnalysisTypes.some((token) => token === id || token === label);

                (group.tests || []).forEach((test) => {
                    const lab = String(test.lab_section_code || '').trim();
                    if (matchesAnalysis && lab) {
                        labSections[lab] = lab;
                    }
                });
            });

            const toList = (map) => Object.entries(map)
                .map(([value, label]) => ({ value, label }))
                .sort((a, b) => a.label.localeCompare(b.label));

            return {
                analysisTypes: toList(analysisTypes),
                labSections: toList(labSections),
            };
        },
        get useLabSectionSelect() {
            return (this.filterOptions.labSections?.length || 0) > 20;
        },
        filterChoiceList(items, query, limit = 50) {
            const list = Array.isArray(items) ? items : [];
            const q = String(query || '').trim().toLowerCase();
            const filtered = q
                ? list.filter((item) => String(item.label || item.value || '').toLowerCase().includes(q))
                : list;

            return {
                choices: filtered.slice(0, limit),
                total: filtered.length,
                truncated: filtered.length > limit,
            };
        },
        get labSectionFilterPicker() {
            return this.filterChoiceList(this.filterOptions.labSections, this.labSectionFilterQuery, 50);
        },
        openLabSectionFilter() {
            this.labSectionFilterOpen = !this.labSectionFilterOpen;
            if (!this.labSectionFilterOpen) {
                return;
            }
            this.$nextTick(() => {
                const section = this.$el?.querySelector?.('[data-rft-filter-section="lab"]');
                section?.scrollIntoView?.({ block: 'nearest', behavior: 'smooth' });
                const input = section?.querySelector?.('.rft-trf-filter-select__search input');
                input?.focus?.();
            });
        },
        labSectionFilterSummary() {
            const count = this.filterLabSections?.length || 0;
            if (!count) {
                return 'Search lab sections…';
            }

            return count === 1 ? '1 lab section selected' : `${count} lab sections selected`;
        },
        visibleSelectedFilterTags(key, max = 3) {
            const selected = Array.isArray(this[key]) ? this[key] : [];

            return selected.slice(0, max).map((value) => ({ value, label: value }));
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
            const visible = this.visibleTestIds;
            return visible.length > 0 && visible.every((id) => this.isSelected(id));
        },
        get visibleTestIds() {
            return this.flatTableRows
                .filter((row) => row.type === 'test' && row.test)
                .map((row) => String(row.test.id));
        },
        get visibleSelectedCount() {
            return this.visibleTestIds.reduce(
                (count, id) => count + (this.isSelected(id) ? 1 : 0),
                0,
            );
        },
        get visibleTestCount() {
            return this.visibleTestIds.length;
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
        },
        toggleSelectAllSwitch() {
            if (this.allSelected) {
                this.clearVisibleSelection();
                return;
            }
            this.selectAllVisible();
        },
        selectAllVisible() {
            const visible = this.visibleTestIds;
            if (!visible.length) {
                return;
            }
            const merge = Object.create(null);
            this.selected.forEach((id) => {
                merge[String(id)] = true;
            });
            visible.forEach((id) => {
                merge[String(id)] = true;
            });
            this.setSelected(Object.keys(merge));
            this.markDirty();
        },
        clearVisibleSelection() {
            const remove = Object.create(null);
            this.visibleTestIds.forEach((id) => {
                remove[String(id)] = true;
            });
            this.setSelected(this.selected.filter((id) => !remove[String(id)]));
            this.markDirty();
        },
        toolbarClearSelection() {
            if (this.hasActiveViewFilter) {
                this.clearVisibleSelection();
                return;
            }
            this.clearAll();
        },
        selectAll() {
            this.selectAllVisible();
        },
        clearAll() {
            this.setSelected([]);
            this.markDirty();
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
                this.$nextTick(() => this.hydrateCatalogFromWire(true));
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
            void this.hydrateCatalogFromWire(true);
            this.$nextTick(() => this.decideDirection());
        },
        cancelPanel() {
            this.selected = this.persistedSelected.slice();
            this.dirty = false;
            this.open = false;
            this.search = '';
            this.filtersOpen = false;
            this.analysisLoading = false;
            this.hydrating = false;
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
        },
        removeChip(id) {
            const value = String(id);
            this.setSelected(this.selected.filter((item) => item !== value));
            this.markDirty();
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
        async hydrateCatalogFromWire(force = false) {
            if (this.hydrating || this.syncing) {
                return;
            }

            const hasCatalog = this.groups.length > 0 && this.options.length > 0;

            // Freeze catalog while the user is editing selections in an open modal.
            if (this.open && this.dirty && hasCatalog) {
                return;
            }

            if (!force && hasCatalog) {
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

                const stillFreeze = this.open && this.dirty && this.groups.length > 0 && this.options.length > 0;
                if (!stillFreeze) {
                    if (state && Array.isArray(state.options)) {
                        this.options = normalizeOptions(state.options);
                    }
                    if (state && Array.isArray(state.groups)) {
                        this.groups = normalizeGroups(state.groups);
                        nestCache.value = null;
                        flatCache.value = null;
                    }
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
            }
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
