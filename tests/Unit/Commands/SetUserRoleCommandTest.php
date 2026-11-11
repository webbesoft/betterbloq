<?php

namespace Tests\Unit\Commands;

use App\Models\User;
use Spatie\Permission\Models\Role;

it('sets the Super Admin role only for betterbloq accounts', function () {
    Role::findOrCreate('Super Admin', 'web');
    $admin = User::factory()->create(['email' => 'admin@betterbloq.com']);
    $customer = User::factory()->create(['email' => 'customer@example.org']);

    $this->artisan('app:set-user-role', ['email' => $admin->email])->assertSuccessful();

    expect($admin->fresh()->hasRole('Super Admin'))->toBeTrue()
        ->and($customer->fresh()->hasRole('Super Admin'))->toBeFalse();
});
