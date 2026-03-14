<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class AppSettingController extends Controller
{
    /**
     * Display settings page.
     */
    public function index(): Response
    {
        // Only Superviseurs can access settings
        abort_unless(auth()->user()->hasRole('Superviseur'), 403);

        $settings = AppSetting::getAllGrouped();

        return Inertia::render('Settings/Index', [
            'settings' => $settings,
        ]);
    }

    /**
     * Update settings.
     */
    public function update(Request $request)
    {
        abort_unless(auth()->user()->hasRole('Superviseur'), 403);

        $validated = $request->validate([
            'settings' => 'required|array',
        ]);

        foreach ($validated['settings'] as $key => $value) {
            AppSetting::set($key, $value);
        }

        return redirect()->route('settings.index')->with('success', 'Paramètres mis à jour avec succès.');
    }

    /**
     * Upload logo.
     */
    public function uploadLogo(Request $request)
    {
        abort_unless(auth()->user()->hasRole('Superviseur'), 403);

        $request->validate([
            'logo' => 'required|image|max:3072', // Max 3MB
        ]);

        // Delete old logo if exists
        $oldLogo = AppSetting::get('app_logo');
        if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
            Storage::disk('public')->delete($oldLogo);
        }

        // Store new logo
        $path = $request->file('logo')->store('logos', 'public');

        // Update setting
        AppSetting::set('app_logo', $path);

        return redirect()->route('settings.index')->with('success', 'Logo mis à jour avec succès.');
    }
}
