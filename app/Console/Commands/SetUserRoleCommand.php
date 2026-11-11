<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

class SetUserRoleCommand extends Command
{
    protected $signature = 'app:set-user-role {email? : The verified account to grant Super Admin}';

    protected $description = 'Grant Super Admin to one explicitly selected, verified account';

    public function handle(): int
    {
        $email = $this->argument('email');
        if (! is_string($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Supply the exact email of the verified administrator account. No roles were changed.');

            return self::FAILURE;
        }
        $user = User::where('email', $email)->first();
        if (! $user || ! $user->hasVerifiedEmail()) {
            $this->error('The selected account must exist and have a verified email. No roles were changed.');

            return self::FAILURE;
        }
        $user->assignRole(Role::findOrCreate('Super Admin', 'web'));
        Log::notice('Administrator access explicitly granted from the console.', ['target_user_id' => $user->id]);
        $this->info('Administrator access granted to the selected account.');

        return self::SUCCESS;
    }
}
