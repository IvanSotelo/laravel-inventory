<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryTable extends Migration
{
    public function up()
    {
        Schema::create('inventory_stocks', function (Blueprint $table) {
            $table->id();

            $table->integer('user_id')->unsigned()->nullable();

            $table->string('inventoriable_type');
            $table->unsignedBigInteger('inventoriable_id');
            $table->index(['inventoriable_type', 'inventoriable_id']);

            $table->integer('location_id')->unsigned();
            $table->decimal('quantity', 8, 2)->default(0);
            $table->text('description')->nullable();
            $table->string('aisle')->nullable();
            $table->string('row')->nullable();
            $table->string('bin')->nullable();

            /*
             * This allows only one inventory stock
             * to be created on a single location
             */
            $table->unique(['inventoriable_id', 'location_id']);

            $table->foreign('user_id')->references('id')->on('users')
                ->onUpdate('restrict')
                ->onDelete('set null');

            $table->foreign('location_id')->references('id')->on('locations')
                ->onUpdate('restrict')
                ->onDelete('cascade');

            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->softDeletes();

            $table->integer('inventory_id')->unsigned();
            $table->integer('user_id')->unsigned()->nullable();
            $table->decimal('before', 8, 2)->default(0);
            $table->decimal('after', 8, 2)->default(0);
            $table->decimal('cost', 8, 2)->default(0)->nullable();
            $table->nullableMorphs('receiver');
            $table->string('reason')->nullable();
            $table->boolean('returned')->default(0);

            $table->foreign('inventory_id')->references('id')->on('inventories')
                ->onUpdate('restrict')
                ->onDelete('cascade');

            $table->foreign('user_id')->references('id')->on('users')
                ->onUpdate('restrict')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inventory_movements');
        Schema::dropIfExists('inventory_stocks');
    }
}
