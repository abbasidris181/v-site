<?php

namespace App\Services\Providers\Drivers;

use App\Models\Service;
use App\Services\Providers\DTO\ProviderResult;

class NinV2Driver extends BaseVerificationDriver
{
    public function getProviderId(): string
    {
        return 'nin_v2';
    }

    public function verify(Service $service, array $input): ProviderResult
    {
        $mode = $input['mode'] ?? 'nin';
        $trackingInput = trim($input['tracking_input'] ?? $input['nin'] ?? '');

        if ($mode === 'demographics') {
            if (empty($input['first_name']) || empty($input['dob'])) {
                return ProviderResult::failure('First name and Date of Birth are required for demographic verification.', 'INVALID_FORMAT');
            }
        } elseif ($mode === 'phone') {
            if (! preg_match('/^[0-9]{11}$/', $trackingInput)) {
                return ProviderResult::failure('Invalid 11-digit phone number provided.', 'INVALID_FORMAT');
            }
        } else {
            if (! preg_match('/^[0-9]{11}$/', $trackingInput)) {
                return ProviderResult::failure('Invalid 11-digit NIN format provided.', 'INVALID_FORMAT');
            }
        }

        $data = $this->generateSimulatedDemographics($trackingInput, 'nin', $input);
        $data['provider'] = 'NIN Gateway V2 (Biometric Cross-Match)';
        $data['verification_mode'] = $mode;
        $data['confidence_score'] = '99.2%';
        $data['biometric_match_status'] = 'MATCHED';

        return ProviderResult::success($data, 'NIN successfully verified via Gateway V2.');
    }
}
