<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Finance\TrialPlanService;

class ExpireTrialPlansCommand extends Command
{
    protected $signature = 'trial:expire';

    protected $description = 'Expire old trial plans';

    public function handle()
    {
        app(TrialPlanService::class)->expireTrialPlans();

        $this->info('Trial plans expired');

        return Command::SUCCESS;
    }
}