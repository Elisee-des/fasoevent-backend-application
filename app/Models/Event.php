<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Event extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'start_date',
        'end_date',
        'price',
        'image',
        'is_active',
        'city_id'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'id' => 'string',
        'start_date' => 'date',
        'end_date' => 'date',
        'price' => 'float',
        'is_active' => 'boolean'
    ];

    /**
     * Get the city that owns the event.
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * The users that belong to the event (réservations).
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'event_user')
            ->withTimestamps(); // Si vous voulez inclure les timestamps
    }
}
