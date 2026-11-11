<?php

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;

it('does not grant administrators implicitly based on email domain', function () {
    $user = User::factory()->create(['email' => 'person@betterbloq.com']);
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->seed(RolesAndPermissionsSeeder::class);
    expect(Role::where('name', 'Super Admin')->count())->toBe(1)
        ->and($user->fresh()->hasRole('Super Admin'))->toBeFalse();
    $this->artisan('app:set-user-role')->assertFailed();
    expect($user->fresh()->hasRole('Super Admin'))->toBeFalse();
});

it('refuses to grant administrator access to an unverified account', function () {
    $user = User::factory()->unverified()->create(['email' => 'person@betterbloq.com']);
    $this->artisan('app:set-user-role', ['email' => $user->email])->assertFailed();
    expect($user->fresh()->roles()->count())->toBe(0);
});

it('does not create a privileged default account in production or staging', function (string $environment) {
    app()->instance('env', $environment);
    try {
        expect(fn () => (new DatabaseSeeder)->run())->toThrow(RuntimeException::class, 'Demo seeding is disabled');
        expect(User::count())->toBe(0)->and(Role::count())->toBe(0);
        Mail::assertNothingSent();
    } finally {
        app()->instance('env', 'testing');
    }
})->with(['production', 'staging']);
