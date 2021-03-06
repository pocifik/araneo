<?php

namespace App\Console\Commands;

use App\Proxy;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class PurgeCommand extends Command
{
    protected $signature = 'araneo:purge {hours}';
    protected $description = 'Purge proxies that are not working.';
    protected $proxy;

    public function __construct(Proxy $proxy)
    {
        $this->proxy = $proxy;

        parent::__construct();
    }

    public function handle()
    {
        $hours = $this->argument('hours');
        $acceptableTTL = Carbon::now()->subHour($hours);

        $this->info(sprintf('Searching for proxies with last working at is lower than %s.', $acceptableTTL));

        $proxies = $this->proxy
            ->where(function (Builder $query) use ($acceptableTTL) {
                $query->whereDate('last_worked_at', '<=', $acceptableTTL)
                    ->whereLastStatus(Proxy::STATUS_FAILED);
            })
            ->orWhere(function (Builder $query) use ($acceptableTTL) {
                $query->whereNull('last_worked_at')
                    ->whereDate('created_at', '<=', $acceptableTTL);
            })
            ->get();

        $this->info(sprintf('Found %s proxies.', $proxies->count()));

        foreach ($proxies as $proxy) {
            if ($proxy->delete()) {
                $this->info(sprintf('Proxy has been deleted: #%s.', $proxy->id));
            }
        }

        $this->info('Job is done.');
    }
}
