<?php

namespace Tests\Unit\Commands;

use App\Models\User;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\artisan;

it('should set the Super Admin role for all example.org accounts', function () {
    $users = User::factory()->create([
        'email' => $this->faker->name.'@example.org',
    ]);

    artisan('account:plan-check')->run();

    $modelRoles = DB::table('model_has_roles')->count();

    expect($modelRoles)->toBe(1);
});
