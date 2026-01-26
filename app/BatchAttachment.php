<?php

namespace App;

use App\Models\System\SystemConfiguration;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class BatchAttachment extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
    protected $table = 'batch_attachments';
    protected $appends = ['uploaduser','attachtypename'];
    public function getUploadUserAttribute(){
        return User::find($this->uploaded_by)->name ?? '';
    }
    public function getAttachtypenameAttribute(){
        return SystemConfiguration::find($this->attachment_type)->value ?? 'General';
    }
    
    /**
     * Get all annotations for this attachment.
     */
    public function annotations()
    {
        return $this->hasMany(\App\Models\BatchAttachmentAnnotation::class, 'batch_attachment_id');
    }
}
