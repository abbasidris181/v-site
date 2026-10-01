<?php

namespace App\Services\Providers\Drivers;

use App\Services\Providers\Contracts\VerificationProviderInterface;

abstract class BaseVerificationDriver implements VerificationProviderInterface
{
    /**
     * Generate an authentic base64 SVG avatar photo for the Nigerian verification slip.
     */
    protected function generateSamplePhotoBase64(string $gender = 'Male'): string
    {
        $coatColor = $gender === 'Male' ? '#1e293b' : '#334155';
        $shirtColor = '#f8fafc';
        $skinColor = '#8d5524'; // Warm brown tone

        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 240" width="100%" height="100%">
  <rect width="200" height="240" fill="#e2e8f0"/>
  <!-- Backdrop subtle watermark -->
  <circle cx="100" cy="120" r="85" fill="#cbd5e1" opacity="0.3"/>
  <!-- Torso / Suit -->
  <path d="M 30,240 L 45,160 L 80,165 L 100,195 L 120,165 L 155,160 L 170,240 Z" fill="{$coatColor}"/>
  <!-- Shirt & Tie -->
  <polygon points="80,165 100,195 120,165 110,150 90,150" fill="{$shirtColor}"/>
  <polygon points="96,170 104,170 106,215 100,225 94,215" fill="#047857"/>
  <!-- Neck -->
  <rect x="86" y="125" width="28" height="35" rx="5" fill="{$skinColor}"/>
  <!-- Head -->
  <ellipse cx="100" cy="100" rx="42" ry="50" fill="{$skinColor}"/>
  <!-- Hair -->
  <path d="M 58,90 C 58,60 80,45 100,45 C 120,45 142,60 142,90 C 130,55 70,55 58,90 Z" fill="#0f172a"/>
  <!-- Ears -->
  <ellipse cx="58" cy="100" rx="6" ry="12" fill="{$skinColor}"/>
  <ellipse cx="142" cy="100" rx="6" ry="12" fill="{$skinColor}"/>
  <!-- Eyes -->
  <ellipse cx="85" cy="98" rx="4" ry="3" fill="#1e293b"/>
  <ellipse cx="115" cy="98" rx="4" ry="3" fill="#1e293b"/>
  <!-- Eyebrows -->
  <path d="M 78,90 Q 85,86 92,90" stroke="#0f172a" stroke-width="2.5" fill="none"/>
  <path d="M 108,90 Q 115,86 122,90" stroke="#0f172a" stroke-width="2.5" fill="none"/>
  <!-- Nose -->
  <path d="M 100,98 L 97,112 L 103,112 Z" fill="#6f3b14"/>
  <!-- Mouth -->
  <path d="M 88,125 Q 100,130 112,125" stroke="#3b1f0b" stroke-width="3" fill="none"/>
</svg>
SVG;

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    /**
     * Generate authentic Nigerian demographic details.
     *
     * @return array<string, mixed>
     */
    protected function generateSimulatedDemographics(string $identifier, string $type = 'nin', array $overrides = []): array
    {
        $firstNames = ['Amina', 'Chinedu', 'Babatunde', 'Fatima', 'Emeka', 'Oluwaseun', 'Ibrahim', 'Ngozi', 'Zainab', 'Tariq'];
        $surnames = ['Danjuma', 'Okonkwo', 'Adeyemi', 'Shehu', 'Eze', 'Balogun', 'Musa', 'Nwosu', 'Bello', 'Al-Hassan'];
        $middleNames = ['Kalu', 'Chioma', 'Adewale', 'Farida', 'Chukwu', 'Folake', 'Garba', 'Amara', 'Usman', 'Boma'];
        
        $states = [
            'Kano' => ['Nassarawa', 'Dala', 'Fagge', 'Gwale'],
            'Lagos' => ['Ikeja', 'Surulere', 'Alimosho', 'Eti-Osa'],
            'Enugu' => ['Enugu North', 'Nkanu West', 'Nsukka', 'Udi'],
            'Rivers' => ['Port Harcourt', 'Obio-Akpor', 'Eleme', 'Ikwerre'],
            'Oyo' => ['Ibadan North', 'Oyo East', 'Ogbomoso', 'Ibarapa'],
            'FCT' => ['Abuja Municipal', 'Bwari', 'Gwagwalada', 'Kuje'],
        ];

        // Pick deterministic index based on identifier digits
        $seed = abs(crc32($identifier));
        $fn = !empty($overrides['first_name']) ? $overrides['first_name'] : $firstNames[$seed % count($firstNames)];
        $sn = !empty($overrides['surname']) 
            ? $overrides['surname'] 
            : (!empty($overrides['last_name']) 
                ? $overrides['last_name'] 
                : (!empty($overrides['lastname']) ? $overrides['lastname'] : $surnames[($seed + 3) % count($surnames)]));
        if (array_key_exists('middle_name', $overrides) && $overrides['middle_name'] !== null) {
            $rawOvMn = is_string($overrides['middle_name']) ? trim($overrides['middle_name']) : '';
            $isMaskedOvMn = empty($rawOvMn) || preg_match('/^[\*\s—\-]+$/', $rawOvMn) || in_array(strtolower($rawOvMn), ['null', 'nil', 'none', 'n/a', 'na'], true);
            $mn = ! $isMaskedOvMn ? $rawOvMn : null;
        } else {
            $mn = $middleNames[($seed + 7) % count($middleNames)];
        }

        $stateKeys = array_keys($states);
        $state = $stateKeys[($seed + 2) % count($stateKeys)];
        $lgas = $states[$state];
        $lga = $lgas[$seed % count($lgas)];

        $gender = ($seed % 2 === 0) ? 'Male' : 'Female';
        if (!empty($overrides['gender'])) {
            $gender = ucfirst(strtolower($overrides['gender']));
        }
        
        if (!empty($overrides['dob'])) {
            $dob = $overrides['dob'];
        } else {
            $birthYear = 1975 + ($seed % 28);
            $birthMonth = str_pad(($seed % 12) + 1, 2, '0', STR_PAD_LEFT);
            $birthDay = str_pad(($seed % 27) + 1, 2, '0', STR_PAD_LEFT);
            $dob = "{$birthYear}-{$birthMonth}-{$birthDay}";
        }

        $phone = !empty($overrides['normalized_phone']) 
            ? $overrides['normalized_phone'] 
            : (!empty($overrides['phone_number']) ? $overrides['phone_number'] : ('080' . substr(str_pad((string)($seed * 7), 8, '3', STR_PAD_LEFT), 0, 8)));
        
        $trackingId = strtoupper(substr(md5($identifier . 'tracking'), 0, 15));
        
        $mode = $overrides['mode'] ?? 'nin';
        if (preg_match('/^[0-9]{11}$/', $identifier) && $mode === 'nin') {
            $nin = $identifier;
        } else {
            $nin = '1' . substr(str_pad((string)$seed, 10, '0', STR_PAD_LEFT), 0, 10);
        }
        $ninFormatted = substr($nin, 0, 4) . ' ' . substr($nin, 4, 3) . ' ' . substr($nin, 7, 4);

        $fullName = trim("{$fn} " . ($mn ? "{$mn} " : '') . "{$sn}");

        return [
            'first_name' => $fn,
            'surname' => $sn,
            'middle_name' => $mn,
            'full_name' => $fullName,
            'gender' => $gender,
            'date_of_birth' => $dob,
            'phone_number' => $phone,
            'state_of_origin' => $state,
            'lga_of_origin' => $lga,
            'residence_address' => "No. " . (($seed % 90) + 1) . ", Verification Crescent, {$lga}, {$state} State",
            'tracking_id' => $trackingId,
            'nin' => $nin,
            'nin_formatted' => $ninFormatted,
            'photo_base64' => $this->generateSamplePhotoBase64($gender),
            'issue_date' => now()->subMonths(($seed % 24) + 1)->format('Y-m-d'),
            'verification_stamp' => 'VERIFIED_OFFICIAL_' . strtoupper(substr(md5($identifier), 0, 8)),
        ];
    }
}
