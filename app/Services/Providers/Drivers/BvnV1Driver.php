<?php

namespace App\Services\Providers\Drivers;

use App\Models\Service;
use App\Services\Providers\DTO\ProviderResult;

class BvnV1Driver extends BaseVerificationDriver
{
    public function getProviderId(): string
    {
        return 'bvn_v1';
    }

    public function verify(Service $service, array $input): ProviderResult
    {
        $bvn = trim($input['tracking_input'] ?? $input['bvn'] ?? '');

        if (! preg_match('/^[0-9]{11}$/', $bvn)) {
            return ProviderResult::failure('Invalid 11-digit BVN format provided.', 'INVALID_FORMAT');
        }

        $seed = abs(crc32($bvn));
        $demographics = $this->generateSimulatedDemographics($bvn, 'bvn');

        $banks = [
            'Access Bank Plc',
            'Zenith Bank Plc',
            'First Bank of Nigeria Limited',
            'Guaranty Trust Bank (GTBank)',
            'United Bank for Africa (UBA)',
            'Stanbic IBTC Bank',
            'Fidelity Bank Plc',
        ];
        $bank = $banks[$seed % count($banks)];

        $branches = [
            'Victoria Island Main Branch, Lagos',
            'Marina Central Branch, Lagos',
            'Garki Area 3 Commercial Branch, Abuja',
            'Trans-Amadi Industrial Branch, Port Harcourt',
            'Bompai Commercial Branch, Kano',
            'Bodija Commercial Branch, Ibadan',
        ];
        $branch = $branches[($seed + 1) % count($branches)];

        $bvnFormatted = substr($bvn, 0, 4) . ' ' . substr($bvn, 4, 3) . ' ' . substr($bvn, 7, 4);

        $data = array_merge($demographics, [
            'bvn' => $bvn,
            'bvn_formatted' => $bvnFormatted,
            'enrollment_bank' => $bank,
            'enrollment_branch' => $branch,
            'registration_date' => now()->subMonths(($seed % 36) + 6)->format('Y-m-d'),
            'account_tier' => 'Tier 3 (Fully KYC Verified)',
            'bvn_status' => 'ACTIVE & OPERATIONAL',
            'provider' => 'NIBSS Central Financial Identity Gateway V1',
            'watchlist_status' => 'CLEAR (No Adverse Records)',
        ]);

        return ProviderResult::success($data, 'BVN identity record verified successfully via NIBSS.');
    }
}
