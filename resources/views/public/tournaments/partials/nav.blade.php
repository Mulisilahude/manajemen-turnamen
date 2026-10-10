@php
    $nav = [
        ['route' => 'public.tournaments.show', 'label' => 'Ringkasan'],
        ['route' => 'public.tournaments.teams', 'label' => 'Tim'],
        ['route' => 'public.tournaments.standings', 'label' => 'Klasemen'],
        ['route' => 'public.tournaments.schedule', 'label' => 'Jadwal'],
        ['route' => 'public.tournaments.results', 'label' => 'Hasil'],
        ['route' => 'public.tournaments.statistics', 'label' => 'Statistik'],
        ['route' => 'public.tournaments.bracket', 'label' => 'Bagan'],
    ];
@endphp
<div class="sticky top-16 z-30 border-b border-line surface/90 backdrop-blur">
    <div class="app-container">
        <nav class="flex gap-1 overflow-x-auto py-2.5">
            @foreach($nav as $item)
                @php $aktif = request()->routeIs($item['route']); @endphp
                <a href="{{ route($item['route'], $tournament) }}" class="tab {{ $aktif ? 'tab-active' : '' }}">
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>
    </div>
</div>
