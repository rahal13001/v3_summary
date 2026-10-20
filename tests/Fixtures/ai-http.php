<?php

use App\Models\Indicator;
use App\Models\Report;
use App\Models\User;
use App\Services\Ai\TokenService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

// Isolated CLI fixture used by the local MCP smoke test. Never an HTTP entrypoint.
if (PHP_SAPI !== 'cli' || ! preg_match('/^summary-ai-smoke-[a-f0-9-]+$/', $argv[2] ?? '')) {
    exit(2);
}

$project = dirname(__DIR__, 2);
$root = $project.'/storage/framework/testing/'.$argv[2];
$mode = $argv[1] ?? '';
if ($mode === 'prepare') {
    if (is_dir($root)) {
        throw new RuntimeException('Fixture directory already exists.');
    }
    mkdir($root.'/storage/framework/views', 0777, true);
    mkdir($root.'/storage/framework/sessions', 0777, true);
    mkdir($root.'/storage/logs', 0777, true);
    touch($root.'/database.sqlite');
} elseif (! is_file($root.'/database.sqlite')) {
    exit(3);
}

$environment = [
    'APP_ENV' => 'testing', 'APP_DEBUG' => 'false',
    'APP_KEY' => 'base64:'.base64_encode(str_repeat('x', 32)),
    'APP_CONFIG_CACHE' => 'storage/framework/testing/'.$argv[2].'/config.php',
    'APP_SERVICES_CACHE' => 'storage/framework/testing/'.$argv[2].'/services.php',
    'APP_PACKAGES_CACHE' => 'storage/framework/testing/'.$argv[2].'/packages.php',
    'APP_ROUTES_CACHE' => 'storage/framework/testing/'.$argv[2].'/routes.php',
    'LARAVEL_STORAGE_PATH' => $root.'/storage', 'VIEW_COMPILED_PATH' => $root.'/storage/framework/views',
    'DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => $root.'/database.sqlite',
    'CACHE_STORE' => 'array', 'SESSION_DRIVER' => 'file', 'SESSION_SECURE_COOKIE' => 'false',
    'QUEUE_CONNECTION' => 'sync', 'MAIL_MAILER' => 'array', 'AI_API_ENABLED' => 'true', 'BCRYPT_ROUNDS' => '4',
];
foreach ($environment as $name => $value) {
    putenv($name.'='.$value);
    $_ENV[$name] = $_SERVER[$name] = $value;
}

require $project.'/vendor/autoload.php';
$app = require $project.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

if ($mode === 'prepare') {
    Artisan::call('migrate', ['--force' => true]);
    $user = User::create(['name' => 'AI Smoke Reader', 'email' => 'smoke@example.test',
        'password' => bin2hex(random_bytes(32)), 'status' => true]);
    $user->forceFill(['email_verified_at' => now()])->save();
    $user->assignRole(Role::findOrCreate('writer', 'web'));
    foreach (['use_ai_api', 'view_any_report', 'view_report', 'view_any_indicator', 'view_indicator'] as $permission) {
        $user->givePermissionTo(Permission::findOrCreate($permission, 'web'));
    }
    $indicator = Indicator::create(['nomor_iku' => '1', 'nama_iku' => 'IKU Layanan', 'tahun_iku' => '2026', 'status_iku' => 'active']);
    foreach ([['2026-07-01', 'Kegiatan Juli', '10'], ['2026-09-30', 'Kegiatan September', '15'], ['2026-10-01', 'Di luar periode', '99']] as [$date, $name, $participants]) {
        $report = Report::create(['user_id' => $user->id, 'no_st' => 'TEST', 'what' => $name,
            'why' => 'Fixture pengujian', 'when' => $date, 'tanggal_selesai' => $date, 'where' => 'Lokasi uji',
            'who' => 'Peserta uji', 'how' => 'Kegiatan selesai sesuai laporan.', 'penyelenggara' => 'Organisasi uji',
            'total_peserta' => $participants, 'total_wanita' => '5', 'kode' => 'TEST']);
        $report->indicators()->attach($indicator);
    }
    $token = app(TokenService::class)->issue($user, 'Smoke', ['reports', 'indicators'], [
        'reports' => ['what', 'when', 'tanggal_selesai', 'how', 'total_peserta'],
        'indicators' => ['id', 'nama_iku', 'nomor_iku'],
    ], 'account', 7);
    file_put_contents($root.'/token.txt', $token);
    echo json_encode(['prepared' => true, 'reports' => 3]);
    exit;
}

if ($mode !== 'request') {
    exit(4);
}
$payload = json_decode(stream_get_contents(STDIN), true, flags: JSON_THROW_ON_ERROR);
$url = $payload['url'];
if (! in_array(parse_url($url, PHP_URL_HOST), ['localhost', '127.0.0.1'], true)) {
    exit(5);
}
Route::middleware('web')->get('/fixture-login', function () {
    Auth::loginUsingId(1);
    request()->session()->regenerate();

    return redirect('/ai-access');
});
$cookies = [];
foreach (explode(';', $payload['headers']['cookie'] ?? '') as $cookie) {
    if (str_contains($cookie, '=')) {
        [$name, $value] = explode('=', trim($cookie), 2);
        $cookies[$name] = urldecode($value);
    }
}
$server = [];
foreach ($payload['headers'] as $name => $value) {
    $key = strtoupper(str_replace('-', '_', $name));
    $server[in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH'], true) ? $key : 'HTTP_'.$key] = $value;
}
$request = Request::create($url, $payload['method'], [], $cookies, [], $server, $payload['body'] ?? '');
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle($request);
$headers = $response->headers->all();
$headers['set-cookie'] = array_map('strval', $response->headers->getCookies());
ob_start();
$response->sendContent();
$body = ob_get_clean();
echo json_encode(['status' => $response->getStatusCode(), 'headers' => $headers, 'body' => base64_encode($body)], JSON_THROW_ON_ERROR);
$kernel->terminate($request, $response);
