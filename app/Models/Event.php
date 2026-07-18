<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Event extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'description',
        'type',
        'location',
        'major_id',
        'major_year_id',
        'starts_at',
        'ends_at',
        'cover_image_path',
        'is_published',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_published' => 'boolean',
        ];
    }

    /**
     * Attributes appended to the array/JSON form.
     *
     * @var list<string>
     */
    protected $appends = ['cover_image_url'];

    /**
     * The major this event targets (nullable — event is universal).
     */
    public function major(): BelongsTo
    {
        return $this->belongsTo(Major::class);
    }

    /**
     * The major year this event targets (nullable).
     */
    public function majorYear(): BelongsTo
    {
        return $this->belongsTo(MajorYear::class);
    }

    /**
     * The public URL for the event cover, or null.
     */
    protected function coverImageUrl(): Attribute
    {
        return Attribute::get(fn () => $this->cover_image_path
            ? Storage::disk('public')->url($this->cover_image_path)
            : null);
    }
}
