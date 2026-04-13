<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class AdminController extends Controller
{
    public function dashboard(Request $request)
    {
        if (!in_array($request->user()->role_id, ['super_admin', 'verifier'])) {
            abort(403);
        }

        $pendingCount = \App\Models\AlumniProfile::where('verification_status', 'pending')->count();
        $verifiedCount = \App\Models\AlumniProfile::where('verification_status', 'verified')->count();
        $companyCount = User::where('role_id', 'company')->count();

        return Inertia::render('Admin/Dashboard', [
            'role' => $request->user()->role_id,
            'stats' => [
                'pending' => $pendingCount,
                'verified' => $verifiedCount,
                'companies' => $companyCount
            ]
        ]);
    }

    public function queue(Request $request)
    {
        if (!in_array($request->user()->role_id, ['super_admin', 'verifier'])) {
            abort(403);
        }

        $pendingProfiles = \App\Models\AlumniProfile::where('verification_status', 'pending')
            ->with(['educations', 'certificates', 'seaServices'])
            ->orderBy('updated_at', 'asc')
            ->get();

        return Inertia::render('Admin/VerificationQueue', [
            'queue' => $pendingProfiles
        ]);
    }

    public function approve(Request $request, $id)
    {
        if (!in_array($request->user()->role_id, ['super_admin', 'verifier'])) {
            abort(403);
        }

        $profile = \App\Models\AlumniProfile::findOrFail($id);
        $profile->update([
            'verification_status' => 'verified',
            'verified_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Profile Approved!');
    }

    public function index(Request $request)
    {
        if ($request->user()->role_id !== 'super_admin') {
            abort(403, 'Unauthorized actions.');
        }

        $admins = User::whereIn('role_id', ['super_admin', 'verifier'])->get();

        return Inertia::render('Admin/ManageUsers', [
            'admins' => $admins
        ]);
    }

    public function store(Request $request)
    {
        if ($request->user()->role_id !== 'super_admin') {
            abort(403);
        }

        $request->validate([
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'role_id' => 'required|in:super_admin,verifier'
        ]);

        User::create([
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
            'is_active' => true,
        ]);

        return redirect()->back();
    }
}
