<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Video extends Model
{
    use HasFactory, HasUuids;

    protected $primaryKey = 'video_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'course_id',
        'title',
        'duration',
        's3_url',
        'order_in_course',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function questions()
    {
        return $this->hasMany(Question::class, 'video_id');
    }

    public function userVideoProgress()
    {
        return $this->hasMany(UserVideoProgress::class, 'video_id');
    }
}
