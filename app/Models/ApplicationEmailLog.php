<?php

namespace App\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicationEmailLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'application_id',
        'status',
        'email_sent_at',
    ];

    public function getStatusAttribute($value)
    {
        return getApplicationStatus($value);
    }

    public function setStatusAttribute($value)
    {
        switch ($value) {
            case 'accepted':
                $this->attributes['status'] = APPLICATION_STATUSES['ACCEPTED'];
                break;
            case 'rejected':
                $this->attributes['status'] = APPLICATION_STATUSES['REJECTED'];
                break;
            case 'archived':
                $this->attributes['status'] = APPLICATION_STATUSES['ARCHIVED'];
                break;
            case 'shortlisted':
                $this->attributes['status'] = APPLICATION_STATUSES['SHORTLISTED'];
                break;
            case 'recommended':
                $this->attributes['status'] = APPLICATION_STATUSES['RECOMMENDED'];
                break;
            case 'hired':
                $this->attributes['status'] = APPLICATION_STATUSES['HIRED'];
                break;
            default:
                $this->attributes['status'] = APPLICATION_STATUSES['PENDING'];
                break;
        }
    }

    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    protected static function booted() {}
}