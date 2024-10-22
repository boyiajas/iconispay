<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequisitionHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'requisition_id',
        'user_name',
        'action',
        'details',
    ];

    /**
     * The history log belongs to a requisition.
     */
    public function requisition()
    {
        return $this->belongsTo(Requisition::class);
    }
}
