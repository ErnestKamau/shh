@props(['plainRows' => false])

<div {{ $attributes->class(['crm-table-wrap', 'crm-table-wrap--plain-rows' => $plainRows]) }}>
    <div class="table-responsive">
        <table @class([
            'table',
            'crm-table',
            'mb-0',
            'table-hover' => true,
            'table-striped' => ! $plainRows,
        ])>
            <thead>
                {{ $header }}
            </thead>
            <tbody>
                {{ $slot }}
            </tbody>
        </table>
    </div>
</div>
