<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'country_id',
        'state_id',
        'city_id',
        'profile_photo',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
    ];

    protected $appends = ['profile_photo_url'];

    // ── Accessors ─────────────────────────────────────

    public function getProfilePhotoUrlAttribute(): ?string
    {
        if (!$this->profile_photo) {
            return null;
        }
        return Storage::disk('public')->url($this->profile_photo);
    }

    // ── Location ────────────────────────────────────────

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    // ── Profile ─────────────────────────────────────────

    public function photographerProfile(): HasOne
    {
        return $this->hasOne(PhotographerProfile::class);
    }

    // ── Categories (pivot with charge_per_day) ──────────

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'photographer_categories', 'photographer_id', 'category_id')
                    ->withPivot('charge_per_day');
    }

    // ── Camera Kit ──────────────────────────────────────

    public function cameraKits(): HasMany
    {
        return $this->hasMany(CameraKit::class);
    }

    // ── Work Cities ─────────────────────────────────────

    public function workCities(): HasMany
    {
        return $this->hasMany(WorkCity::class);
    }

    // ── Device Tokens (multi-device auth) ───────────────

    public function deviceTokens(): HasMany
    {
        return $this->hasMany(DeviceToken::class);
    }

    // ── Hire Requests ───────────────────────────────────

    public function hireRequests(): HasMany
    {
        return $this->hasMany(HireRequest::class, 'photographer_id');
    }

    // ── Notifications (pivot with read_at) ──────────────

    public function appNotifications(): BelongsToMany
    {
        return $this->belongsToMany(Notification::class, 'notification_user')
                    ->withPivot('read_at');
    }

    // ── Scopes ──────────────────────────────────────────

    public function scopePhotographers($query)
    {
        return $query->where('role', 'photographer');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeBlocked($query)
    {
        return $query->where('status', 'blocked');
    }

    // ── Helpers ─────────────────────────────────────────

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isPhotographer(): bool
    {
        return $this->role === 'photographer';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
