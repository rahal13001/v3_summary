<div class="space-y-4">
    @forelse ($revisions as $revision)
        <section class="rounded-lg border border-gray-200 p-4 dark:border-white/10">
            <p class="text-sm font-semibold text-gray-950 dark:text-white">
                {{ $revision->editor?->name ?? 'Pengguna tidak tersedia' }}
            </p>
            <p class="mb-3 text-xs text-gray-500">
                {{ $revision->changed_at?->format('d-m-Y H:i:s') }}
            </p>

            <dl class="space-y-3">
                @foreach ($revision->changes as $field => $change)
                    <div>
                        <dt class="text-sm font-medium text-gray-700 dark:text-gray-200">
                            {{ str($field)->replace('_', ' ')->title() }}
                        </dt>
                        <dd class="whitespace-pre-wrap break-words text-sm text-gray-600 dark:text-gray-300">
                            <span class="line-through">{{ is_array($change['old'] ?? null) ? implode("\n", $change['old']) : ($change['old'] ?? 'Kosong') }}</span>
                            <span aria-hidden="true"> → </span>
                            <span>{{ is_array($change['new'] ?? null) ? implode("\n", $change['new']) : ($change['new'] ?? 'Kosong') }}</span>
                        </dd>
                    </div>
                @endforeach
            </dl>
        </section>
    @empty
        <p class="text-sm text-gray-500">Belum ada riwayat perubahan.</p>
    @endforelse
</div>
