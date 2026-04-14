<?php

namespace App\Models;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Model;

class BatchAttachmentAnnotation extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'batch_attachment_annotations';
    
    protected $fillable = [
        'batch_attachment_id',
        'page_number',
        'annotation_type',
        'content',
        'x_position',
        'y_position',
        'width',
        'height',
        'style_data',
    ];
    
    protected $casts = [
        'style_data' => 'array',
        'x_position' => 'decimal:2',
        'y_position' => 'decimal:2',
        'width' => 'decimal:2',
        'height' => 'decimal:2',
    ];
    
    /**
     * Get the batch attachment that owns the annotation.
     */
    public function batchAttachment()
    {
        return $this->belongsTo(\App\BatchAttachment::class, 'batch_attachment_id');
    }
}
