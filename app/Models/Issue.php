<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Issue extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'priority',
        'category',
        'status',
        'summary',
        'next_action',
        'summary_status',
        'is_escalated',
    ];

    // Casts are used to convert the data to the correct type
    protected $casts = [
        'is_escalated' => 'boolean',
    ];

    // An issue has zero or more comments from team members.
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    // Escalation rule: high/critical priority that is still open or in-progress
    public function shouldEscalate(): bool
    {
        return in_array($this->priority, ['high', 'critical'])
            && in_array($this->status, ['open', 'in_progress']);
    }

    public function refreshEscalation(): void
    {
        $this->is_escalated = $this->shouldEscalate();
    }

    // Query scopes for filtering
    public function scopeByStatus(Builder $query, ?string $status): Builder
    {
        return $status ? $query->where('status', $status) : $query;
    }

    public function scopeByCategory(Builder $query, ?string $category): Builder
    {
        return $category ? $query->where('category', $category) : $query;
    }

    public function scopeByPriority(Builder $query, ?string $priority): Builder
    {
        return $priority ? $query->where('priority', $priority) : $query;
    }

    public function scopeEscalated(Builder $query): Builder
    {
        return $query->where('is_escalated', true);
    }

    // Ordered by urgency: critical first, then high, then by recency
    // Builder is a class that is used to build the query
    // The scopeByUrgency() method will order the issues by urgency
    // The orderByRaw() method is used to order the issues by urgency
    // The orderBy() method is used to order the issues by recency
    // The created_at column is used to order the issues by recency
    // The priority column is used to order the issues by urgency
    // The critical priority is first, then high, then medium, then low
    // The created_at column is used to order the issues by recency
    // The recency is the latest issues first
    public function scopeByUrgency(Builder $query): Builder
    {
        return $query->orderByRaw("CASE priority
            WHEN 'critical' THEN 1
            WHEN 'high'     THEN 2
            WHEN 'medium'   THEN 3
            WHEN 'low'      THEN 4
            END")->orderBy('created_at', 'desc');
    }

    public static function priorities(): array
    {
        return ['low', 'medium', 'high', 'critical'];
    }

    public static function categories(): array
    {
        return ['bug', 'feature', 'infrastructure', 'security', 'other'];
    }

    public static function statuses(): array
    {
        return ['open', 'in_progress', 'resolved', 'closed'];
    }
}
