<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'notifications' => [
                'pending_count' => ($request->user() && in_array($request->user()->role_id, ['super_admin', 'verifier'])) 
                    ? \App\Models\AlumniProfile::where('verification_status', 'pending')->count() 
                    : 0,
                'new_residents_count' => ($request->user() && in_array($request->user()->role_id, ['super_admin', 'verifier']))
                    ? \App\Models\AlumniProfile::where('verification_status', 'unverified')->count()
                    : 0,
                'latest_entries' => ($request->user() && in_array($request->user()->role_id, ['super_admin', 'verifier']))
                    ? \App\Models\AlumniProfile::orderBy('created_at', 'desc')->take(5)->get()
                    : [],
                'expiry_alerts' => ($request->user() && $request->user()->role_id === 'alumni')
                    ? \App\Models\Certificate::whereHas('alumniProfile', function($q) use ($request) {
                        $q->where('user_id', $request->user()->id);
                    })
                    ->where('expiry_date', '<=', now()->addDays(7))
                    ->orderBy('expiry_date', 'asc')
                    ->get()
                    : [],
                'system_announcements' => [
                    [
                        'title' => 'Rayterton Protocol Update',
                        'message' => 'New institutional verification standards are now active.',
                        'date' => now()->format('Y-m-d')
                    ]
                ]
            ],
        ];
    }
}
