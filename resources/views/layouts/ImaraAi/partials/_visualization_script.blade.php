// ── Dedicated Visualization & BI Tool Scripts ────────────────────────────────

// State trackers for active modal visualizations
let modalChartInstance = null;
let currentConfigId = null;

// Initialise any chart canvases that were injected by renderMarkdown
window.initCharts = function(container) {
    if (typeof Chart === 'undefined') return;
    container.querySelectorAll('canvas[data-config-id], canvas[data-chart]').forEach(canvas => {
        try {
            let configId = canvas.dataset.configId;
            let rawJson = configId ? window.chartConfigs[configId] : null;
            if (!rawJson && canvas.dataset.chart) {
                rawJson = canvas.dataset.chart;
                configId = `cfg-${Date.now()}-${Math.random().toString(36).slice(2)}`;
                window.chartConfigs = window.chartConfigs || {};
                window.chartConfigs[configId] = rawJson;
                canvas.dataset.configId = configId;
            }
            if (!rawJson) return;
            const cfg = JSON.parse(rawJson);
            
            const palette = ['#a72b2a','#3b82f6','#10b981','#f59e0b','#8b5cf6','#ec4899','#06b6d4','#84cc16','#f97316','#6366f1'];
            const datasets = (cfg.datasets || []).map((ds, i) => {
                const dataPoints = Array.isArray(ds.data) ? ds.data : [];
                return {
                    ...ds,
                    data: dataPoints,
                    backgroundColor: dataPoints.map((_, j) => palette[(i * 3 + j) % palette.length] + 'cc'),
                    borderColor:     dataPoints.map((_, j) => palette[(i * 3 + j) % palette.length]),
                    borderWidth: 1,
                    borderRadius: 4,
                };
            });

            new Chart(canvas, {
                type: cfg.type || 'bar',
                data: { labels: cfg.labels || [], datasets },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: datasets.length > 1, position: 'bottom' },
                        title: { display: !!cfg.title, text: cfg.title || '' },
                    },
                    scales: cfg.type === 'pie' || cfg.type === 'doughnut' ? {} : { 
                        y: { beginAtZero: true, grid: { color: '#f0f0f0' } },
                        x: { grid: { display: false } }
                    }
                }
            });
        } catch (e) {
            console.warn('Imara AI: failed to render chart', e);
        }
    });
};

// ── Chart Modal Handlers ────────────────────────────────────────────────────
window.openChartModal = function(configId) {
    currentConfigId = configId;
    const rawJson = window.chartConfigs[configId];
    if (!rawJson) return;
    
    try {
        const cfg = JSON.parse(rawJson);
        const modal = document.getElementById('chartModal');
        const canvas = document.getElementById('modalChartCanvas');
        const titleEl = document.getElementById('chartModalTitle');
        const dataEl = document.getElementById('modalChartData');
        
        if (!modal || !canvas) return;

        titleEl.textContent = cfg.title || 'Visualization Analysis';
        
        // Destroy previous instance
        if (modalChartInstance) {
            modalChartInstance.destroy();
        }

        modal.style.display = 'flex';
        document.body.classList.add('modal-open');

        // Render Chart
        const palette = ['#a72b2a','#3b82f6','#10b981','#f59e0b','#8b5cf6','#ec4899','#06b6d4','#84cc16','#f97316','#6366f1'];
        const datasets = (cfg.datasets || []).map((ds, i) => {
            const dataPoints = Array.isArray(ds.data) ? ds.data : [];
            return {
                ...ds,
                data: dataPoints,
                backgroundColor: dataPoints.map((_, j) => palette[(i * 3 + j) % palette.length] + 'cc'),
                borderColor:     dataPoints.map((_, j) => palette[(i * 3 + j) % palette.length]),
                borderWidth: 2,
                borderRadius: 6,
            };
        });

        modalChartInstance = new Chart(canvas, {
            type: cfg.type || 'bar',
            data: { labels: cfg.labels || [], datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: true, position: 'bottom' },
                    title: { display: false }
                },
                scales: cfg.type === 'pie' || cfg.type === 'doughnut' ? {} : { 
                    y: { beginAtZero: true, grid: { color: '#f0f0f0' } },
                    x: { grid: { display: false } }
                }
            }
        });

        // Render Table (Drill-down)
        if (cfg.labels && cfg.datasets && cfg.datasets[0]) {
            let html = '<table class="drilldown-table"><thead><tr><th>Category</th>';
            cfg.datasets.forEach(ds => {
                html += `<th>${escapeHtml(ds.label || 'Value')}</th>`;
            });
            html += '</tr></thead><tbody>';
            
            cfg.labels.forEach((l, i) => {
                html += `<tr><td>${escapeHtml(l)}</td>`;
                cfg.datasets.forEach(ds => {
                    html += `<td><strong>${ds.data[i]}</strong></td>`;
                });
                html += '</tr>';
            });
            html += '</tbody></table>';
            dataEl.innerHTML = html;
        }

    } catch (e) {
        console.error('Failed to open chart modal', e);
    }
};

window.exportChartCSV = function() {
    if (!currentConfigId) return;
    const cfg = JSON.parse(window.chartConfigs[currentConfigId]);
    
    let csv = 'Category,' + cfg.datasets.map(ds => (ds.label || 'Value')).join(',') + '\n';
    cfg.labels.forEach((l, i) => {
        csv += `"${l}",` + cfg.datasets.map(ds => ds.data[i]).join(',') + '\n';
    });

    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `imara-data-export-${Date.now()}.csv`;
    a.click();
};

window.exportChartPDF = function() {
    window.print();
};

window.closeChartModal = function() {
    const modal = document.getElementById('chartModal');
    if (modal) modal.style.display = 'none';
    document.body.classList.remove('modal-open');
};

window.toggleDrilldownTable = function() {
    const wrap = document.getElementById('drilldownTableWrap');
    const chev = document.getElementById('drilldownChevron');
    if (wrap && chev) {
        const isCollapsed = wrap.classList.contains('collapsed');
        if (isCollapsed) {
            wrap.classList.remove('collapsed');
            chev.className = 'mdi mdi-chevron-up';
        } else {
            wrap.classList.add('collapsed');
            chev.className = 'mdi mdi-chevron-down';
        }
    }
};

// ── Event Listeners for Modal Interactions ───────────────────────────
document.getElementById('closeChartModal')?.addEventListener('click', closeChartModal);

document.getElementById('chartModal')?.addEventListener('click', (e) => {
    if (e.target === document.getElementById('chartModal')) {
        closeChartModal();
    }
});

// ESC key listener to safely dismiss visualization modals
window.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeChartModal();
});
