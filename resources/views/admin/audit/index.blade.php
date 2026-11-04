@extends('layouts.app')

@section('title', 'Jejak Audit Domain')
@section('page_title', 'Jejak Audit & Log Perubahan Sistem')

@section('content')
<div style="display:flex; flex-direction:column; gap:24px;">

    <!-- Filter Bar -->
    <div class="card" style="margin-bottom:0;">
        <div class="card-body" style="display:flex; align-items:center; justify-content:space-between; gap:16px;">
            <form action="{{ route('admin.audit.index') }}" method="GET" style="display:flex; align-items:center; gap:10px;">
                <input type="text" name="action" class="form-control" placeholder="Cari nama aksi (mis: permit, rule, scan)..." value="{{ $action }}" style="min-width:280px;">
                <button type="submit" class="btn btn-outline btn-sm">Cari</button>
                @if($action)
                    <a href="{{ route('admin.audit.index') }}" class="btn btn-outline btn-sm">Reset</a>
                @endif
            </form>
            <span class="badge badge-teal">Append-only Record</span>
        </div>
    </div>

    <!-- Audit Logs Table -->
    <div class="card">
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Waktu (WIB)</th>
                        <th>Aktor</th>
                        <th>Aksi</th>
                        <th>Entitas Terkait</th>
                        <th>Alasan / Keterangan</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td>
                                <strong>{{ $log->created_at->format('d/m/Y H:i:s') }}</strong>
                            </td>
                            <td>
                                {{ $log->actor->name ?? 'System' }}<br>
                                <small style="color:var(--text-muted);">{{ $log->actor->email ?? '-' }}</small>
                            </td>
                            <td>
                                <span class="badge badge-teal" style="font-family:monospace;">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td>
                                {{ class_basename($log->auditable_type ?? '-') }} #{{ $log->auditable_id ?? '-' }}
                            </td>
                            <td style="max-width:300px;">
                                <div style="font-size:13px;">{{ $log->reason ?? '-' }}</div>
                            </td>
                            <td><small style="color:var(--text-muted);">{{ $log->ip_address ?? '-' }}</small></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align:center; padding:32px; color:var(--text-muted);">
                                Belum ada catatan jejak audit.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
            <div style="padding:16px;">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
