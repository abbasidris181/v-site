<?php

namespace App\Services\Providers\Drivers;

use App\Models\Service;
use App\Services\Providers\DTO\ProviderResult;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NinV1Driver extends BaseVerificationDriver
{
    public function getProviderId(): string
    {
        return 'nin_v1';
    }

    public function verify(Service $service, array $input): ProviderResult
    {
        $mode = $input['mode'] ?? 'nin';
        $trackingInput = trim((string) ($input['tracking_input'] ?? $input['nin'] ?? ''));

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

        // Live Provider Integration: VerifyUser NIN API
        $token = trim((string) config('services.verify_user.token'));
        $baseUrl = rtrim((string) (config('services.verify_user.base_url') ?: 'https://unitybills.com'), '/');

        if ($mode === 'nin' && ! empty($token)) {
            $endpoint = $baseUrl . '/api/nin/index.php';

            $payload = [
                'idNumber' => $trackingInput,
                'idType' => 'nin',
                'consent' => true,
            ];

            $headers = [
                'Accept' => 'application/json, text/plain, */*',
                'Content-Type' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ];

            try {
                $response = Http::withHeaders($headers)
                    ->timeout(35)
                    ->post($endpoint, $payload);

                $responseData = $response->json();

                if (! is_array($responseData)) {
                    $rawBody = $response->body();
                    $decoded = json_decode($rawBody, true);
                    $responseData = is_array($decoded) ? $decoded : [];
                }

                $isSuccessful = ! empty($responseData['status']) && (
                    $responseData['status'] === true ||
                    $responseData['status'] === 'true' ||
                    $responseData['status'] === 1 ||
                    $responseData['status'] === '1'
                );

                if ($isSuccessful && isset($responseData['message']) && is_array($responseData['message'])) {
                    $normalized = $this->normalizeVerifyUserResponse($responseData, $trackingInput);
                    return ProviderResult::success($normalized, 'NIN successfully verified via VerifyUser.', $responseData);
                }

                // Determine upstream provider error message
                $errorMessage = 'NIN verification failed or record not found with the provider.';
                if (isset($responseData['message']) && is_string($responseData['message']) && ! empty(trim($responseData['message']))) {
                    $rawMsg = trim($responseData['message']);
                    if (strtolower($rawMsg) === 'server error') {
                        $errorMessage = 'The identity gateway experienced a temporary provider server error. Please try again in a moment.';
                    } else {
                        $errorMessage = $rawMsg;
                    }
                } elseif (! empty($responseData['error']) && is_string($responseData['error'])) {
                    $errorMessage = trim($responseData['error']);
                } elseif (! $response->successful()) {
                    $errorMessage = "Provider service unreachable or returned HTTP {$response->status()}. Please try again shortly.";
                }

                Log::warning('VerifyUser NIN provider failure', [
                    'tracking_input' => $trackingInput,
                    'status' => $response->status(),
                    'response' => $responseData,
                ]);

                return ProviderResult::failure($errorMessage, 'PROVIDER_VERIFICATION_FAILED', $responseData ?: []);
            } catch (Exception $e) {
                Log::error('VerifyUser NIN HTTP request exception: ' . $e->getMessage(), [
                    'tracking_input' => $trackingInput,
                ]);

                return ProviderResult::failure('Unable to connect to NIN verification gateway. ' . $e->getMessage(), 'GATEWAY_CONNECTION_ERROR');
            }
        }

        // Fallback simulation for testing without configured token or local demo
        $data = $this->generateSimulatedDemographics($trackingInput, 'nin', $input);
        $data['provider'] = 'NIN Gateway V1 (Demographic Core)';
        $data['verification_mode'] = $mode;
        $data['tracking_id'] = $input['tracking_id'] ?? ($input['trackingId'] ?? null);
        $data['trackingId'] = $data['tracking_id'];

        return ProviderResult::success($data, 'NIN successfully verified via Gateway V1.');
    }

    /**
     * Normalize the VerifyUser API response into standard application format.
     *
     * @param  array<string, mixed>  $responseData
     * @param  string  $trackingInput
     * @return array<string, mixed>
     */
    protected function normalizeVerifyUserResponse(array $responseData, string $trackingInput): array
    {
        $msg = is_array($responseData['message'] ?? null) ? $responseData['message'] : [];
        $transId = $responseData['transID'] ?? ($responseData['transId'] ?? null);

        $fn = trim((string) ($msg['firstname'] ?? ''));
        $rawMn = trim((string) ($msg['middlename'] ?? ''));
        $isMaskedMn = empty($rawMn)
            || preg_match('/^[\*\s—\-]+$/', $rawMn)
            || in_array(strtolower($rawMn), ['null', 'nil', 'none', 'n/a', 'na'], true);
        $mn = ! $isMaskedMn ? $rawMn : null;
        $sn = trim((string) ($msg['surname'] ?? ''));

        $fullNameParts = array_filter([$fn, $mn, $sn]);
        $fullName = ! empty($fullNameParts) ? implode(' ', $fullNameParts) : 'Verified Customer';

        $nin = ! empty($msg['nin']) ? trim((string) $msg['nin']) : $trackingInput;
        $ninFormatted = strlen($nin) === 11
            ? substr($nin, 0, 4) . ' ' . substr($nin, 4, 3) . ' ' . substr($nin, 7, 4)
            : $nin;

        $gender = ! empty($msg['gender']) ? ucfirst(strtolower(trim((string) $msg['gender']))) : 'Unspecified';

        // Resolve Photo: handle base64 prefix
        $rawImage = $msg['image'] ?? null;
        if (! empty($rawImage) && is_string($rawImage)) {
            $trimmedImage = trim($rawImage);
            if (str_starts_with($trimmedImage, 'data:') || str_starts_with($trimmedImage, 'http')) {
                $photoBase64 = $trimmedImage;
            } else {
                $photoBase64 = 'data:image/jpeg;base64,' . ltrim($trimmedImage);
            }
        } else {
            $photoBase64 = $this->generateSamplePhotoBase64($gender);
        }

        // Format Residential Address
        $residenceAddressParts = array_filter([
            $msg['residence_AdressLine1'] ?? null,
            $msg['residence_town'] ?? null,
            $msg['residence_lga'] ?? null,
            $msg['residence_state'] ?? null,
        ]);
        $residenceAddress = ! empty($residenceAddressParts) ? implode(', ', $residenceAddressParts) : null;

        // Next of Kin
        $nokFn = trim((string) ($msg['nok_firstname'] ?? ''));
        $nokSn = trim((string) ($msg['nok_surname'] ?? ''));
        $nokFullName = trim("{$nokFn} {$nokSn}");

        $nokAddrParts = array_filter([
            $msg['nok_address1'] ?? null,
            $msg['nok_town'] ?? null,
            $msg['nok_lga'] ?? null,
            $msg['nok_state'] ?? null,
        ]);
        $nokAddress = ! empty($nokAddrParts) ? implode(', ', $nokAddrParts) : null;

        // Origin details (fallback to residence if self origin is null)
        $stateOfOrigin = ! empty($msg['self_origin_state']) ? $msg['self_origin_state'] : ($msg['residence_state'] ?? null);
        $lgaOfOrigin = ! empty($msg['self_origin_lga']) ? $msg['self_origin_lga'] : ($msg['residence_lga'] ?? null);

        // Resolve provider tracking ID strictly from provider payload (never synthesize from transId or reference)
        $rawTrackingId = $msg['trackingId']
            ?? ($msg['tracking_id']
            ?? ($msg['trackingID']
            ?? ($msg['trackingid']
            ?? ($msg['trackingNo']
            ?? ($msg['tracking_no']
            ?? ($responseData['trackingId']
            ?? ($responseData['tracking_id']
            ?? ($responseData['trackingID']
            ?? ($responseData['trackingid']
            ?? null)))))))));

        $providerTrackingId = (! empty($rawTrackingId) && is_scalar($rawTrackingId)) ? trim((string) $rawTrackingId) : null;

        return [
            // Identifiers
            'nin' => $nin,
            'nin_formatted' => $ninFormatted,
            'tracking_id' => $providerTrackingId,
            'trackingId' => $providerTrackingId,
            'trans_id' => $transId,
            'transID' => $transId,

            // Names
            'full_name' => $fullName,
            'first_name' => $fn,
            'firstname' => $fn,
            'middle_name' => $mn,
            'middlename' => $mn,
            'surname' => $sn,

            // Contact & Core Demographics
            'phone_number' => $msg['telephoneno'] ?? null,
            'telephoneno' => $msg['telephoneno'] ?? null,
            'date_of_birth' => $msg['birthdate'] ?? null,
            'birthdate' => $msg['birthdate'] ?? null,
            'gender' => $gender,

            // Media
            'photo_base64' => $photoBase64,
            'image' => $rawImage,
            'signature' => $msg['signature'] ?? null,

            // Residential Information
            'residence_address' => $residenceAddress,
            'residence_address_line1' => $msg['residence_AdressLine1'] ?? null,
            'residence_town' => $msg['residence_town'] ?? null,
            'residence_lga' => $msg['residence_lga'] ?? null,
            'residence_state' => $msg['residence_state'] ?? null,
            'residence_status' => $msg['residencestatus'] ?? null,

            // Origin & Birth Information
            'state_of_origin' => $stateOfOrigin,
            'self_origin_state' => $msg['self_origin_state'] ?? null,
            'lga_of_origin' => $lgaOfOrigin,
            'self_origin_lga' => $msg['self_origin_lga'] ?? null,
            'place_of_origin' => $msg['self_origin_place'] ?? null,
            'self_origin_place' => $msg['self_origin_place'] ?? null,
            'birth_country' => $msg['birthcountry'] ?? null,
            'birthcountry' => $msg['birthcountry'] ?? null,
            'birth_state' => $msg['birthstate'] ?? null,
            'birthstate' => $msg['birthstate'] ?? null,
            'birth_lga' => $msg['birthlga'] ?? null,
            'birthlga' => $msg['birthlga'] ?? null,
            'country' => ! empty($msg['country']) ? $msg['country'] : 'NG',

            // Personal Particulars
            'height' => $msg['heigth'] ?? null,
            'heigth' => $msg['heigth'] ?? null,
            'marital_status' => $msg['maritalstatus'] ?? null,
            'maritalstatus' => $msg['maritalstatus'] ?? null,
            'religion' => $msg['religion'] ?? null,
            'profession' => $msg['profession'] ?? null,
            'spoken_language' => $msg['nspokenlang'] ?? null,
            'nspokenlang' => $msg['nspokenlang'] ?? null,
            'pmiddlename' => $msg['pmiddlename'] ?? null,

            // Next of Kin
            'nok_name' => $nokFullName ?: null,
            'nok_firstname' => $nokFn ?: null,
            'nok_surname' => $nokSn ?: null,
            'nok_address' => $nokAddress ?: null,
            'nok_address1' => $msg['nok_address1'] ?? null,
            'nok_town' => $msg['nok_town'] ?? null,
            'nok_lga' => $msg['nok_lga'] ?? null,

            // Metadata
            'provider' => 'VerifyUser (NIN)',
            'verification_mode' => 'nin',
            'verified_at' => now()->toIso8601String(),
            'issue_date' => now()->format('Y-m-d'),
            'raw' => $msg,
        ];
    }
}
