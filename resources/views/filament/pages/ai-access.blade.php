<x-filament-panels::page>
    <style>
        .summary-ai-form { display: grid; gap: 1.25rem; }
        .summary-ai-field { display: grid; gap: .5rem; }
        .summary-ai-field > label, .summary-ai-form legend { font-weight: 600; }
        .summary-ai-form fieldset { border: 1px solid #cbd5e1; border-radius: .75rem; padding: 1rem; }
        .summary-ai-form legend { padding: 0 .35rem; }
        .summary-ai-columns { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .75rem 1.25rem; margin-top: 1rem; }
        .summary-ai-check { display: flex; align-items: flex-start; gap: .625rem; line-height: 1.5; }
        .summary-ai-check input { margin-top: .25rem; flex: 0 0 auto; }
        .summary-ai-help { color: #475569; line-height: 1.6; }
        .summary-ai-select, .summary-ai-token { box-sizing: border-box; width: 100%; border: 1px solid #94a3b8; border-radius: .5rem; padding: .65rem .75rem; background: white; color: #0f172a; font-size: 1rem; }
        .summary-ai-select:focus, .summary-ai-token:focus { outline: 2px solid #b45309; outline-offset: 2px; }
        .summary-ai-token { font-family: monospace; margin: .75rem 0; overflow-wrap: anywhere; }
        .summary-ai-table-wrap { overflow-x: auto; }
        .summary-ai-table { width: 100%; border-collapse: collapse; text-align: left; }
        .summary-ai-table th, .summary-ai-table td { padding: .85rem .75rem; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
        .summary-ai-table th { font-weight: 600; }
        .dark .summary-ai-form fieldset, .dark .summary-ai-table th, .dark .summary-ai-table td { border-color: #475569; }
        .dark .summary-ai-help { color: #cbd5e1; }
        .dark .summary-ai-select, .dark .summary-ai-token { background: #111827; color: #f8fafc; border-color: #64748b; }
        @media (max-width: 640px) { .summary-ai-columns { grid-template-columns: 1fr; } }
    </style>
    <x-filament::section heading="Koneksi AI readonly">
        <p>AI hanya membaca data yang Anda izinkan. Dokumen dibuat di laptop. Data yang dikirim ke model dapat diproses penyedia AI.</p>
        <p>Status API: <strong>{{ config('ai.enabled') ? 'Aktif' : 'Belum diaktifkan oleh pengelola' }}</strong>.</p>
    </x-filament::section>

    @if ($plainToken)
        <x-filament::section heading="Salin token sekarang">
            <p>Token asli hanya tersedia saat ini. Simpan pada environment konektor, jangan dalam prompt atau repository.</p>
            <label for="new-ai-token">Token akses Summary</label>
            <textarea id="new-ai-token" readonly rows="3" class="summary-ai-token" spellcheck="false">{{ $plainToken }}</textarea>
            <x-filament::button wire:click="dismissToken" color="gray">Tutup token</x-filament::button>
        </x-filament::section>
    @endif

    <x-filament::section heading="Buat token laptop">
        <form wire:submit="issue" class="summary-ai-form">
            <div class="summary-ai-field">
                <label for="ai-token-name">Nama token</label>
                <x-filament::input.wrapper><x-filament::input id="ai-token-name" wire:model="tokenName" required maxlength="100" placeholder="Laptop Pribadi" /></x-filament::input.wrapper>
            </div>
            <div class="summary-ai-field">
                <label for="ai-scope">Batas laporan</label>
                <select id="ai-scope" wire:model="reportScope" class="summary-ai-select">
                    <option value="own_related">Laporan sendiri atau yang saya ikuti</option>
                    <option value="account">Seluruh laporan sesuai izin akun</option>
                </select>
            </div>
            <div class="summary-ai-field">
                <label for="ai-days">Masa berlaku</label>
                <select id="ai-days" wire:model="days" class="summary-ai-select">
                    <option value="7">7 hari</option><option value="30">30 hari</option><option value="90">90 hari</option>
                </select>
            </div>
            <p class="summary-ai-help">Pilih kelompok data dan kolom yang diperlukan. Untuk membaca kegiatan dalam rentang waktu, sertakan tanggal mulai dan tanggal selesai. Sertakan ID IKU jika perlu memilih IKU tertentu.</p>
            @foreach ($this->availableDatasets() as $dataset => $columns)
                <fieldset>
                    <legend>{{ \App\Services\Ai\Registry::LABELS[$dataset] }}</legend>
                    <label class="summary-ai-check">
                        <x-filament::input.checkbox wire:model="datasets" value="{{ $dataset }}" />
                        Izinkan {{ \App\Services\Ai\Registry::LABELS[$dataset] }}
                    </label>
                    <div class="summary-ai-columns">
                        @foreach ($columns as $field => $definition)
                            <label class="summary-ai-check">
                                <x-filament::input.checkbox wire:model="fields.{{ $dataset }}" value="{{ $field }}" />
                                {{ $definition[1] }}
                            </label>
                        @endforeach
                    </div>
                </fieldset>
            @endforeach
            @if ($errors->any())
                <div role="alert" class="text-danger-600">{{ $errors->first() }}</div>
            @endif
            <x-filament::button type="submit" wire:loading.attr="disabled">Buat token readonly</x-filament::button>
        </form>
    </x-filament::section>

    <x-filament::section heading="Token AI saya">
        <div class="summary-ai-table-wrap">
            <table class="summary-ai-table">
                <thead><tr><th scope="col">Nama</th><th scope="col">Kedaluwarsa</th><th scope="col">Terakhir digunakan</th><th scope="col">Tindakan</th></tr></thead>
                <tbody>
                @forelse ($this->tokens() as $token)
                    <tr wire:key="ai-token-{{ $token->id }}">
                        <td class="p-2">{{ $token->name }}</td>
                        <td class="p-2">{{ $token->expires_at?->format('d-m-Y H:i') }}</td>
                        <td class="p-2">{{ $token->last_used_at?->format('d-m-Y H:i') ?? 'Belum digunakan' }}</td>
                        <td class="p-2"><x-filament::button color="danger" wire:click="revoke({{ $token->id }})" wire:confirm="Cabut token ini? Koneksi laptop menggunakan token ini akan berhenti.">Cabut {{ $token->name }}</x-filament::button></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="p-2">Belum ada token AI.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-panels::page>
