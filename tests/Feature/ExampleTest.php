<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Spatie\Multitenancy\Concerns\UsesMultitenancyConfig;
use Spatie\Multitenancy\Models\Tenant;
use Tests\CreatesApplication;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase; // Overridden refreshTestDatabase() method below.  See note...
    use UsesMultitenancyConfig;

    protected $tenant;
    protected $anotherTenant;

    public function setUp(): void
    {
        parent::setUp();

        $this->assertSame(0, Tenant::count(), 'The landlord.tenants table should be empty...right...?');

        $this->tenant = factory(Tenant::class)->create();
        $this->anotherTenant = factory(Tenant::class)->create();
    }

    protected function connectionsToTransact()
    {
        return [
            $this->landlordDatabaseConnectionName(),
            // $this->tenantDatabaseConnectionName(),
        ];
    }

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
            // First tenants...
            Tenant::all()->eachCurrent(function ($tenant) {
                $this->artisan('migrate:fresh --database=tenant');
            });

            // Then landlord...
            $this->artisan('migrate:fresh', [
                '--drop-views' => $this->shouldDropViews(),
                '--drop-types' => $this->shouldDropTypes(),
                '--path' => 'database/migrations/landlord', // <-- these two lines are added...
                '--database' => 'landlord',                 // <-- these two lines are added...
            ]);

            $this->app[Kernel::class]->setArtisan(null);

            RefreshDatabaseState::$migrated = true;
        }

        $this->beginDatabaseTransaction();
    }

    /** @test */
    public function it_works()
    {
        $this->tenant->makeCurrent();
        $user = factory(User::class)->create(['email' => 'me@mydomain.com']);
        $this->assertDatabaseCount('users', 1, 'tenant');

        $this->anotherTenant->makeCurrent();
        $user = factory(User::class)->create(['email' => 'me@mydomain.com']);
        $this->assertDatabaseCount('users', 1, 'tenant');

        $this->tenant->makeCurrent();
        $this->assertDatabaseCount('users', 1, 'tenant');
    }

    /** @test */
    public function it_still_works()
    {
        $this->tenant->makeCurrent();
        $user = factory(User::class)->create(['email' => 'me@mydomain.com']);
        $this->assertDatabaseCount('users', 1, 'tenant');

        $this->anotherTenant->makeCurrent();
        $user = factory(User::class)->create(['email' => 'me@mydomain.com']);
        $this->assertDatabaseCount('users', 1, 'tenant');

        $this->tenant->makeCurrent();
        $this->assertDatabaseCount('users', 1, 'tenant');
    }
}
