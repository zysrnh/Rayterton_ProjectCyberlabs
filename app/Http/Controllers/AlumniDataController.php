<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;

class AlumniDataController extends Controller
{
    public function storeEducation(Request $request)
    {
        $profile = $request->user()->alumniProfile;
        
        $request->validate([
            'institution_name' => 'required|string|max:255',
            'degree_program' => 'required|string|max:255',
            'graduation_year' => 'required|integer|min:1950|max:2099',
            'diploma_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:2048',
        ]);

        $path = null;
        if ($request->hasFile('diploma_file')) {
            $path = $request->file('diploma_file')->store('diplomas', 'public');
        }

        $profile->educations()->create([
            'institution_name' => $request->institution_name,
            'degree_program' => $request->degree_program,
            'graduation_year' => $request->graduation_year,
            'diploma_file_url' => $path,
            'is_verified' => false,
        ]);

        $this->updateProfileCompleteness($profile);

        return redirect()->back();
    }

    public function storeCertificate(Request $request)
    {
        $profile = $request->user()->alumniProfile;
        
        $request->validate([
            'cert_type' => 'required|string|in:BST,COP,COC,OTHER',
            'cert_name' => 'required|string|max:255',
            'cert_number' => 'required|string|max:255|unique:certificates,cert_number',
            'issuing_body' => 'required|string|max:255',
            'issued_date' => 'required|date',
            'expiry_date' => 'nullable|date|after:issued_date',
            'cert_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:2048',
        ]);

        $path = null;
        if ($request->hasFile('cert_file')) {
            $path = $request->file('cert_file')->store('certificates', 'public');
        }

        $profile->certificates()->create([
            'cert_type' => $request->cert_type,
            'cert_name' => $request->cert_name,
            'cert_number' => $request->cert_number,
            'issuing_body' => $request->issuing_body,
            'issued_date' => $request->issued_date,
            'expiry_date' => $request->expiry_date,
            'cert_file_url' => $path,
        ]);

        $this->updateProfileCompleteness($profile);

        return redirect()->back();
    }

    public function storeSeaService(Request $request)
    {
        $profile = $request->user()->alumniProfile;
        
        $request->validate([
            'vessel_name' => 'required|string|max:255',
            'vessel_type' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'rank_at_time' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'contract_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:2048',
        ]);

        $path = null;
        if ($request->hasFile('contract_file')) {
            $path = $request->file('contract_file')->store('sea_services', 'public');
        }

        // Calculate duration in months approximately
        $durationMonths = 0;
        if ($request->end_date) {
            $start = Carbon::parse($request->start_date);
            $end = Carbon::parse($request->end_date);
            $durationMonths = $start->diffInMonths($end);
        }

        $profile->seaServices()->create([
            'vessel_name' => $request->vessel_name,
            'vessel_type' => $request->vessel_type,
            'company_name' => $request->company_name,
            'rank_at_time' => $request->rank_at_time,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'duration_months' => $durationMonths,
            'route' => $request->route ?? '-', // Default if empty
            'contract_file_url' => $path,
        ]);

        $this->updateProfileCompleteness($profile);

        return redirect()->back();
    }

    public function updateEducation(Request $request, $id)
    {
        $profile = $request->user()->alumniProfile;
        $edu = $profile->educations()->findOrFail($id);
        
        $request->validate([
            'institution_name' => 'required|string|max:255',
            'degree_program' => 'required|string|max:255',
            'graduation_year' => 'required|integer|min:1950|max:2099',
            'diploma_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:2048',
        ]);

        $updateData = [
            'institution_name' => $request->institution_name,
            'degree_program' => $request->degree_program,
            'graduation_year' => $request->graduation_year,
            'is_verified' => false,
        ];

        if ($request->hasFile('diploma_file')) {
            $updateData['diploma_file_url'] = $request->file('diploma_file')->store('diplomas', 'public');
        }

        $edu->update($updateData);
        $this->updateProfileCompleteness($profile);

        return redirect()->back();
    }

    public function destroyEducation(Request $request, $id)
    {
        $profile = $request->user()->alumniProfile;
        $edu = $profile->educations()->findOrFail($id);
        $edu->delete();
        $this->updateProfileCompleteness($profile);
        return redirect()->back();
    }

    public function updateCertificate(Request $request, $id)
    {
        $profile = $request->user()->alumniProfile;
        $cert = $profile->certificates()->findOrFail($id);

        $isExpired = $cert->expiry_date && Carbon::parse($cert->expiry_date)->isPast();

        // Lock if verified AND NOT expired
        if ($cert->verification_status !== 'unverified' && $cert->verification_status !== null && !$isExpired) {
            return redirect()->back()->with('error', 'Verified records cannot be modified unless expired.');
        }
        
        $request->validate([
            'cert_type' => 'required|string|in:BST,COP,COC,OTHER',
            'cert_name' => 'required|string|max:255',
            'cert_number' => 'required|string|max:255|unique:certificates,cert_number,'.$id,
            'issuing_body' => 'required|string|max:255',
            'issued_date' => 'required|date',
            'expiry_date' => 'nullable|date|after:issued_date',
            'cert_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:2048',
        ]);

        $updateData = [
            'cert_type' => $request->cert_type,
            'cert_name' => $request->cert_name,
            'cert_number' => $request->cert_number,
            'issuing_body' => $request->issuing_body,
            'issued_date' => $request->issued_date,
            'expiry_date' => $request->expiry_date,
            'verification_status' => 'unverified'
        ];

        if ($request->hasFile('cert_file')) {
            $updateData['cert_file_url'] = $request->file('cert_file')->store('certificates', 'public');
        }

        $cert->update($updateData);
        $this->updateProfileCompleteness($profile);

        return redirect()->back();
    }

    public function destroyCertificate(Request $request, $id)
    {
        $profile = $request->user()->alumniProfile;
        $cert = $profile->certificates()->findOrFail($id);
        
        $isExpired = $cert->expiry_date && Carbon::parse($cert->expiry_date)->isPast();

        if ($cert->verification_status !== 'unverified' && $cert->verification_status !== null && !$isExpired) {
            return redirect()->back()->with('error', 'Verified records cannot be deleted unless expired.');
        }

        $cert->delete();
        $this->updateProfileCompleteness($profile);
        return redirect()->back();
    }

    public function updateSeaService(Request $request, $id)
    {
        $profile = $request->user()->alumniProfile;
        $sea = $profile->seaServices()->findOrFail($id);
        
        $request->validate([
            'vessel_name' => 'required|string|max:255',
            'vessel_type' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'rank_at_time' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'contract_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:2048',
        ]);

        $durationMonths = 0;
        if ($request->end_date) {
            $start = Carbon::parse($request->start_date);
            $end = Carbon::parse($request->end_date);
            $durationMonths = $start->diffInMonths($end);
        }

        $updateData = [
            'vessel_name' => $request->vessel_name,
            'vessel_type' => $request->vessel_type,
            'company_name' => $request->company_name,
            'rank_at_time' => $request->rank_at_time,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'duration_months' => $durationMonths,
            'route' => $request->route ?? $sea->route,
        ];

        if ($request->hasFile('contract_file')) {
            $updateData['contract_file_url'] = $request->file('contract_file')->store('sea_services', 'public');
        }

        $sea->update($updateData);
        $this->updateProfileCompleteness($profile);

        return redirect()->back();
    }

    public function destroySeaService(Request $request, $id)
    {
        $profile = $request->user()->alumniProfile;
        $sea = $profile->seaServices()->findOrFail($id);
        $sea->delete();
        $this->updateProfileCompleteness($profile);
        return redirect()->back();
    }

    private function updateProfileCompleteness($profile)
    {
        // Simple logic for MVP: 20 base + 20 per component 
        $score = 20; // Master Profile filled

        if ($profile->educations()->exists()) $score += 20;
        if ($profile->certificates()->exists()) $score += 30;
        if ($profile->seaServices()->exists()) $score += 30;

        $profile->update([
            'profile_completeness' => min($score, 100)
        ]);
    }
}
