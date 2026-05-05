<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    public $timestamps = false;

    protected $fillable = ['name', 'status'];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function photographers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'photographer_categories', 'category_id', 'photographer_id')
                    ->withPivot('charge_per_day');
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }
}
