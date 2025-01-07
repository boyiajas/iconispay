<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\NotificationType;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'requisition_id',
        'notification_type',
    ];

    protected $casts = [
        'notification_type' => NotificationType::class,
    ];

    public function requisition()
    {
        return $this->belongsTo(Requisition::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
