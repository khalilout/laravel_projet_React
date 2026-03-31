<?php


require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Hash;
use App\Models\User;

$username = 'apitest';
if (User::where('username', $username)->exists()) {
    $user = User::where('username', $username)->first();
    echo $user->api_token . PHP_EOL;
    exit;
}

$user = User::create([
    'nom_complet' => 'API Test',
    'username' => $username,
    'password' => Hash::make('secret'),
]);

$token = $user->generateApiToken();
echo $token . PHP_EOL;
