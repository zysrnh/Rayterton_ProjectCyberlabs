<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AlumniProfile;
use Inertia\Inertia;
use Illuminate\Support\Str;

class AlumniProfileController extends Controller
{
    public function updateMasterProfile(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'rank' => 'required|string',
            'region' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'preferred_vessel_type' => 'required|string|max:255',
            'preferred_route' => 'required|string|max:255',
        ]);

        $user = $request->user();
        if ($user->role_id !== 'alumni') {
            abort(403);
        }

        $profile = $user->alumniProfile;

        // If profile doesn't exist (because the automatic generation failed on first registration attempt), create one
        if (!$profile) {
            $profile = $user->alumniProfile()->create([
                'profile_completeness' => 0,
            ]);
            $profile->refresh();
        }

        // Auto-generate alumni_code if not presents
        if (!$profile->alumni_code) {
            $year = date('y');
            $random = rand(10000, 99999);
            $profile->alumni_code = "AL-{$year}-{$random}";
        }

        // Updating basic fields
        $profile->full_name = $request->full_name;
        $profile->rank = $request->rank;
        $profile->region = $request->region;
        $profile->phone = $request->phone;
        $profile->preferred_vessel_type = $request->preferred_vessel_type;
        $profile->preferred_route = $request->preferred_route;
        
        // Initial setup for the readiness score & completion
        // Roughly estimation for now: if all master profile fields filled, gives 20%
        $profile->profile_completeness = 20;

        $profile->save();

        return redirect()->back()->with('success', 'Master profile successfully updated.');
    }

    public function submitForVerification(Request $request)
    {
        $profile = $request->user()->alumniProfile;

        if (!$profile) {
            abort(404);
        }

        if ($profile->profile_completeness < 100) {
            return redirect()->back()->withErrors(['verification' => 'Data profil Anda belum 100% lengkap. Silakan lengkapi Pendidikan, Sertifikat, dan Sea Service.']);
        }

        $profile->update([
            'verification_status' => 'pending'
        ]);

        return redirect()->back()->with('success', 'Profil Anda telah diajukan untuk verifikasi.');
    }
}
