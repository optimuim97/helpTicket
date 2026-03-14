<?php

namespace App\Http\Middleware;

use App\Models\AppSetting;
use App\Services\MenuService;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * The menu service instance.
     *
     * @var MenuService
     */
    protected $menuService;

    /**
     * Create a new middleware instance.
     */
    public function __construct(MenuService $menuService)
    {
        $this->menuService = $menuService;
    }

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? $user->load('roles') : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'appSettings' => [
                'app_name' => AppSetting::get('app_name', 'HELPTICKET'),
                'app_tagline' => AppSetting::get('app_tagline', 'Un ticket, un déclic, HELPTICKET c\'est magique'),
                'app_logo' => AppSetting::get('app_logo', 'logos/helpticket-logo.png'),
            ],
            'navigation' => [
                'main' => $user ? $this->menuService->getMenuForUser($user, 'main') : [],
                'user' => $user ? $this->menuService->getMenuForUser($user, 'user') : [],
                'mobile' => $user ? $this->menuService->getMenuForUser($user, 'mobile') : [],
            ],
        ];
    }
}
