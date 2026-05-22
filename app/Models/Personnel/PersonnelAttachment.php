<?php

namespace App\Models\Personnel;

use App\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonnelAttachment extends Model
{
    use HasFactory;

    protected $table = 'personnel_attachments';

    protected $fillable = [
        'user_id',
        'file_name',
        'file_path',
        'uploaded_by',
    ];

    public function personnel()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
