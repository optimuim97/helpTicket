<?php

namespace App\Providers;

use App\Models\EquipmentAssignment;
use App\Models\InterventionSheet;
use App\Observers\TicketObserver;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
        \App\Models\Ticket::observe(\App\Observers\TicketObserver::class);

        Relation::morphMap([
            'intervention_sheet'   => InterventionSheet::class,
            'equipment_assignment' => EquipmentAssignment::class,
        ]);
    }
}
