<?php

use App\Models\Report;
use App\Models\ReportEvaluation;
use App\Models\User;
use App\Models\WorkUnit;
use App\Models\WorkUnitCoordinator;
use App\Services\Ai\TokenService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

// Opt-in compatibility check: a random, newly created LOCAL database only.
if (PHP_SAPI !== 'cli' || getenv('SUMMARY_ALLOW_LOCAL_MYSQL_TEST') !== '1') {
    exit(2);
}

$project = dirname(__DIR__, 2);
$id = 'summary_ai_compat_'.bin2hex(random_bytes(8));
$root = $project.'/storage/framework/testing/'.$id;
mkdir($root.'/storage/framework/views', 0777, true);
mkdir($root.'/storage/logs', 0777, true);
foreach ([
    'APP_ENV' => 'testing', 'APP_DEBUG' => 'false', 'AI_API_ENABLED' => 'true',
    'APP_CONFIG_CACHE' => 'storage/framework/testing/'.$id.'/config.php',
    'LARAVEL_STORAGE_PATH' => $root.'/storage', 'VIEW_COMPILED_PATH' => $root.'/storage/framework/views',
    'CACHE_STORE' => 'array', 'SESSION_DRIVER' => 'array', 'BCRYPT_ROUNDS' => '4',
] as $name => $value) {
    putenv($name.'='.$value);
    $_ENV[$name] = $_SERVER[$name] = $value;
}

require $project.'/vendor/autoload.php';
$app = require $project.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
$connection = config('database.connections.mysql');
if (! in_array($connection['host'] ?? '', ['localhost', '127.0.0.1', '::1'], true) || ! empty($connection['url'])) {
    throw new RuntimeException('Only an explicit localhost MySQL connection is permitted.');
}
config(['database.connections.ai_compat_admin' => $connection]);
$admin = DB::connection('ai_compat_admin');
$created = false;
$expect = function (bool $condition, string $message): void {
    if (! $condition) {
        throw new RuntimeException($message);
    }
};

try {
    // id is generated internally from a fixed prefix and random hexadecimal bytes.
    $admin->statement('CREATE DATABASE `'.$id.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    $created = true;
    config(['database.default' => 'mysql', 'database.connections.mysql.database' => $id]);
    DB::purge('mysql');
    $expect(DB::connection()->selectOne('SELECT DATABASE() AS name')->name === $id, 'Test database isolation failed.');
    Artisan::call('migrate', ['--force' => true]);
    $user = User::create(['name' => 'MySQL compatibility reader', 'email' => 'compat@example.test', 'password' => bin2hex(random_bytes(24)), 'status' => true]);
    foreach (['use_ai_api', 'view_any_report', 'view_report', 'view_any_user', 'view_user'] as $permission) {
        $user->givePermissionTo(Permission::findOrCreate($permission, 'web'));
    }
    foreach ([['2026-06-30', '2026-07-02', '100% kegiatan'], ['2026-09-30', '2026-09-30', 'Kegiatan September'], ['2026-10-01', '2026-10-01', 'Di luar periode']] as [$start, $end, $name]) {
        Report::create(['user_id' => $user->id, 'no_st' => 'TEST', 'what' => $name,
            'why' => 'Fixture', 'when' => $start, 'tanggal_selesai' => $end, 'where' => 'Fixture', 'who' => 'Fixture',
            'how' => 'Hasil', 'penyelenggara' => 'Fixture', 'total_peserta' => '10', 'total_wanita' => '5', 'kode' => 'TEST']);
    }
    $report = Report::firstOrFail();
    $followers = User::factory()->count(27)->create();
    $report->followers()->attach($followers->modelKeys());
    $token = app(TokenService::class)->issue($user, 'Compatibility', ['reports', 'people'], [
        'reports' => ['what', 'when', 'tanggal_selesai'], 'people' => ['id', 'name'],
    ], 'account', 7);
    $before = Report::orderBy('id')->get()->toArray();
    $get = function (string $path) use ($app, &$token, $expect): array {
        $request = Request::create('https://localhost/api/ai/v1/'.$path, 'GET', [], [], [], ['HTTP_AUTHORIZATION' => 'Bearer '.$token, 'HTTP_ACCEPT' => 'application/json']);
        $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
        $response = $kernel->handle($request);
        $data = json_decode($response->getContent(), true, flags: JSON_THROW_ON_ERROR);
        $kernel->terminate($request, $response);
        $expect($response->getStatusCode() === 200, 'API request failed on isolated MySQL: '.$path);

        return $data;
    };
    $expect($get('reports?period_start=2026-07-01&period_end=2026-09-30')['meta']['total'] === 2, 'MySQL overlap mismatch.');
    $expect($get('reports?q=100%25')['meta']['total'] === 1, 'MySQL literal LIKE escaping mismatch.');
    $nested = $get('reports/'.$report->id.'?include=people');
    $expect(count($nested['data']['people']['followers']) === 25 && $nested['data']['_relation_meta']['followers']['has_more'], 'MySQL eager collection limit mismatch.');
    $expect(count($get('reports/'.$report->id.'/followers?page=2')['data']) === 2, 'MySQL follower pagination mismatch.');
    $expect($before === Report::orderBy('id')->get()->toArray(), 'Business reports changed during API reads.');
    config(['organization.monev_enabled' => true]);
    $evaluationReport = Report::factory()->for(User::factory())->create();
    $unit = WorkUnit::factory()->create();
    WorkUnitCoordinator::create(['work_unit_id' => $unit->id, 'user_id' => $user->id, 'starts_at' => today()->subDays(5)]);
    $latest = WorkUnitCoordinator::create(['work_unit_id' => $unit->id, 'user_id' => $followers->first()->id, 'starts_at' => today()->subDay()]);
    ReportEvaluation::factory()->create(['report_id' => $evaluationReport->id, 'work_unit_id' => $unit->id]);
    $token = app(TokenService::class)->issue($user, 'Evaluation compatibility', ['reports', 'evaluations'], [
        'reports' => ['what'], 'evaluations' => ['id', 'report_id', 'kendala'],
    ], 'account', 7);
    $expect($get('evaluations')['meta']['total'] === 0, 'Older overlapping coordinator gained access on MySQL.');
    $latest->update(['user_id' => $user->id]);
    $expect($get('evaluations')['meta']['total'] === 1, 'Current coordinator could not read evaluation on MySQL.');
    $evaluationReport->delete();
    $expect($get('evaluations')['meta']['total'] === 0, 'Deleted parent evaluation was exposed on MySQL.');
    echo json_encode(['passed' => true, 'engine' => $admin->selectOne('SELECT VERSION() AS version')->version, 'isolated_database' => $id, 'business_data_unchanged' => true]);
} finally {
    DB::disconnect('mysql');
    if ($created) {
        $admin->statement('DROP DATABASE `'.$id.'`');
    }
    DB::disconnect('ai_compat_admin');
}
