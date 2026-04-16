<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\AlumniProfile;
use App\Models\Education;
use App\Models\Certificate;
use App\Models\SeaService;

class FileControlController extends Controller
{
    public function access($path)
    {
        $user = Auth::user();
        if (!$user) {
            abort(403, 'Unauthorized Access Protocol');
        }

        // 1. If user is Admin/Verifier, they have clearance for all vault assets
        if (in_array($user->role_id, ['super_admin', 'verifier'])) {
            return $this->serveFile($path);
        }

        // 2. If user is Alumni, they only have clearance for their own assets
        $profile = $user->alumniProfile;
        if (!$profile) {
            abort(403, 'No Registry Profile Found');
        }

        // Check if the file is their avatar
        if ($profile->avatar_url === $path) {
            return $this->serveFile($path);
        }

        // Check if the file belongs to their education records
        $isEduOwner = Education::where('alumni_profile_id', $profile->id)
            ->where('diploma_file_url', $path)
            ->exists();
        
        if ($isEduOwner) {
            return $this->serveFile($path);
        }

        // Check if the file belongs to their certificates vault
        $isCertOwner = Certificate::where('alumni_profile_id', $profile->id)
            ->where('cert_file_url', $path)
            ->exists();

        if ($isCertOwner) {
            return $this->serveFile($path);
        }

        // Check if the file belongs to their sea service history
        $isSeaOwner = SeaService::where('alumni_profile_id', $profile->id)
            ->where('contract_file_url', $path)
            ->exists();

        if ($isSeaOwner) {
            return $this->serveFile($path);
        }

        // If no clearance matches, block access
        abort(403, 'Clearance Denied: You do not own this asset.');
    }

    private function serveFile($path)
    {
        // Try looking in the private app storage first
        if (Storage::disk('local')->exists($path)) {
            return response()->file(storage_path('app/' . $path));
        }

        // Fallback to public storage if it hasn't been moved yet
        if (Storage::disk('public')->exists($path)) {
            return response()->file(storage_path('app/public/' . $path));
        }

        abort(404, 'Asset not located in Registry Vault');
    }
}
