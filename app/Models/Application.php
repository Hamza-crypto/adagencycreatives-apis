<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Application extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'user_id',
        'job_id',
        'attachment_id',
        'message',
        'status',
    ];

    const STATUSES = [
        'PENDING' => 0,
        'ACCEPTED' => 1,
        'REJECTED' => 2,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    public function getStatusAttribute($value)
    {
        switch ($value) {
            case Application::STATUSES['PENDING']:
                return 'pending';
            case Application::STATUSES['ACCEPTED']:
                return 'accepted';
            case Application::STATUSES['REJECTED']:
                return 'rejected';

            default:
                return null;
        }
    }

    public function setStatusAttribute($value)
    {
        switch ($value) {
            case 'accepted':
                $this->attributes['status'] = Application::STATUSES['ACCEPTED'];
                break;
            case 'rejected':
                $this->attributes['status'] = Application::STATUSES['REJECTED'];
                break;
            default:
                $this->attributes['status'] = Application::STATUSES['PENDING'];
                break;
        }
    }
}
