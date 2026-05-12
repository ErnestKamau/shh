<?php

namespace App\Constants\CRM;

class CrmConstants
{
    // Permissions
    const PERMISSION_COMPLAINT_VIEW = 'crm.complaints.view';
    const PERMISSION_COMPLAINT_ADD = 'crm.complaints.add';
    const PERMISSION_COMPLAINT_EDIT = 'crm.complaints.edit';
    const PERMISSION_COMPLAINT_RESOLUTION_ADD = 'crm.complaints-resolution.add';

    // Complaint Priorities
    const PRIORITY_HIGH = 'High';
    const PRIORITY_MEDIUM = 'Medium';
    const PRIORITY_LOW = 'Low';

    // Complaint Workflow
    const WORKFLOW_STAGE_ALL = 0;

    // Actions
    const ACTION_CREATE_RESOLUTION = 'Create Resolution';

    public static function getPriorities()
    {
        return [
            self::PRIORITY_HIGH,
            self::PRIORITY_MEDIUM,
            self::PRIORITY_LOW,
        ];
    }
}
