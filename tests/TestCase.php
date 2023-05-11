<?php

namespace IvanSotelo\Inventory\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Schema\Blueprint;
use IvanSotelo\Inventory\Models\InventoryStock;
use IvanSotelo\Inventory\InventoryServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected $inventoryModel;

    protected $secondInventoryModel;

    public function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'IvanSotelo\\Inventory\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );

        $this->setUpDatabase($this->app);

        $this->loadMigrationsFrom(__DIR__.'/../migrations');

        $this->inventoryModel = InventoryModel::first();

        $this->secondInventoryModel = InventoryModel::find(2);
    }

    protected function getPackageProviders($app)
    {
        return [
            InventoryServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        /*
        include_once __DIR__.'/../database/migrations/create_inventory_table.php.stub';
        (new \CreatePackageTable())->up();
        */
    }

    protected function setUpDatabase($app)
    {
        $builder = $app['db']->connection()->getSchemaBuilder();

        $builder->create('inventory_models', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
        });

        $builder->create('inventory_stocks', function (Blueprint $table) {
            $table->id();
            $table->decimal('quantity', 8, 2)->default(0);
            $table->text('description')->nullable();
            $table->string('inventoriable_type');
            $table->unsignedBigInteger('inventoriable_id');
            $table->index(['inventoriable_type', 'inventoriable_id']);
            $table->integer('user_id')->unsigned()->nullable();

            $table->integer('location_id')->unsigned()->nullable();
            $table->timestamps();
        });

        InventoryModel::create([
            'name' => 'InventoryModel',
        ]);

        InventoryModel::create([
            'name' => 'SecondInventoryModel',
        ]);

        InventoryStock::create([
            'quantity' => 0,
            'description' => 'Inventory description',
            'inventoriable_type' => 'IvanSotelo\Inventory\Tests\InventoryModel',
            'inventoriable_id' => '1',
        ]);

        InventoryStock::create([
            'quantity' => 10,
            'description' => 'Inventory description',
            'inventoriable_type' => 'IvanSotelo\Inventory\Tests\InventoryModel',
            'inventoriable_id' => '2',
        ]);
    }
}
