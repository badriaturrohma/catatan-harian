<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Campus;
use App\Models\Program;
use App\Models\QrCredential;
use App\Models\Region;
use App\Models\Room;
use App\Models\Santri;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SantriController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $campusId = $request->query('campus_id');
        $status = $request->query('status');

        $query = Santri::with(['campus', 'program', 'room', 'activeQrCredential']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('niup', 'like', "%{$search}%");
            });
        }

        if ($campusId) {
            $query->where('campus_id', $campusId);
        }

        if ($status !== null && $status !== '') {
            $query->where('is_active', $status === '1');
        }

        $santris = $query->orderBy('name')->paginate(15)->withQueryString();
        $campuses = Campus::where('is_active', true)->get();

        return view('admin.santri.index', compact('santris', 'campuses', 'search', 'campusId', 'status'));
    }

    public function create(): View
    {
        $campuses = Campus::where('is_active', true)->get();
        $programs = Program::where('is_active', true)->get();
        $regions = Region::where('is_active', true)->get();
        $rooms = Room::where('is_active', true)->get();

        return view('admin.santri.create', compact('campuses', 'programs', 'regions', 'rooms'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'niup' => ['required', 'string', 'unique:santris,niup', 'max:50'],
            'name' => ['required', 'string', 'max:150'],
            'gender' => ['required', 'in:L,P'],
            'campus_id' => ['nullable', 'exists:campuses,id'],
            'program_id' => ['nullable', 'exists:programs,id'],
            'room_id' => ['nullable', 'exists:rooms,id'],
        ]);

        $santri = Santri::create($validated);

        // Auto-generate QR Credential
        $credentialData = QrCredential::generateForSantri($santri);

        AuditLog::log(
            action: 'santri_created',
            auditable: $santri,
            newValues: $santri->toArray(),
            reason: 'Pengurus mendaftarkan santri baru'
        );

        return redirect()->route('admin.santri.index')
            ->with('success', "Santri {$santri->name} berhasil didaftarkan dan QR token aktif telah dibuat.")
            ->with('plain_token', $credentialData['token']);
    }

    public function edit(Santri $santri): View
    {
        $campuses = Campus::where('is_active', true)->get();
        $programs = Program::where('is_active', true)->get();
        $regions = Region::where('is_active', true)->get();
        $rooms = Room::where('is_active', true)->get();

        return view('admin.santri.edit', compact('santri', 'campuses', 'programs', 'regions', 'rooms'));
    }

    public function update(Request $request, Santri $santri): RedirectResponse
    {
        $validated = $request->validate([
            'niup' => ['required', 'string', "unique:santris,niup,{$santri->id}", 'max:50'],
            'name' => ['required', 'string', 'max:150'],
            'gender' => ['required', 'in:L,P'],
            'campus_id' => ['nullable', 'exists:campuses,id'],
            'program_id' => ['nullable', 'exists:programs,id'],
            'room_id' => ['nullable', 'exists:rooms,id'],
            'is_active' => ['required', 'boolean'],
        ]);

        $oldData = $santri->toArray();
        $santri->update($validated);

        AuditLog::log(
            action: 'santri_updated',
            auditable: $santri,
            oldValues: $oldData,
            newValues: $santri->toArray(),
            reason: 'Pengurus memperbarui data santri'
        );

        return redirect()->route('admin.santri.index')->with('success', 'Data santri berhasil diperbarui.');
    }

    /**
     * Regenerate QR Credential for a santri (rotates previous credential)
     */
    public function regenerateQr(Santri $santri): RedirectResponse
    {
        $credentialData = QrCredential::generateForSantri($santri);

        AuditLog::log(
            action: 'qr_regenerated',
            auditable: $santri,
            reason: 'Pengurus melakukan rotasi token QR'
        );

        return back()
            ->with('success', "Token QR baru berhasil dibuat untuk {$santri->name}.")
            ->with('plain_token', $credentialData['token']);
    }

    /**
     * Printable QR card view
     */
    public function printCard(Santri $santri): View
    {
        $santri->load(['campus', 'program', 'room', 'activeQrCredential']);

        // Plain token for QR preview
        $plainToken = session('plain_token') ?? ($santri->activeQrCredential ? 'SNT-'.$santri->activeQrCredential->token_hash : 'SNT-EMPTY');

        return view('admin.santri.card', compact('santri', 'plainToken'));
    }
}
