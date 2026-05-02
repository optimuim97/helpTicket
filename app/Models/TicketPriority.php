<?php

namespace App\Models;

use App\Traits\CacheableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketPriority extends Model
{
    use CacheableModel;

    protected $fillable = ['name', 'level', 'color'];

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'priority_id');
    }
}
