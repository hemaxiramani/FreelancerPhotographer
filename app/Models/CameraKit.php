<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CameraKit extends Model
{
    public $timestamps = false;

    protected $fillable = ['user_id', 'item_name'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
