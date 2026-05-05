<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhotographerProfile extends Model
{
    protected $fillable = [
        'user_id',
        'bio',
        'experience',
        'default_charge',
        'instagram_link',
        'facebook_link',
        'portfolio_link',
    ];

    protected $casts = [
        'default_charge' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
