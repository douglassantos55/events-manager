<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        /**
         * @var User
         */
        $user = \App\Models\User::factory()->hasRoles(3)->create();
        $user->role_id = $user->roles()->get()->random()->first()->id;
        $user->push();
    }
}
