<?php

namespace IvanSotelo\Inventory\Commands;

use Illuminate\Console\Command;

class InventoryCommand extends Command
{
    public $signature = 'laravel-inventory';

    public $description = 'My command';

    public function handle()
    {
        $this->comment('All done');
    }
}
