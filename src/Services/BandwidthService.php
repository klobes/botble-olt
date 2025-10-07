<?php

namespace Botble\FiberHomeOLTManager\Services;

use Botble\FiberHomeOLTManager\Models\BandwidthProfile;
use Botble\FiberHomeOLTManager\Models\ONU;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class BandwidthService
{
    /**
     * Get all bandwidth profiles
     */
    public function getAllProfiles(): array
    {
        return BandwidthProfile::with(['onus'])
            ->orderBy('name')
            ->get()
            ->toArray();
    }

    /**
     * Get profile by ID
     */
    public function getProfileById(int $id): ?BandwidthProfile
    {
        return BandwidthProfile::with(['onus'])
            ->find($id);
    }

    /**
     * Create new bandwidth profile
     */
    public function createProfile(array $data): BandwidthProfile
    {
        return BandwidthProfile::create($data);
    }

    /**
     * Update bandwidth profile
     */
    public function updateProfile(int $id, array $data): ?BandwidthProfile
    {
        $profile = BandwidthProfile::find($id);
        
        if ($profile) {
            $profile->update($data);
            $this->clearCache($profile->id);
        }
        
        return $profile;
    }

    /**
     * Delete bandwidth profile
     */
    public function deleteProfile(int $id): bool
    {
        $profile = BandwidthProfile::find($id);
        
        if ($profile) {
            // Check if profile is in use
            if ($profile->onus()->count() > 0) {
                return false;
            }
            
            $this->clearCache($id);
            return $profile->delete();
        }
        
        return false;
    }

    /**
     * Assign profile to ONU
     */
    public function assignToONU(int $profileId, int $onuId): bool
    {
        try {
            $onu = ONU::find($onuId);
            $profile = BandwidthProfile::find($profileId);
            
            if (!$onu || !$profile) {
                return false;
            }

            $onu->bandwidth_profile_id = $profileId;
            $result = $onu->save();
            
            if ($result) {
                $this->applyBandwidthConfiguration($onu, $profile);
            }
            
            return $result;
            
        } catch (\Exception $e) {
            Log::error("Failed to assign bandwidth profile: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Remove profile from ONU
     */
    public function removeFromONU(int $onuId): bool
    {
        try {
            $onu = ONU::find($onuId);
            
            if (!$onu) {
                return false;
            }

            $onu->bandwidth_profile_id = null;
            return $onu->save();
            
        } catch (\Exception $e) {
            Log::error("Failed to remove bandwidth profile: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get bandwidth statistics
     */
    public function getStatistics(): array
    {
        return [
            'total_profiles' => BandwidthProfile::count(),
            'active_profiles' => BandwidthProfile::where('status', true)->count(),
            'inactive_profiles' => BandwidthProfile::where('status', false)->count(),
            'profiles_in_use' => BandwidthProfile::has('onus')->count(),
            'total_onus_with_profile' => ONU::whereNotNull('bandwidth_profile_id')->count(),
            'total_onus_without_profile' => ONU::whereNull('bandwidth_profile_id')->count(),
            'by_priority' => BandwidthProfile::select('priority', \DB::raw('count(*) as count'))
                ->groupBy('priority')
                ->get()
                ->toArray(),
           // 'by_speed' => BandwidthProfile::select(
           //       \DB::raw('CASE 
			//			WHEN download_speed < 100 THEN \'Low Speed\'
			//			WHEN download_speed < 500 THEN \'Medium Speed\'
			//			WHEN download_speed < 1000 THEN \'High Speed\'
			//			ELSE \'Ultra High Speed\'
			//		END as speed_category'),
			//		\DB::raw('count(*) as count')
             //   )
             //   ->groupBy('speed_category')
             //   ->get()
              //  ->toArray(),
        ];
    }

    /**
     * Get usage by OLT
     */
    public function getUsageByOLT(): array
    {
        return ONU::select('olts.id', 'olts.name', \DB::raw('count(*) as onu_count'))
            ->join('olts', 'onus.olt_id', '=', 'olts.id')
            ->whereNotNull('onus.bandwidth_profile_id')
            ->groupBy('olts.id', 'olts.name')
            ->get()
            ->toArray();
    }

    /**
     * Validate bandwidth configuration
     */
    public function validateConfiguration(array $config): array
    {
        $errors = [];
        
        // Validate download speed
        if (!isset($config['download_speed']) || $config['download_speed'] < 1 || $config['download_speed'] > 10000) {
            $errors[] = 'Download speed must be between 1 and 10000 Mbps';
        }
        
        // Validate upload speed
        if (!isset($config['upload_speed']) || $config['upload_speed'] < 1 || $config['upload_speed'] > 10000) {
            $errors[] = 'Upload speed must be between 1 and 10000 Mbps';
        }
        
        // Validate speed ratio
        if (isset($config['download_speed'], $config['upload_speed'])) {
            $ratio = $config['download_speed'] / $config['upload_speed'];
            if ($ratio > 10) {
                $errors[] = 'Download to upload speed ratio cannot exceed 10:1';
            }
        }
        
        // Validate guaranteed speeds
        if (isset($config['download_guaranteed'])) {
            if ($config['download_guaranteed'] < 10 || $config['download_guaranteed'] > 100) {
                $errors[] = 'Download guaranteed percentage must be between 10 and 100';
            }
            if ($config['download_guaranteed'] > $config['download_speed']) {
                $errors[] = 'Download guaranteed speed cannot exceed total speed';
            }
        }
        
        if (isset($config['upload_guaranteed'])) {
            if ($config['upload_guaranteed'] < 10 || $config['upload_guaranteed'] > 100) {
                $errors[] = 'Upload guaranteed percentage must be between 10 and 100';
            }
            if ($config['upload_guaranteed'] > $config['upload_speed']) {
                $errors[] = 'Upload guaranteed speed cannot exceed total speed';
            }
        }
        
        // Validate priority
        $validPriorities = ['low', 'medium', 'high', 'premium'];
        if (isset($config['priority']) && !in_array($config['priority'], $validPriorities)) {
            $errors[] = 'Priority must be one of: low, medium, high, premium';
        }
        
        return $errors;
    }

    /**
     * Calculate bandwidth usage
     */
    public function calculateUsage(int $profileId): array
    {
        $profile = BandwidthProfile::find($profileId);
        
        if (!$profile) {
            return [];
        }
        
        $onus = ONU::where('bandwidth_profile_id', $profileId)->get();
        
        return [
            'profile' => $profile->toArray(),
            'total_onus' => $onus->count(),
            'total_download_capacity' => $profile->download_speed * $onus->count(),
            'total_upload_capacity' => $profile->upload_speed * $onus->count(),
            'onus' => $onus->toArray(),
        ];
    }

    /**
     * Get recommended profiles based on usage
     */
    public function getRecommendedProfiles(int $downloadSpeed, int $uploadSpeed): array
    {
        return BandwidthProfile::where('download_speed', '>=', $downloadSpeed)
            ->where('upload_speed', '>=', $uploadSpeed)
            ->orderBy('download_speed')
            ->orderBy('upload_speed')
            ->get()
            ->toArray();
    }

    /**
     * Apply bandwidth configuration to ONU
     */
    private function applyBandwidthConfiguration(ONU $onu, BandwidthProfile $profile): void
    {
        // Implementation to apply bandwidth configuration via SNMP
        // This would send configuration commands to the OLT
    }

    /**
     * Clear cache
     */
    private function clearCache(int $profileId): void
    {
        Cache::forget("bandwidth_profile_{$profileId}");
    }
}
