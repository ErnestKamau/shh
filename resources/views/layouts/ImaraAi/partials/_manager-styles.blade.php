{{-- Manager Layout Styles (reusable for all manager/dashboard pages) --}}

/* Main container for manager */
#imara-ai-root {
    display: flex;
    height: calc(100vh - 56px); /* subtract navbar height */
    background: #fff;
    overflow: hidden;
}

/* Manager main content area */
.manager-main {
    display: flex;
    flex-direction: column;
    flex: 1;
    height: 100%;
    width: 100%;
    overflow-y: auto;
    background: #f9fafb;
    padding: 0;
}

/* Manager content wrapper */
.manager-content {
    flex: 1;
    overflow-y: auto;
    padding: 32px 24px;
}

/* Sidebar visibility states for manager */
.ai-sidebar.collapsed .sidebar-header,
.ai-sidebar.collapsed .sidebar-section-label,
.ai-sidebar.collapsed .sidebar-history > div:nth-child(n+2),
.ai-sidebar.collapsed .sidebar-footer span {
    display: none;
}

/* Ensure content scales properly */
@media (max-width: 1024px) {
    .ai-sidebar {
        position: absolute;
        left: 0;
        top: 0;
        height: 100%;
        z-index: 100;
        box-shadow: 2px 0 8px rgba(0, 0, 0, 0.1);
    }

    .ai-sidebar.collapsed {
        width: 0;
        overflow: hidden;
    }

    .manager-main {
        width: 100%;
    }
}

/* Responsive adjustments */
@media (max-width: 768px) {
    #imara-ai-root {
        flex-direction: column;
        height: auto;
    }

    .ai-sidebar {
        width: 100%;
        border-right: none;
        border-bottom: 1px solid #e5e7eb;
        max-height: 60px;
        overflow: hidden;
        flex-direction: row;
    }

    .manager-main {
        width: 100%;
    }

    .manager-content {
        padding: 24px 16px;
    }
}

/* Manager specific element styling */
.manager-content h1 {
    margin-bottom: 32px;
}

/* Smooth transitions for sidebar toggle */
.ai-sidebar,
.manager-main {
    transition: all 0.3s ease;
}
