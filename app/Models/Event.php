<?php

namespace App\Models;

use App\Enums\EventStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Event extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'organizer_id',
        'category_id',
        'venue_id',
        'title',
        'slug',
        'description',
        'banner_path',
        'start_time',
        'end_time',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'status' => EventStatus::class,
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Event $event) {
            if (empty($event->slug)) {
                $event->slug = static::generateUniqueSlug($event->title);
            }
        });
    }

    public static function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($title);
        $slug = $baseSlug;
        $count = 1;

        while (static::withTrashed()->where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = "{$baseSlug}-{$count}";
            $count++;
        }

        return $slug;
    }

    // Relationships
    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    public function ticketTypes(): HasMany
    {
        return $this->hasMany(TicketType::class);
    }

    // Scopes
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', EventStatus::PUBLISHED);
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('start_time', '>', now());
    }

    public function scopeNotEnded(Builder $query): Builder
    {
        return $query->where('end_time', '>', now());
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if ($search) {
            return $query->where('title', 'like', "%{$search}%");
        }

        return $query;
    }

    // Helper status methods
    public function isDraft(): bool
    {
        return $this->status === EventStatus::DRAFT;
    }

    public function isPending(): bool
    {
        return $this->status === EventStatus::PENDING;
    }

    public function isPublished(): bool
    {
        return $this->status === EventStatus::PUBLISHED;
    }

    public function isCancelled(): bool
    {
        return $this->status === EventStatus::CANCELLED;
    }

    public function canBeEdited(): bool
    {
        return $this->isDraft();
    }

    public function canBeSubmitted(): bool
    {
        return $this->isDraft()
            && $this->ticketTypes()->count() > 0
            && $this->start_time > now();
    }

    public function statusLabel(): string
    {
        return $this->status?->label() ?? '';
    }

    public function statusBadgeClass(): string
    {
        return $this->status?->badgeClass() ?? 'bg-secondary';
    }
}
