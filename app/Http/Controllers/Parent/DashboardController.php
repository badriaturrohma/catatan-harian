<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Journey;
use App\Models\Permit;
use App\Models\Santri;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        // Ambil daftar santri yang sudah terhubung dengan wali yang sedang login
        $santris = $user->santris()->with(['campus', 'program', 'room'])->get();
        $verifiedChildren = $santris;

        $selectedSantriId = $request->query('santri_id', $santris->first()?->id);
        $selectedSantri = $santris->firstWhere('id', $selectedSantriId);

        $activeJourney = null;
        $recentJourneys = collect();
        $permits = collect();

        if ($selectedSantri) {
            $activeJourney = Journey::with(['events.location', 'latestEvent', 'activeAlertEpisode'])
                ->where('santri_id', $selectedSantri->id)
                ->whereNotIn('status', ['selesai', 'rekonsiliasi'])
                ->latest('started_at')
                ->first();

            $recentJourneys = Journey::with(['events.location', 'permit'])
                ->where('santri_id', $selectedSantri->id)
                ->orderBy('started_at', 'desc')
                ->take(10)
                ->get();

            $permits = Permit::where('santri_id', $selectedSantri->id)
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();
        }

        return view('parent.dashboard', compact('santris', 'verifiedChildren', 'selectedSantri', 'activeJourney', 'recentJourneys', 'permits'));
    }

    /**
     * Memproses klaim/penghubungan santri menggunakan NIUP
     */
    public function claimSantri(Request $request): RedirectResponse
    {
        $request->validate([
            'niup' => ['required', 'string'],
            'relationship_type' => ['nullable', 'string', 'max:50'],
        ], [
            'niup.required' => 'Nomor NIUP santri wajib diisi.',
        ]);

        $niup = trim($request->input('niup'));
        $user = $request->user();

        // 1. Cek apakah NIUP santri ada di database
        $santri = Santri::where('niup', $niup)->first();

        if (! $santri) {
            return back()->with('error', "Santri dengan NIUP '{$niup}' tidak ditemukan dalam database.");
        }

        // 2. Cek apakah santri tersebut sudah terhubung dengan wali ini
        if ($user->santris()->where('santris.id', $santri->id)->exists()) {
            return back()->with('error', "Santri {$santri->name} (NIUP: {$santri->niup}) sudah terhubung dengan akun Anda.");
        }

        // 3. Jika valid, hubungkan santri dengan wali
        $relationshipType = $request->input('relationship_type') ?: 'Wali';

        $user->santris()->attach($santri->id, [
            'relationship_type' => $relationshipType,
            'is_verified' => true,
            'verified_at' => now(),
        ]);

        AuditLog::log(
            'guardian_claimed_santri',
            $santri,
            reason: "Wali {$user->name} berhasil menghubungkan santri {$santri->name} (NIUP: {$niup})"
        );

        return redirect()->route('parent.dashboard', ['santri_id' => $santri->id])
            ->with('success', "Berhasil menghubungkan santri {$santri->name} (NIUP: {$santri->niup}) ke akun Anda.");
    }
}
