<?php

namespace App\Services\Providers;

use App\Services\Providers\Contracts\VerificationProviderInterface;
use App\Services\Providers\Drivers\BvnV1Driver;
use App\Services\Providers\Drivers\NinV1Driver;
use App\Services\Providers\Drivers\NinV2Driver;
use App\Services\Providers\Drivers\NinV3Driver;
use InvalidArgumentException;

class ProviderManager
{
    /**
     * @var array<string, VerificationProviderInterface>
     */
    protected array $drivers = [];

    public function __construct()
    {
        $this->register(new NinV1Driver());
        $this->register(new NinV2Driver());
        $this->register(new NinV3Driver());
        $this->register(new BvnV1Driver());
    }

    /**
     * Register a provider driver instance into the manager.
     */
    public function register(VerificationProviderInterface $driver): void
    {
        $this->drivers[$driver->getProviderId()] = $driver;
    }

    /**
     * Resolve a provider driver by identifier.
     */
    public function driver(?string $name = null): VerificationProviderInterface
    {
        $key = $name ?? 'nin_v1';

        if (! isset($this->drivers[$key])) {
            throw new InvalidArgumentException("Verification provider driver [{$key}] is not registered.");
        }

        return $this->drivers[$key];
    }

    /**
     * Get list of all available driver IDs.
     *
     * @return list<string>
     */
    public function getAvailableDriverIds(): array
    {
        return array_keys($this->drivers);
    }
}
