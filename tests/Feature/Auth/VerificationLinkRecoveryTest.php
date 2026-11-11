<?php

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Inertia\Testing\AssertableInertia as Assert;

it('offers a fresh link instead of a dead end for an expired verification URL', function () {
    $user = User::factory()->unverified()->create();
    Event::fake([Verified::class]);
    $url = URL::temporarySignedRoute('verification.verify', now()->subMinute(), [
        'id' => $user->id, 'hash' => sha1($user->email),
    ]);
    $this->actingAs($user)->get($url)->assertRedirect(route('verification.notice'))
        ->assertSessionHas('status', 'verification-link-invalid');
    $this->get(route('verification.notice'))->assertInertia(fn (Assert $page) => $page
        ->component('auth/verify-email')->where('status', 'verification-link-invalid'));
    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
    Event::assertNotDispatched(Verified::class);
});

it('does not verify a user when the signature is tampered with', function () {
    $user = User::factory()->unverified()->create();
    $url = URL::temporarySignedRoute('verification.verify', now()->addHour(), [
        'id' => $user->id, 'hash' => sha1($user->email),
    ]);
    $url = preg_replace('/signature=[a-f0-9]+/', 'signature=invalid', $url);
    $this->actingAs($user)->get($url)->assertRedirect(route('verification.notice'));
    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

it('keeps JSON signature failures forbidden', function () {
    $user = User::factory()->unverified()->create();
    $this->actingAs($user)->getJson(route('verification.verify', [
        'id' => $user->id, 'hash' => sha1($user->email),
    ]))->assertForbidden();
});

it('can resend a fresh notification and verify with its signed URL', function () {
    $user = User::factory()->unverified()->create();
    Notification::fake();
    $this->actingAs($user)->from(route('verification.notice'))->post(route('verification.send'))
        ->assertRedirect(route('verification.notice'))->assertSessionHas('status', 'verification-link-sent');
    $notification = Notification::sent($user, VerifyEmail::class)->first();
    expect($notification)->toBeInstanceOf(VerifyEmail::class);
    $url = $notification->toMail($user)->actionUrl;
    $this->get($url)->assertRedirect('/dashboard?verified=1');
    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
});

it('explains log delivery only in the local environment', function () {
    $user = User::factory()->unverified()->create();
    config(['mail.default' => 'log']);
    app()->instance('env', 'local');
    try {
        $this->actingAs($user)->get(route('verification.notice'))->assertInertia(fn (Assert $page) => $page
            ->where('deliveryMode', 'log'));
        app()->instance('env', 'production');
        $this->get(route('verification.notice'))->assertInertia(fn (Assert $page) => $page
            ->where('deliveryMode', 'mail'));
    } finally {
        app()->instance('env', 'testing');
    }
});

it('does not verify a different authenticated account with a valid link', function () {
    $owner = User::factory()->unverified()->create();
    $other = User::factory()->unverified()->create();
    $url = URL::temporarySignedRoute('verification.verify', now()->addHour(), [
        'id' => $owner->id, 'hash' => sha1($owner->email),
    ]);
    $this->actingAs($other)->get($url)->assertForbidden();
    expect($owner->fresh()->hasVerifiedEmail())->toBeFalse()
        ->and($other->fresh()->hasVerifiedEmail())->toBeFalse();
});

it('signs a resent link for the HTTPS request origin even when the configured CLI URL differs', function () {
    config(['app.url' => 'http://localhost']);
    $user = User::factory()->unverified()->create();
    Notification::fake();
    $this->actingAs($user)->from('https://betterbloq.test/verify-email')
        ->post('https://betterbloq.test/email/verification-notification')->assertRedirect('https://betterbloq.test/verify-email');
    $notification = Notification::sent($user, VerifyEmail::class)->first();
    $url = $notification->toMail($user)->actionUrl;
    expect($url)->toStartWith('https://betterbloq.test/verify-email/');
    $this->get($url)->assertRedirect('/dashboard?verified=1');
    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
});
