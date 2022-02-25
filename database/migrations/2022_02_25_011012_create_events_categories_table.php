<?php

use App\Models\Event;
use App\Models\SupplierCategory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('events_categories', function (Blueprint $table) {
            $table->decimal('budget');
            $table->foreignIdFor(Event::class);
            $table->foreignIdFor(SupplierCategory::class);
            $table->primary(['event_id', 'supplier_category_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('events_categories');
    }
};
