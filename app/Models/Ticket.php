<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'ticket_number',
        'type_id',
        'channel_id',
        'priority_id',
        'status_id',
        'subject',
        'description',
        'notes',
        'assigned_to',
        'created_by',
        'resolved_at',
        'closed_at',
        'due_date',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
        'due_date' => 'datetime',
    ];

    // Relationships
    public function type(): BelongsTo
    {
        return $this->belongsTo(TicketType::class, 'type_id');
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(TicketChannel::class, 'channel_id');
    }

    public function priority(): BelongsTo
    {
        return $this->belongsTo(TicketPriority::class, 'priority_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(TicketStatus::class, 'status_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function ticketNotes(): HasMany
    {
        return $this->hasMany(TicketNote::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TicketAttachment::class);
    }

    public function history(): HasMany
    {
        return $this->hasMany(TicketHistory::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(TicketAssignment::class);
    }

    // Scopes
    public function scopeOpen($query)
    {
        return $query->whereHas('status', function ($q) {
            $q->where('is_closed', false);
        });
    }

    public function scopeClosed($query)
    {
        return $query->whereHas('status', function ($q) {
            $q->where('is_closed', true);
        });
    }

    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeCreatedBy($query, $userId)
    {
        return $query->where('created_by', $userId);
    }
}
