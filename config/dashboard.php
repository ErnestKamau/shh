<?php

return [

    'cache' => [
        'store' => env('DASHBOARD_CACHE_STORE', env('CACHE_STORE', env('CACHE_DRIVER', 'file'))),
        'summary_ttl' => (int) env('DASHBOARD_SUMMARY_CACHE_TTL', 60),
        'analytics_ttl' => (int) env('DASHBOARD_ANALYTICS_CACHE_TTL', 120),
        'notifications_ttl' => (int) env('DASHBOARD_NOTIFICATIONS_CACHE_TTL', 60),
        'lists_ttl' => (int) env('DASHBOARD_LISTS_CACHE_TTL', 120),
    ],

    'recent_items_limit' => 5,

    'submission_status_buckets' => [
        'awaiting_samples' => ['submitted', 'received'],
        'in_lab_workflow' => ['in_review', 'approved'],
        'more_info_requested' => ['in_additional_info'],
        'completed' => ['completed', 'released'],
    ],

    'report_status' => 'Completed',

    'positive_feedback_rating_threshold' => 3,

];
