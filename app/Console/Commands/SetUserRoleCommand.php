<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class SetUserRoleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:set-user-role';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will set the admin user account as an account admin.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        $user = User::where('email', '=', 'admin@betterbloq.com')->first();

        $user->assignRole('Super Admin');
    }
}
