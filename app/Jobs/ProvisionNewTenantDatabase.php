<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Spatie\Multitenancy\Jobs\NotTenantAware;
use Spatie\Multitenancy\Models\Tenant;

class ProvisionNewTenantDatabase implements ShouldQueue, NotTenantAware
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Tenant $tenant;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Tenant $tenant)
    {
        $this->tenant = $tenant;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $characterSet = config('database.connections.tenant.charset', 'utf8mb4');
        $collation = config('database.connections.tenant.collation', 'utf8mb4_0900_ai_ci');
        $databaseName = $this->tenant->database;

        DB::connection('landlord')
            ->statement("CREATE DATABASE IF NOT EXISTS {$databaseName} CHARACTER SET {$characterSet} COLLATE {$collation}");

        Artisan::call('tenants:artisan', [
            'artisanCommand' => 'migrate --database=tenant --force',
            '--tenant' => $this->tenant->id,
        ]);
    }
}
