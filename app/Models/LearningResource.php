<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LearningResource extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'file_path',
        'resource_type',
        'uploaded_by',
        'class_id',
        'subject',
    ];

    /**
     * Relationship: Resource uploaded by a teacher
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Relationship: Resource belongs to a class
     */
    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    /**
     * Scope: General resources (not class-specific)
     */
    public function scopeGeneral($query)
    {
        return $query->whereNull('class_id');
    }

    /**
     * Scope: Resources by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('resource_type', $type);
    }
}