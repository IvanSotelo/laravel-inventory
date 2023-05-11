<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class ModifyInventoryTableForAssemblies extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table(config('inventory.inventory_table'), function (Blueprint $table) {
            $table->boolean('is_assembly')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table(config('inventory.inventory_table'), function (Blueprint $table) {
            $table->dropColumn('is_assembly');
        });
    }
}