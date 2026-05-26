@php
   $users = \App\Models\User::query()
    ->withCount(['reports' => function($query){
        $query
        ->whereYear('when', date('Y'))
        ->whereMonth('when', date('m'));
    }])
    ->orderByDesc('reports_count')
    ->limit(5)
    ->get();
@endphp

<x-filament-widgets::widget>
    <x-filament::section>
        <span class="text-center font-bold block">Top Writer This Month !!!</span>
        <table class="mx-auto">
            <thead>
                <tr>
                    <th class="px-4 py-2 text-center font-bold">No</th>
                    <th class="px-4 py-2 text-left font-bold">Nama</th>
                    <th class="px-4 py-2 text-center font-bold">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr>
                        <td class="px-4 py-2 text-center">{{ $loop->iteration }}</td>
                        <td class="px-4 py-2 text-left">{{ $user->name }}</td>
                        <td class="px-4 py-2 text-center">{{ $user->reports_count }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-filament::section>
</x-filament-widgets::widget>
