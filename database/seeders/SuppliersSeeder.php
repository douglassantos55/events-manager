<?php

namespace Database\Seeders;

use App\Models\SupplierCategory;
use Illuminate\Database\Seeder;

class SuppliersSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        SupplierCategory::factory(5)->hasSuppliers(5)->create();
    }
}
