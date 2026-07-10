<?php

use App\Models\MenuItem;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('copy_tradings', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('pair');
            $table->decimal('roi', 18, 2);
            $table->unsignedBigInteger('expires_at')->nullable();
            $table->timestamps();
        });


        // lets add a new module called "copy_trading_module"
        $modules = json_decode(getSetting("modules"), true);
        $modules["copy_trading_module"] = [
            "name" => "Copy Trading",
            "status" => "enabled",
            "menu_search" => [
                [
                    "term" => "Copy Trading",
                    "column" => "label"
                ]
            ],
            "description" => "Enables copy trading for users."
        ];
        updateSetting("modules", $modules);
        file_put_contents(public_path('assets/json/modules.json'), json_encode($modules, JSON_PRETTY_PRINT));


        // lets add a new menu item for user and admin for "copy trading"
        $this->addAdminMenu();
        $this->addUserMenu();

        // Clear menu cache
        Cache::forget('admin_menu_items');
        Cache::forget('user_menu_items');
        Artisan::call('cache:clear');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('copy_tradings');

        $modules = json_decode(getSetting("modules"), true);
        unset($modules["copy_trading_module"]);
        updateSetting("modules", $modules);
        file_put_contents(public_path('assets/json/modules.json'), json_encode($modules, JSON_PRETTY_PRINT));

        // Delete menu items
        MenuItem::where('route_name', 'like', 'admin.copy-trading.%')->delete();
        MenuItem::where('label', 'Copy Trading')->where('type', 'admin')->delete();

        MenuItem::where('route_name', 'like', 'user.copy-trading.%')->delete();
        MenuItem::where('label', 'Copy Trading')->where('type', 'user')->delete();

        // Clear menu cache
        Cache::forget('admin_menu_items');
        Cache::forget('user_menu_items');
        Artisan::call('cache:clear');
    }


    private function addAdminMenu()
    {
        // Find or create parent
        $copyTradingMenu = MenuItem::updateOrCreate(
            ['label' => 'Copy Trading', 'type' => 'admin', 'parent_id' => null],
            [
                'route_name' => null,
                'url' => '#',
                'sort_order' => 7,
                'is_active' => true,
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>',
                'route_wildcard' => 'admin.copy-trading.*',
            ]
        );

        $parent_id = $copyTradingMenu->id;

        // Add "New Copy Trading"
        MenuItem::updateOrCreate(
            ['route_name' => 'admin.copy-trading.create', 'type' => 'admin'],
            [
                'label' => 'New Copy Trading',
                'parent_id' => $parent_id,
                'sort_order' => 1,
                'is_active' => true,
                'route_wildcard' => 'admin.copy-trading.create',
            ]
        );

        // Add "Trading Codes"
        MenuItem::updateOrCreate(
            ['route_name' => 'admin.copy-trading.index', 'type' => 'admin'],
            [
                'label' => 'Trading Codes',
                'parent_id' => $parent_id,
                'sort_order' => 2,
                'is_active' => true,
                'route_wildcard' => 'admin.copy-trading.index',
            ]
        );

        // Add "Trading History"
        MenuItem::updateOrCreate(
            ['route_name' => 'admin.copy-trading.history', 'type' => 'admin'],
            [
                'label' => 'Trading History',
                'parent_id' => $parent_id,
                'sort_order' => 3,
                'is_active' => true,
                'route_wildcard' => 'admin.copy-trading.history',
            ]
        );
    }

    private function addUserMenu()
    {
        // Find or create parent
        $copyTradingMenu = MenuItem::updateOrCreate(
            ['label' => 'Copy Trading', 'type' => 'user', 'parent_id' => null],
            [
                'route_name' => null,
                'url' => '#',
                'sort_order' => 8,
                'is_active' => true,
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>',
                'route_wildcard' => 'user.copy-trading.*',
            ]
        );

        $parent_id = $copyTradingMenu->id;

        // Add "Trade Now"
        MenuItem::updateOrCreate(
            ['route_name' => 'user.copy-trading.index', 'type' => 'user'],
            [
                'label' => 'Trade Now',
                'parent_id' => $parent_id,
                'sort_order' => 1,
                'is_active' => true,
                'route_wildcard' => 'user.copy-trading.index',
            ]
        );

        // Add "Trading History"
        MenuItem::updateOrCreate(
            ['route_name' => 'user.copy-trading.history', 'type' => 'user'],
            [
                'label' => 'Trading History',
                'parent_id' => $parent_id,
                'sort_order' => 2,
                'is_active' => true,
                'route_wildcard' => 'user.copy-trading.history',
            ]
        );
    }
};
