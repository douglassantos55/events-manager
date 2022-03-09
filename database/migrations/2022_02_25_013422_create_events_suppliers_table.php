<?php

use App\Models\Supplier;
use App\Models\EventCategory;
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
        Schema::create('events_suppliers', function (Blueprint $table) {
            $table->id();
            $table->decimal('value');
            $table->string('status')->default('pending');
            $table->foreignIdFor(Supplier::class);
            $table->foreignIdFor(EventCategory::class)->constrained('events_categories')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('events_suppliers');
    }
};
