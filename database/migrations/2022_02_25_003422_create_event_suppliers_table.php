<?php

use App\Models\Event;
use App\Models\Supplier;
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
            $table->decimal('value');
            $table->string('status')->default('pending');
            $table->foreignIdFor(Event::class);
            $table->foreignIdFor(Supplier::class);
            $table->primary(['supplier_id', 'event_id']);
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
