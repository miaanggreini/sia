@extends('layouts.siswa')

@section('content')
    <div class="mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Nilai Akademik</h1>
            <p class="text-sm text-gray-500 mt-1">
                Rekap nilai akhir mata pelajaran per semester.
            </p>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    {{-- Grafik perkembangan --}}
    @if(!empty($chartLabels) && count($chartLabels) > 0)
        <div class="bg-white rounded-xl shadow border mb-6">
            <div class="px-5 py-4 border-b">
                <h2 class="font-semibold text-gray-800">Grafik Perkembangan Nilai</h2>
                <p class="text-xs text-gray-500 mt-1">
                    Perbandingan rata-rata nilai tiap semester per tahun ajaran.
                </p>
            </div>
            <div class="p-5">
                <canvas id="nilaiChart" height="110"></canvas>
            </div>
        </div>
    @endif

    {{-- Rekap nilai accordion --}}
    <div class="space-y-4">
        @forelse($groups as $idx => $group)
            @php
                $accordionId = 'accordion-nilai-' . $idx;
                $collapseId = 'collapse-nilai-' . $idx;
            @endphp

            <div class="bg-white rounded-xl shadow border overflow-hidden">
                <button type="button"
                        class="w-full px-5 py-4 flex items-center justify-between text-left hover:bg-gray-50 transition"
                        onclick="toggleAccordion('{{ $collapseId }}', '{{ $accordionId }}')"
                        id="{{ $accordionId }}">
                    <div>
                        <h2 class="font-semibold text-gray-900">
                            Tahun Ajaran {{ $group->ta }}
                        </h2>
                        <p class="text-sm text-gray-500 mt-1">
                            Tingkat {{ $group->tingkat }} • Semester {{ $group->semester }}
                        </p>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="text-sm text-right">
                            <div class="text-gray-500">Rata-rata Semester</div>
                            <div class="font-semibold text-indigo-700">
                                {{ $group->avg !== null ? number_format($group->avg, 2) : '-' }}
                            </div>
                        </div>
                        <svg class="w-5 h-5 text-gray-500 transition-transform duration-200"
                             data-icon
                             xmlns="http://www.w3.org/2000/svg"
                             fill="none"
                             viewBox="0 0 24 24"
                             stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </button>

                <div id="{{ $collapseId }}" class="{{ $idx === 0 ? '' : 'hidden' }}">
                    <div class="px-5 py-3 border-t border-b bg-gray-50 flex items-center justify-between">
                        <div class="text-sm text-gray-600">
                            Detail nilai semester {{ $group->semester }} tahun ajaran {{ $group->ta }}
                        </div>

                        @if (Route::has('siswa.nilai.download-pdf'))
                            <a href="{{ route('siswa.nilai.download-pdf', [
                                    'tahun_ajaran' => $group->ta,
                                    'semester' => $group->semester,
                                    'tingkat' => $group->tingkat,
                                ]) }}"
                               target="_blank"
                               class="px-4 py-2 rounded-lg bg-emerald-600 text-white text-sm font-medium hover:bg-emerald-700">
                                Cetak PDF Semester Ini
                            </a>
                        @elseif (Route::has('nilai.download-pdf'))
                            <a href="{{ route('nilai.download-pdf', [
                                    'tahun_ajaran' => $group->ta,
                                    'semester' => $group->semester,
                                    'tingkat' => $group->tingkat,
                                ]) }}"
                               target="_blank"
                               class="px-4 py-2 rounded-lg bg-emerald-600 text-white text-sm font-medium hover:bg-emerald-700">
                                Cetak PDF Semester Ini
                            </a>
                        @endif
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-100 text-gray-700">
                                <tr>
                                    <th class="px-4 py-3 text-left w-16">No</th>
                                    <th class="px-4 py-3 text-left">Mata Pelajaran</th>
                                    <th class="px-4 py-3 text-left">Guru</th>
                                    <th class="px-4 py-3 text-left w-36">Nilai Akhir</th>
                                    <th class="px-4 py-3 text-left w-40">Predikat</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @forelse($group->rows as $i => $row)
                                    @php
                                        $nilaiAkhir = $row->nilai_akhir ?? $row->rata ?? null;
                                        $namaMapel = $row->mapel ?? $row->nama_mapel ?? $row->mata_pelajaran ?? '-';
                                        $namaGuru = $row->guru ?? '-';
                                    @endphp
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3">{{ $i + 1 }}</td>
                                        <td class="px-4 py-3 font-medium text-gray-900">{{ $namaMapel }}</td>
                                        <td class="px-4 py-3 text-gray-700">{{ $namaGuru }}</td>
                                        <td class="px-4 py-3 font-semibold text-indigo-700">
                                            {{ $nilaiAkhir !== null ? number_format($nilaiAkhir, 2) : '-' }}
                                        </td>
                                        <td class="px-4 py-3">
                                            @if(($row->status ?? null) === 'tuntas')
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-50 text-green-700 border border-green-200">
                                                    Tuntas
                                                </span>
                                            @elseif(($row->status ?? null) === 'tidak_tuntas')
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-50 text-red-700 border border-red-200">
                                                    Tidak Tuntas
                                                </span>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                            Tidak ada data nilai pada periode ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-xl shadow border">
                <div class="px-5 py-8 text-center text-gray-500">
                    Tidak ada data nilai yang tersedia.
                </div>
            </div>
        @endforelse
    </div>

    @if(!empty($chartLabels) && count($chartLabels) > 0)
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            const chartLabels = @json($chartLabels);
            const chartValues = @json($chartValues);

            const ctx = document.getElementById('nilaiChart');
            if (ctx) {
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: chartLabels,
                        datasets: [{
                            label: 'Rata-rata Nilai',
                            data: chartValues,
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                display: true
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                suggestedMax: 100
                            }
                        }
                    }
                });
            }

            function toggleAccordion(collapseId, buttonId) {
                const target = document.getElementById(collapseId);
                const button = document.getElementById(buttonId);
                const icon = button.querySelector('[data-icon]');

                if (target.classList.contains('hidden')) {
                    target.classList.remove('hidden');
                    icon.classList.add('rotate-180');
                } else {
                    target.classList.add('hidden');
                    icon.classList.remove('rotate-180');
                }
            }

            document.addEventListener('DOMContentLoaded', function () {
                const firstOpen = document.querySelector('[id^="collapse-nilai-"]:not(.hidden)');
                if (firstOpen) {
                    const btnId = firstOpen.id.replace('collapse', 'accordion');
                    const btn = document.getElementById(btnId);
                    const icon = btn?.querySelector('[data-icon]');
                    if (icon) icon.classList.add('rotate-180');
                }
            });
        </script>
    @endif
@endsection