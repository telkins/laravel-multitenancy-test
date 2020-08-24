<?php

namespace Tests\Feature;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Spatie\Multitenancy\Concerns\UsesMultitenancyConfig;
use Spatie\Multitenancy\Models\Tenant;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase; // Overridden refreshTestDatabase() method below.  See note...
    use UsesMultitenancyConfig;

    protected $game;

    // protected function connectionsToTransact()
    // {
    //     return [
    //         $this->landlordDatabaseConnectionName(),
    //         $this->tenantDatabaseConnectionName(),
    //     ];
    // }

    /**
     * Refresh a conventional test database.
     *
     * NOTE: Initially, tests fail because refreshTestDatabase() migrates the
     * tenant migrations.  This override is provided to refresh the landlord
     * database each time.
     *
     * @return void
     */
    protected function refreshTestDatabase()
    {
        if (! RefreshDatabaseState::$migrated) {
            // First landlord...
            $this->artisan('migrate:fresh', [
                '--drop-views' => $this->shouldDropViews(),
                '--drop-types' => $this->shouldDropTypes(),
                '--path' => 'database/migrations/landlord', // <-- these two lines are added...
                '--database' => 'landlord',                 // <-- these two lines are added...
            ]);

            // Then tenants...?
            // ...

            $this->app[Kernel::class]->setArtisan(null);

            RefreshDatabaseState::$migrated = true;
        }

        $this->beginDatabaseTransaction();
    }

    /** @test */
    public function it_works()
    {
        $tenant = factory(Tenant::class)->create();

        $anotherTenant = factory(Tenant::class)->create();

        $this->assertTrue(true);
    }
}
