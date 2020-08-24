<?php

namespace App\Observers;

use App\Jobs\ProvisionNewTenantDatabase;
use Spatie\Multitenancy\Models\Tenant;

class TenantObserver
{
    /**
     * Handle the tenant "created" event.
     *
     * @param  \Spatie\Multitenancy\Models\Tenant  $tenant
     * @return void
     */
    public function created(Tenant $tenant)
    {
        $tenant->database = "tenant_{$tenant->id}";
        $tenant->save();

        ProvisionNewTenantDatabase::dispatch($tenant);
    }

    /**
     * Handle the tenant "updated" event.
     *
     * @param  \Spatie\Multitenancy\Models\Tenant  $tenant
     * @return void
     */
    public function updated(Tenant $tenant)
    {
        //
    }

    /**
     * Handle the tenant "deleted" event.
     *
     * @param  \Spatie\Multitenancy\Models\Tenant  $tenant
     * @return void
     */
    public function deleted(Tenant $tenant)
    {
        //
    }

    /**
     * Handle the tenant "restored" event.
     *
     * @param  \Spatie\Multitenancy\Models\Tenant  $tenant
     * @return void
     */
    public function restored(Tenant $tenant)
    {
        //
    }

    /**
     * Handle the tenant "force deleted" event.
     *
     * @param  \Spatie\Multitenancy\Models\Tenant  $tenant
     * @return void
     */
    public function forceDeleted(Tenant $tenant)
    {
        //
    }
}
