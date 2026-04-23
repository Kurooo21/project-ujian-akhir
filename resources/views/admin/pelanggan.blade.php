@extends('admin.layouts.app')
@section('page_title', 'Data Pelanggan')

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between p-5 lg:px-6 border-b border-slate-200 gap-4">
        <div>
            <h2 class="text-base font-bold text-slate-900 tracking-tight">Daftar Pelanggan Terdaftar</h2>
            <span class="text-xs text-slate-400">Database user yang mendaftar di Chi-Pok</span>
        </div>
        <div class="flex gap-2 w-full sm:w-auto">
            <button class="flex-1 sm:flex-none flex items-center justify-center gap-1.5 px-3.5 py-2 border border-slate-200 rounded-md text-[13px] font-medium text-slate-600 bg-white hover:border-blue-600 hover:text-blue-600 transition-colors">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5">
                    <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
                    <polyline points="7 10 12 15 17 10"/>
                    <line x1="12" y1="15" x2="12" y2="3"/>
                </svg>
                Export CSV
            </button>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse whitespace-nowrap lg:whitespace-normal">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-semibold text-slate-400 uppercase tracking-widest text-left">
                    <th class="py-3 px-6">No</th>
                    <th class="py-3 px-6">Identitas Pelanggan</th>
                    <th class="py-3 px-6">Email</th>
                    <th class="py-3 px-6">No Telp</th>
                    <th class="py-3 px-6">Tanggal Bergabung</th>
                    <th class="py-3 px-6 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-[13px] text-slate-600 divide-y divide-slate-100">
                @forelse($pelanggan as $index => $user)
                <tr class="hover:bg-blue-50/50 transition-colors">
                    <td class="py-3.5 px-6">{{ $index + 1 }}</td>
                    <td class="py-3.5 px-6">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-xs shrink-0">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <div class="font-semibold text-slate-900">{{ $user->name }}</div>
                        </div>
                    </td>
                    <td class="py-3.5 px-6">{{ $user->email }}</td>
                    <td class="py-3.5 px-6">{{ $user->no_hp ?? '-' }}</td>
                    <td class="py-3.5 px-6">{{ $user->created_at->format('d M Y') }}</td>
                    <td class="py-3.5 px-6 text-right">
                        <button class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-semibold text-slate-600 border border-slate-200 bg-white hover:bg-slate-50 transition-colors" onclick="alert('Riwayat pesanan pelanggan ini (Feature Coming Soon)')">
                            Lihat Riwayat
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-10 text-slate-400">Belum ada pelanggan terdaftar.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
