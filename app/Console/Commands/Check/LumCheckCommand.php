<?php

namespace App\Console\Commands\Check;

use App\Jobs\LumCheckJob;
use App\Proxy;
use Carbon\Carbon;
use Illuminate\Console\Command;

class LumCheckCommand extends Command
{
    const BATCH_SIZE = 10;

    protected $signature = 'araneo:check:lumtest {minutes}';
    protected $description = 'Test all proxies by a given amount of time.';
    protected $proxy;

    public function __construct(Proxy $proxy)
    {
        $this->proxy = $proxy;

        parent::__construct();
    }

    public function handle()
    {
        $minutes = $this->argument('minutes');
        $acceptableTTL = Carbon::now()->subMinutes($minutes);

        $this->info(sprintf('Searching for proxies with last checked at is higher than %s.', $acceptableTTL));

        $proxies = $this->proxy
            ->whereDate('last_checked_at', '>=', $acceptableTTL)
            ->get();

        $this->info(sprintf('Found %s proxies.', $proxies->count()));
        $this->info(sprintf('Using thunks of %s.', self::BATCH_SIZE));

        foreach ($proxies->chunk(self::BATCH_SIZE) as $batch) {
            dispatch(new LumCheckJob($batch));
        }

        $this->info('Job is done.');
    }
}
