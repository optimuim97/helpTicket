<?php

namespace App\Services;

use App\Models\User;

class MenuService
{
    /**
     * Get the navigation menu for a specific user
     * Filters menu items based on permissions and roles
     *
     * @param User $user
     * @param string $menuKey Menu configuration key (main, user, mobile)
     * @return array
     */
    public function getMenuForUser(User $user, string $menuKey = 'main'): array
    {
        $menuConfig = config("menu.{$menuKey}", []);
        
        return $this->filterMenuItems($menuConfig, $user);
    }

    /**
     * Get all menus for a user
     *
     * @param User $user
     * @return array
     */
    public function getAllMenusForUser(User $user): array
    {
        return [
            'main' => $this->getMenuForUser($user, 'main'),
            'user' => $this->getMenuForUser($user, 'user'),
            'mobile' => $this->getMenuForUser($user, 'mobile'),
        ];
    }

    /**
     * Filter menu items based on user permissions and roles
     *
     * @param array $items
     * @param User $user
     * @return array
     */
    protected function filterMenuItems(array $items, User $user): array
    {
        $filtered = [];

        foreach ($items as $item) {
            // Check if user has required permission
            if (isset($item['permission']) && !$user->can($item['permission'])) {
                continue;
            }

            // Check if user has required role
            if (isset($item['role']) && !$user->hasRole($item['role'])) {
                continue;
            }

            // Process children if exists
            if (isset($item['children']) && is_array($item['children'])) {
                $filteredChildren = $this->filterMenuItems($item['children'], $user);
                
                // Skip parent if no children remain after filtering
                if (empty($filteredChildren)) {
                    continue;
                }
                
                $item['children'] = $filteredChildren;
            }

            $filtered[] = $item;
        }

        return $filtered;
    }

    /**
     * Check if a menu item is active based on current route
     *
     * @param array $item
     * @param string $currentRoute
     * @return bool
     */
    public function isActive(array $item, string $currentRoute): bool
    {
        if (!isset($item['active'])) {
            return false;
        }

        $activePattern = $item['active'];

        // Handle wildcard patterns
        if (str_contains($activePattern, '*')) {
            $pattern = str_replace('*', '.*', $activePattern);
            return (bool) preg_match("/^{$pattern}$/", $currentRoute);
        }

        // Handle multiple patterns separated by |
        if (str_contains($activePattern, '|')) {
            $patterns = explode('|', $activePattern);
            foreach ($patterns as $pattern) {
                if ($this->matchPattern($pattern, $currentRoute)) {
                    return true;
                }
            }
            return false;
        }

        return $activePattern === $currentRoute;
    }

    /**
     * Match a single pattern against the current route
     *
     * @param string $pattern
     * @param string $currentRoute
     * @return bool
     */
    protected function matchPattern(string $pattern, string $currentRoute): bool
    {
        if (str_contains($pattern, '*')) {
            $pattern = str_replace('*', '.*', $pattern);
            return (bool) preg_match("/^{$pattern}$/", $currentRoute);
        }

        return $pattern === $currentRoute;
    }

    /**
     * Build navigation tree with additional metadata
     *
     * @param array $items
     * @param User $user
     * @param string|null $currentRoute
     * @return array
     */
    public function buildNavigationTree(array $items, User $user, ?string $currentRoute = null): array
    {
        $tree = [];

        foreach ($items as $item) {
            $node = [
                'label' => $item['label'] ?? '',
                'route' => $item['route'] ?? null,
                'icon' => $item['icon'] ?? null,
                'type' => $item['type'] ?? 'link',
                'method' => $item['method'] ?? 'get',
                'active' => false,
            ];

            // Check if item is active
            if ($currentRoute && isset($item['active'])) {
                $node['active'] = $this->isActive($item, $currentRoute);
            }

            // Process children
            if (isset($item['children'])) {
                $node['children'] = $this->buildNavigationTree($item['children'], $user, $currentRoute);
                
                // Parent is active if any child is active
                if (!$node['active']) {
                    foreach ($node['children'] as $child) {
                        if ($child['active'] ?? false) {
                            $node['active'] = true;
                            break;
                        }
                    }
                }
            }

            $tree[] = $node;
        }

        return $tree;
    }

    /**
     * Get breadcrumbs from menu configuration
     *
     * @param string $currentRoute
     * @param array|null $menuItems
     * @return array
     */
    public function getBreadcrumbs(string $currentRoute, ?array $menuItems = null): array
    {
        if ($menuItems === null) {
            $menuItems = config('menu.main', []);
        }

        $breadcrumbs = [];
        $this->findBreadcrumbPath($menuItems, $currentRoute, $breadcrumbs);

        return $breadcrumbs;
    }

    /**
     * Recursively find breadcrumb path
     *
     * @param array $items
     * @param string $targetRoute
     * @param array &$path
     * @param array $currentPath
     * @return bool
     */
    protected function findBreadcrumbPath(array $items, string $targetRoute, array &$path, array $currentPath = []): bool
    {
        foreach ($items as $item) {
            $newPath = array_merge($currentPath, [[
                'label' => $item['label'] ?? '',
                'route' => $item['route'] ?? null,
            ]]);

            // Check if this is the target item
            if (isset($item['active']) && $this->isActive($item, $targetRoute)) {
                $path = $newPath;
                return true;
            }

            // Search in children
            if (isset($item['children']) && is_array($item['children'])) {
                if ($this->findBreadcrumbPath($item['children'], $targetRoute, $path, $newPath)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get menu statistics
     *
     * @param User $user
     * @return array
     */
    public function getMenuStatistics(User $user): array
    {
        $mainMenu = $this->getMenuForUser($user, 'main');
        
        return [
            'total_items' => $this->countMenuItems($mainMenu),
            'top_level_items' => count($mainMenu),
            'has_admin_access' => $user->can('view_users'),
        ];
    }

    /**
     * Count total menu items including children
     *
     * @param array $items
     * @return int
     */
    protected function countMenuItems(array $items): int
    {
        $count = count($items);

        foreach ($items as $item) {
            if (isset($item['children'])) {
                $count += $this->countMenuItems($item['children']);
            }
        }

        return $count;
    }
}
