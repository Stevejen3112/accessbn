<?php

use App\Models\MenuItem;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->addAdminMenu();
        $this->addUserMenu();

        // Clear menu cache
        Cache::forget('admin_menu_items');
        Cache::forget('user_menu_items');
        Artisan::call('cache:clear');
    }

    private function addAdminMenu()
    {
        // Find the "Global Trading Bots" menu item (which might be the original single link)
        $botMenu = MenuItem::where('type', 'admin')
            ->where(function ($q) {
                $q->where('route_name', 'admin.trading-bots.index')
                    ->orWhere('label', 'Global Trading Bots')
                    ->orWhere('label', 'Trading Bots');
            })
            ->first();

        if ($botMenu) {
            // If it's currently a single link, convert it to a parent group
            if (empty($botMenu->parent_id)) {
                $botMenu->update([
                    'label' => 'Trading Bots',
                    'route_name' => null,
                    'url' => '#',
                    'route_wildcard' => 'admin.trading-bots.*',
                ]);

                // Create "Bot Manager" as a child (points to the original index)
                MenuItem::create([
                    'label' => 'Bot Manager',
                    'route_name' => 'admin.trading-bots.index',
                    'type' => 'admin',
                    'parent_id' => $botMenu->id,
                    'sort_order' => 1,
                    'is_active' => true,
                    'route_wildcard' => 'admin.trading-bots.index',
                ]);
            }
            $parent_id = $botMenu->id;
        } else {
            // Create new parent
            $botMenu = MenuItem::create([
                'label' => 'Trading Bots',
                'type' => 'admin',
                'route_name' => null,
                'url' => '#',
                'sort_order' => 7,
                'is_active' => true,
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8V4H8"/><rect width="16" height="12" x="4" y="8" rx="2"/><path d="M2 14h2"/><path d="M20 14h2"/><path d="M15 13v2"/><path d="M9 13v2"/></svg>',
                'route_wildcard' => 'admin.trading-bots.*',
            ]);

            MenuItem::create([
                'label' => 'Bot Manager',
                'route_name' => 'admin.trading-bots.index',
                'type' => 'admin',
                'parent_id' => $botMenu->id,
                'sort_order' => 1,
                'is_active' => true,
                'route_wildcard' => 'admin.trading-bots.index',
            ]);

            $parent_id = $botMenu->id;
        }

        // Add "Bot Activations"
        MenuItem::updateOrCreate(
            ['route_name' => 'admin.trading-bots.activations.index', 'type' => 'admin'],
            [
                'label' => 'Bot Activations',
                'parent_id' => $parent_id,
                'sort_order' => 2,
                'is_active' => true,
                'route_wildcard' => 'admin.trading-bots.activations.*',
            ]
        );

        // Add "Trading Logs"
        MenuItem::updateOrCreate(
            ['route_name' => 'admin.trading-bots.logs.index', 'type' => 'admin'],
            [
                'label' => 'Trading Logs',
                'parent_id' => $parent_id,
                'sort_order' => 3,
                'is_active' => true,
                'route_wildcard' => 'admin.trading-bots.logs.*',
            ]
        );
    }

    private function addUserMenu()
    {
        // Find existing "Trading Bots" menu for user if it exists
        $botMenu = MenuItem::where('type', 'user')
            ->where(function ($q) {
                $q->where('route_name', 'user.trading-bots.index')
                    ->orWhere('label', 'Trading Bots');
            })
            ->first();

        if ($botMenu) {
            if (empty($botMenu->parent_id)) {
                $botMenu->update([
                    'label' => 'Trading Bots',
                    'route_name' => null,
                    'url' => '#',
                    'route_wildcard' => 'user.trading-bots.*',
                ]);

                // Create "Available Bots" child
                MenuItem::create([
                    'label' => 'Available Bots',
                    'route_name' => 'user.trading-bots.index',
                    'type' => 'user',
                    'parent_id' => $botMenu->id,
                    'sort_order' => 1,
                    'is_active' => true,
                ]);
            }
            $parent_id = $botMenu->id;
        } else {
            // Create new top-level parent for user bots
            $botMenu = MenuItem::create([
                'label' => 'Trading Bots',
                'type' => 'user',
                'route_name' => null,
                'url' => '#',
                'sort_order' => 8,
                'is_active' => true,
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8V4H8"/><rect width="16" height="12" x="4" y="8" rx="2"/><path d="M2 14h2"/><path d="M20 14h2"/><path d="M15 13v2"/><path d="M9 13v2"/></svg>',
                'route_wildcard' => 'user.trading-bots.*',
            ]);

            MenuItem::create([
                'label' => 'Available Bots',
                'route_name' => 'user.trading-bots.index',
                'type' => 'user',
                'parent_id' => $botMenu->id,
                'sort_order' => 1,
                'is_active' => true,
                'route_wildcard' => 'user.trading-bots.*',
            ]);

            $parent_id = $botMenu->id;
        }

        // Add "My Activations"
        MenuItem::updateOrCreate(
            ['route_name' => 'user.trading-bots.activations', 'type' => 'user'],
            [
                'label' => 'My Activations',
                'parent_id' => $parent_id,
                'sort_order' => 2,
                'is_active' => true,
                'route_wildcard' => 'user.trading-bots.*',
            ]
        );

        // Add "Trading Logs"
        MenuItem::updateOrCreate(
            ['route_name' => 'user.trading-bots.logs', 'type' => 'user'],
            [
                'label' => 'Trading Logs',
                'parent_id' => $parent_id,
                'sort_order' => 3,
                'is_active' => true,
                'route_wildcard' => 'user.trading-bots.*',
            ]
        );
        // Add "Daily Summary"
        MenuItem::updateOrCreate(
            ['route_name' => 'user.trading-bots.daily-summary', 'type' => 'user'],
            [
                'label' => 'Daily Summary',
                'parent_id' => $parent_id,
                'sort_order' => 4,
                'is_active' => true,
                'route_wildcard' => 'user.trading-bots.*',
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Delete all children items first
        MenuItem::whereIn('route_name', [
            // admin
            'admin.trading-bots.activations.index',
            'admin.trading-bots.logs.index',
            'admin.trading-bots.index',
            // User
            'user.trading-bots.activations',
            'user.trading-bots.logs',
            'user.trading-bots.index',
            'user.trading-bots.daily-summary',
        ])->delete();

        // 2. Delete parent items with specific labels if they have no remaining children
        // Use pluck then delete to avoid MySQL error 1093
        $parentIds = MenuItem::whereIn('label', ['Trading Bots', 'Global Trading Bots'])
            ->whereDoesntHave('children')
            ->pluck('id');

        if ($parentIds->isNotEmpty()) {
            MenuItem::whereIn('id', $parentIds)->delete();
        }

        Cache::forget('admin_menu_items');
        Cache::forget('user_menu_items');
        Artisan::call('cache:clear');
    }
};
