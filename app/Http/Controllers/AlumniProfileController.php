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
        $profile = $request->user()->alumniProfile;

        if (!$profile) {
            $profile = $request->user()->alumniProfile()->create([
                'profile_completeness' => 0,
            ]);
        }

        $request->validate([
            'full_name' => 'required|string|max:255',
            'rank' => 'required|string|max:255',
            'region' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'preferred_vessel_type' => 'nullable|string|max:255',
            'preferred_route' => 'nullable|string|max:255',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $profile->avatar_url = $path;
        }

        // Auto-generate alumni_code if not present
        if (!$profile->alumni_code) {
            $year = date('y');
            $random = rand(10000, 99999);
            $profile->alumni_code = "AL-{$year}-{$random}";
        }

        $profile->update([
            'full_name' => $request->full_name,
            'rank' => $request->rank,
            'region' => $request->region,
            'phone' => $request->phone,
            'preferred_vessel_type' => $request->preferred_vessel_type,
            'preferred_route' => $request->preferred_route,
            'profile_completeness' => max($profile->profile_completeness, 20)
        ]);

        return redirect()->back()->with('success', 'Master profile successfully updated.');
    }

    public function toggleAvailability()
    {
        $profile = auth()->user()->alumniProfile;
        
        $newStatus = $profile->availability_status === 'open_to_offers' ? 'unavailable' : 'open_to_offers';
        
        $profile->update([
            'availability_status' => $newStatus
        ]);

        return redirect()->back()->with('success', 'Status ketersediaan berhasil diperbarui!');
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
