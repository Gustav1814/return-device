<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$email = $argv[1] ?? 'admin@returndevice.com';
$newPassword = $argv[2] ?? 'password';

$user = App\Models\User::query()->where('email', $email)->first();
if (!$user) {
    fwrite(STDERR, "User not found: {$email}\n");
    exit(1);
}

$user->password = Illuminate\Support\Facades\Hash::make($newPassword);
$user->save();

fwrite(STDOUT, "OK: reset {$email}\n");

