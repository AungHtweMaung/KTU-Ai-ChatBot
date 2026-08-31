<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Timetable extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'major_year_id',
        'semester',
        'image_path',
    ];

    /**
     * The class (major year) this timetable belongs to.
     */
    public function majorYear(): BelongsTo
    {
        return $this->belongsTo(MajorYear::class);
    }

    /**
     * The public URL for the uploaded timetable image, or null if none.
     */
    protected function imageUrl(): Attribute
    {
        return Attribute::get(fn () => $this->image_path
            ? Storage::disk('public')->url($this->image_path)
            : null);
    }

    /**
     * Attributes to append to the array/JSON form.
     *
     * @var list<string>
     */
    protected $appends = ['image_url'];
}
