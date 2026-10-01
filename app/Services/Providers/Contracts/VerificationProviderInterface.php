<?php

namespace App\Services\Providers\Contracts;

use App\Models\Service;
use App\Services\Providers\DTO\ProviderResult;

interface VerificationProviderInterface
{
    /**
     * Get the unique provider driver identifier (e.g. 'nin_v1', 'nin_v2', 'nin_v3', 'bvn_v1').
     */
    public function getProviderId(): string;

    /**
     * Execute identity verification against this provider driver.
     *
     * @param  Service  $service
     * @param  array<string, mixed>  $input
     * @return ProviderResult
     */
    public function verify(Service $service, array $input): ProviderResult;
}
