<?php

// Run against an existing installation; all read-status changes are rolled back.
require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
if (isset($argv[1])) {
    $app->useStoragePath($argv[1]);
}
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['session.driver' => 'array']);
$user = App\Models\User::firstOrFail();
Illuminate\Support\Facades\Auth::onceUsingId($user->id);
$controller = app(App\Http\Controllers\User\DashboardController::class);
$request = Illuminate\Http\Request::create('https://accessbn.com/user/dashboard');
$request->setUserResolver(fn () => $user);
$request->setLaravelSession(app('session')->driver());
$request->setRouteResolver(fn () => app('router')->getRoutes()->getByName('user.dashboard'));
$app->instance('request', $request);
$check = function ($condition, $message) {
    if (!$condition) {
        throw new RuntimeException($message);
    }
    echo "PASS: $message\n";
};

Illuminate\Support\Facades\DB::beginTransaction();
try {
    $expected = $user->notificationMessages()->where('status', 'unread')->count();
    $ids = [];
    do {
        $page = $controller->notifications($request)->getData(true);
        $check(count($page['items']) <= 20, 'Page is bounded to 20 messages');
        foreach ($page['items'] as $item) {
            $check(!isset($item['body']), 'List excludes message bodies');
            $ids[] = $item['id'];
        }
        $request->query->set('cursor', $page['next_cursor']);
    } while ($page['next_cursor']);
    $check(count($ids) === $expected && count(array_unique($ids)) === $expected, 'Pagination preserves all unread messages without duplicates');
    if ($ids) {
        $detail = $controller->notification($request, $ids[0])->getData(true);
        $check(isset($detail['body']), 'Details remain accessible');
        $request->merge(['notification_id' => $ids[0]]);
        $controller->notificationMarkAsRead($request);
        $check($user->notificationMessages()->find($ids[0])->status === 'read', 'Own message can be marked read');
    }
    $foreign = App\Models\NotificationMessage::where('user_id', '!=', $user->id)->first();
    if ($foreign) {
        foreach (['notification', 'notificationMarkAsRead'] as $method) {
            $request->merge(['notification_id' => $foreign->id]);
            try {
                $controller->$method($request, $foreign->id);
                throw new RuntimeException('Foreign notification was accessible');
            } catch (Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                $check(true, 'Foreign notification is denied: '.$method);
            }
        }
    }
    $start = microtime(true);
    $html = $controller->index()->render();
    $check(strlen($html) < 1000000, 'Dashboard HTML stays below 1 MB');
    echo json_encode(['dashboard_seconds' => round(microtime(true) - $start, 3), 'html_bytes' => strlen($html)])."\n";
} finally {
    Illuminate\Support\Facades\DB::rollBack();
}
