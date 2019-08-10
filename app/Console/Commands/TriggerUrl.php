<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TriggerUrlService;

class TriggerUrl extends Command
{
    private $triggerUrlService;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trigger:url';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Trigger Url to create cache';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(TriggerUrlService $service)
    {
        parent::__construct();
	$this->triggerUrlService = $service;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //
	$this->triggerUrlService->trigger("123");
    }
}
