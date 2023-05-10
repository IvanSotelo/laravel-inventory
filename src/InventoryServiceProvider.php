<?php

namespace IvanSotelo\Inventory;

use IvanSotelo\Inventory\Commands\InventoryCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class InventoryServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-inventory')
            ->hasConfigFile()
            ->hasMigration('create_inventories_table')
            ->hasCommand(InventoryCommand::class);
    }
}
