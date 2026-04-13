<div class="crm-table-wrap" {{ $attributes }}>
    <div class="table-responsive">
        <table class="table crm-table table-striped table-hover mb-0">
            <thead>
                {{ $header }}
            </thead>
            <tbody>
                {{ $slot }}
            </tbody>
        </table>
    </div>
</div>
