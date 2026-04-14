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

        $stats = \Illuminate\Support\Facades\Cache::remember('admin_dashboard_stats', 3600, function () {
            return [
                'pending' => \App\Models\AlumniProfile::where('verification_status', 'pending')->count(),
                'verified' => \App\Models\AlumniProfile::where('verification_status', 'verified')->count(),
                'companies' => User::where('role_id', 'company')->count()
            ];
        });

        $recentVerified = \Illuminate\Support\Facades\Cache::remember('recent_verified_alumni', 3600, function () {
            return \App\Models\AlumniProfile::where('verification_status', 'verified')
                ->orderBy('verified_at', 'desc')
                ->take(5)
                ->get();
        });

        return Inertia::render('Admin/Dashboard', [
            'role' => $request->user()->role_id,
            'stats' => $stats,
            'recentVerified' => $recentVerified
        ]);
    }

    public function queue(Request $request)
    {
        if (!in_array($request->user()->role_id, ['super_admin', 'verifier'])) {
            abort(403);
        }

        $allProfiles = \Illuminate\Support\Facades\Cache::remember('alumni_registry_queue', 3600, function () {
            return \App\Models\AlumniProfile::with(['educations', 'certificates', 'seaServices'])
                ->orderBy('updated_at', 'desc')
                ->get();
        });

        return Inertia::render('Admin/VerificationQueue', [
            'queue' => $allProfiles
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

        \Illuminate\Support\Facades\Cache::forget('admin_dashboard_stats');
        \Illuminate\Support\Facades\Cache::forget('recent_verified_alumni');
        \Illuminate\Support\Facades\Cache::forget('alumni_registry_queue');

        return redirect()->back()->with('success', 'Profile Approved!');
    }

    public function markAsReviewing(Request $request, $id)
    {
        if (!in_array($request->user()->role_id, ['super_admin', 'verifier'])) {
            abort(403);
        }
        
        $profile = \App\Models\AlumniProfile::findOrFail($id);
        if ($profile->verification_status === 'pending') {
            $profile->update([
                'verification_status' => 'in_review'
            ]);
            
            \Illuminate\Support\Facades\Cache::forget('admin_dashboard_stats');
            \Illuminate\Support\Facades\Cache::forget('alumni_registry_queue');
        }

        return redirect()->back();
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
    public function residents(Request $request)
    {
        if (!in_array($request->user()->role_id, ['super_admin', 'verifier'])) {
            abort(403);
        }

        $query = User::whereNotIn('role_id', ['super_admin', 'verifier'])
            ->with(['alumniProfile'])
            ->orderBy('created_at', 'desc');

        if ($request->search) {
            $query->where('email', 'like', '%' . $request->search . '%');
        }

        return Inertia::render('Admin/RegistryResidents', [
            'residents' => $query->get(),
            'filters' => $request->only(['search'])
        ]);
    }
}
