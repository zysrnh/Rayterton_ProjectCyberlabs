<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\AlumniProfile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    public function index()
    {
        return view('auth.register');
    }

    public function store(Request $request)
    {
        // 1. Validasi Sederhana
        $request->validate([
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'full_name' => 'required',
        ]);

        // 2. Create User
        $user = User::create([
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => 'alumni',
            'is_active' => true,
        ]);

        // 3. Create Profile (generate alumni_id otomatis)
        $year = date('Y');
        $random = rand(10000, 99999);
        $alumniId = "AL-{$year}-{$random}";

        $user->alumniProfile()->create([
            'alumni_id' => $alumniId,
            'full_name' => $request->full_name,
            'rank' => $request->rank,
            'phone' => $request->phone,
            'sea_time_total' => $request->sea_time_total,
            'region' => $request->region,
            'preferred_vessel' => $request->preferred_vessel,
            'preferred_route' => $request->preferred_route,
            'availability_status' => 'Available',
            'profile_completeness' => 20, // Initial stage
            'open_to_offers' => true,
        ]);

        return back()->with([
            'success' => "Data Berhasil Disimpan ke Database! (Alumni ID: $alumniId)",
            'user_id' => $user->id
        ]);
    }
}
