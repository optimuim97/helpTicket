<?php

namespace App\Models;

use App\Traits\CacheableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketChannel extends Model
{
    use CacheableModel;

    protected $fillable = ['name'];

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'channel_id');
    }
}
