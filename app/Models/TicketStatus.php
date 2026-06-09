<?php

namespace App\Models;

use App\Traits\CacheableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketStatus extends Model
{
    use CacheableModel;

    public static function flushModelCache(): void
    {
        Cache::forget(static::cacheKey());
        Cache::forget('ticket_status_open_ids');
        Cache::forget('ticket_status_closed_ids');
    }

    protected $fillable = ['name', 'color', 'is_closed'];

    protected $casts = [
        'is_closed' => 'boolean',
    ];

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'status_id');
    }
}
