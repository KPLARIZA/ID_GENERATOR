@php
    $userName = auth()->user()?->name ?? 'User';
    $employeeIdModel = \App\Models\EmployeeId::class;

    $totalIds = $employeeIdModel::query()->count();
    $updatedIds = $employeeIdModel::query()->whereColumn('updated_at', '!=', 'created_at')->count();
    $printedIds = $employeeIdModel::query()
        ->where('print_status', $employeeIdModel::PRINT_STATUS_DONE_PRINTING)
        ->count();
    $cancelledGrowth = -2.3;

    $totalGrowth = 12.4;
    $updatedGrowth = 8.1;
    $printedGrowth = 15.7;

    $monthlyStatusBreakdown = $this->getMonthlyPrintStatusBreakdown(12);
    $topOffices = $this->getTopOffices();

    $months = collect($monthlyStatusBreakdown)->pluck('label')->values()->all();
    $printedSeries = collect($monthlyStatusBreakdown)->pluck('printed')->map(fn ($value) => (int) $value)->values()->all();
    $inProgressSeries = collect($monthlyStatusBreakdown)->pluck('in_progress')->map(fn ($value) => (int) $value)->values()->all();
    $cancelledSeries = collect($monthlyStatusBreakdown)->pluck('cancelled')->map(fn ($value) => (int) $value)->values()->all();

    $inProgress = $employeeIdModel::query()
        ->where('print_status', $employeeIdModel::PRINT_STATUS_IN_PROGRESS)
        ->count();
    $cancelled = $employeeIdModel::query()
        ->where('print_status', $employeeIdModel::PRINT_STATUS_CANCELLED)
        ->count();
    $donutTotal = $printedIds + $inProgress + $cancelled;

    $barLabels = array_slice($months, -6);
    $barPrinted = array_slice($printedSeries, -6);
    $barInProgress = array_slice($inProgressSeries, -6);
    $barCancelled = array_slice($cancelledSeries, -6);

    $historyActivities = $this->getPrintStatusHistoryActivities(10);
@endphp

<x-filament-panels::page>
    <style>
        .eid-dashboard {
            min-height: 100vh;
            border-radius: 18px;
            background: #0b1220;
            padding: 24px;
            color: #f8fafc;
            font-family: Inter, Segoe UI, Roboto, sans-serif;
        }
        .eid-welcome-title { font-size: 34px; font-weight: 700; margin: 0; }
        .eid-welcome-subtitle { margin-top: 8px; color: #94a3b8; font-size: 15px; }
        .eid-card-grid {
            margin-top: 28px;
            display: grid;
            gap: 14px;
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
        .eid-card {
            background: #111827;
            border: 1px solid #1f2937;
            border-radius: 16px;
            padding: 18px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.28);
            transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
        }
        .eid-card:hover { transform: translateY(-2px); border-color: #334155; box-shadow: 0 16px 35px rgba(0, 0, 0, 0.35); }
        .eid-card-row { display: flex; justify-content: space-between; gap: 12px; align-items: flex-start; }
        .eid-card-title { color: #cbd5e1; font-size: 13px; margin-bottom: 10px; }
        .eid-card-value { font-size: 34px; font-weight: 700; line-height: 1; }
        .eid-card-growth { margin-top: 10px; font-size: 12px; }
        .eid-icon {
            width: 34px; height: 34px; border-radius: 10px;
            background: rgba(148, 163, 184, 0.12);
            display: grid; place-items: center; color: #e2e8f0;
            border: 1px solid rgba(148, 163, 184, 0.2);
        }
        .eid-accent-blue { box-shadow: inset 0 2px 0 #3b82f6; }
        .eid-accent-green { box-shadow: inset 0 2px 0 #10b981; }
        .eid-accent-purple { box-shadow: inset 0 2px 0 #8b5cf6; }
        .eid-accent-red { box-shadow: inset 0 2px 0 #ef4444; }

        .eid-analytics-grid {
            margin-top: 20px;
            display: grid;
            gap: 14px;
            grid-template-columns: 7fr 3fr;
        }
        .eid-panel {
            background: #111827;
            border: 1px solid #1f2937;
            border-radius: 16px;
            padding: 18px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.22);
        }
        .eid-panel-title { font-size: 18px; font-weight: 600; margin: 0; }
        .eid-filter {
            display: inline-flex;
            border: 1px solid #334155;
            border-radius: 12px;
            background: #0f172a;
            padding: 4px;
        }
        .eid-filter button {
            border: 0;
            background: transparent;
            color: #cbd5e1;
            padding: 7px 12px;
            border-radius: 8px;
            font-size: 12px;
            cursor: pointer;
        }
        .eid-filter button.active { background: rgba(56, 189, 248, 0.2); color: #67e8f9; }
        .eid-line-wrap { height: 330px; margin-top: 12px; }
        .eid-right-grid { display: grid; gap: 14px; }
        .eid-donut-wrap { position: relative; width: 220px; height: 220px; margin: 6px auto 0; }
        .eid-donut-center {
            position: absolute; inset: 0; display: grid; place-items: center;
            pointer-events: none;
        }
        .eid-donut-center small { display: block; text-align: center; color: #94a3b8; font-size: 12px; }
        .eid-donut-center strong { display: block; text-align: center; font-size: 30px; margin-top: 2px; }
        .eid-bar-wrap { height: 220px; margin-top: 8px; }

        .eid-timeline-panel { margin-top: 20px; }
        .eid-timeline-title-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
        .eid-timeline-title { font-size: 18px; font-weight: 600; margin: 0; }
        .eid-timeline-search {
            width: 320px;
            max-width: 100%;
            border: 1px solid #334155;
            border-radius: 10px;
            background: #0f172a;
            color: #e2e8f0;
            font-size: 13px;
            padding: 9px 12px;
            outline: none;
        }
        .eid-timeline-search:focus {
            border-color: #38bdf8;
            box-shadow: 0 0 0 2px rgba(56, 189, 248, 0.2);
        }
        .eid-timeline-scroll { max-height: 420px; overflow: auto; padding-right: 4px; }
        .eid-timeline-grid { display: grid; grid-template-columns: 1fr; gap: 10px; }
        .eid-event {
            border: 1px solid #1f2937;
            background: #0f172a;
            border-radius: 12px;
            padding: 12px 13px;
        }
        .eid-event-row { display: flex; align-items: flex-start; gap: 10px; }
        .eid-dot {
            width: 12px; height: 12px; border-radius: 999px; margin-top: 6px; flex-shrink: 0;
            box-shadow: 0 0 0 5px rgba(148, 163, 184, 0.14);
        }
        .eid-event-head { display: flex; justify-content: space-between; gap: 10px; }
        .eid-event-title { font-weight: 600; font-size: 14px; }
        .eid-event-time { color: #94a3b8; font-size: 11px; white-space: nowrap; }
        .eid-event-desc { margin-top: 4px; color: #cbd5e1; font-size: 13px; line-height: 1.5; }
        .eid-history-no-results {
            margin-top: 12px;
            border: 1px dashed #334155;
            border-radius: 10px;
            color: #94a3b8;
            text-align: center;
            font-size: 13px;
            padding: 12px;
            display: none;
        }

        .eid-top-panel { margin-top: 20px; }
        .eid-top-title { font-size: 16px; font-weight: 600; margin: 0 0 10px; }
        .eid-top-list { display: grid; gap: 9px; }
        .eid-top-row {
            border: 1px solid #1f2937;
            background: #0f172a;
            border-radius: 10px;
            display: flex;
            justify-content: space-between;
            gap: 10px;
            padding: 12px;
            color: #cbd5e1;
            font-size: 13px;
        }
        .eid-top-count { color: #f8fafc; font-weight: 600; }

        @media (max-width: 1280px) {
            .eid-card-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .eid-analytics-grid { grid-template-columns: 1fr; }
            .eid-right-grid { grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 768px) {
            .eid-dashboard { padding: 14px; border-radius: 12px; }
            .eid-welcome-title { font-size: 25px; }
            .eid-card-grid { grid-template-columns: 1fr; }
            .eid-right-grid { grid-template-columns: 1fr; }
            .eid-donut-wrap { width: 190px; height: 190px; }
            .eid-timeline-title-row { align-items: flex-start; flex-direction: column; gap: 10px; }
            .eid-timeline-search { width: 100%; }
        }
    </style>

    <div class="eid-dashboard">
        <section>
            <h1 class="eid-welcome-title">Welcome, {{ $userName }} 👋</h1>
            <p class="eid-welcome-subtitle">Here's what's happening with your Employee ID system today.</p>
        </section>

        @php
            $cards = [
                ['label' => 'Total IDs Generated', 'value' => $totalIds, 'growth' => $totalGrowth, 'accent' => 'eid-accent-blue', 'icon' => 'heroicon-o-identification'],
                ['label' => 'IDs In Progress', 'value' => $inProgress, 'growth' => $updatedGrowth, 'accent' => 'eid-accent-green', 'icon' => 'heroicon-o-clock'],
                ['label' => 'Printed IDs', 'value' => $printedIds, 'growth' => $printedGrowth, 'accent' => 'eid-accent-purple', 'icon' => 'heroicon-o-printer'],
                ['label' => 'Cancelled IDs', 'value' => $cancelled, 'growth' => $cancelledGrowth, 'accent' => 'eid-accent-red', 'icon' => 'heroicon-o-x-circle'],
            ];
        @endphp

        <section class="eid-card-grid">
            @foreach ($cards as $card)
                <article class="eid-card {{ $card['accent'] }}">
                    <div class="eid-card-row">
                        <div>
                            <div class="eid-card-title">{{ $card['label'] }}</div>
                            <div class="eid-card-value">{{ number_format($card['value']) }}</div>
                            <div class="eid-card-growth" style="color: {{ $card['growth'] >= 0 ? '#4ade80' : '#f87171' }};">
                                {{ $card['growth'] >= 0 ? '+' : '' }}{{ $card['growth'] }}% <span style="color:#94a3b8;">vs last 30 days</span>
                            </div>
                        </div>
                        <div class="eid-icon">
                            <x-filament::icon :icon="$card['icon']" class="h-4 w-4" />
                        </div>
                    </div>
                </article>
            @endforeach
        </section>

        <section class="eid-analytics-grid">
            <div class="eid-panel">
                <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;">
                    <h2 class="eid-panel-title">Overview</h2>
                    <div class="eid-filter">
                        <button class="chart-range-btn" data-range="day">Day</button>
                        <button class="chart-range-btn" data-range="week">Week</button>
                        <button class="chart-range-btn active" data-range="month">Month</button>
                    </div>
                </div>
                <div class="eid-line-wrap">
                    <canvas id="overviewLineChart"></canvas>
                </div>
            </div>

            <div class="eid-right-grid">
                <div class="eid-panel">
                    <h3 class="eid-panel-title" style="font-size:16px;">User Report</h3>
                    <div class="eid-donut-wrap">
                        <canvas id="userReportDonut"></canvas>
                        <div class="eid-donut-center">
                            <div>
                                <small>Total</small>
                                <strong>{{ number_format($donutTotal) }}</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="eid-panel">
                    <h3 class="eid-panel-title" style="font-size:16px;">Activity</h3>
                    <div class="eid-bar-wrap">
                        <canvas id="activityBarChart"></canvas>
                    </div>
                </div>
            </div>
        </section>

        <section class="eid-panel eid-timeline-panel">
            <div class="eid-timeline-title-row">
                <h2 class="eid-timeline-title">Print Status History</h2>
                <input
                    id="eidHistorySearch"
                    class="eid-timeline-search"
                    type="search"
                    placeholder="Search history by ID, status, user, or date/time..."
                />
            </div>

            <div class="eid-timeline-scroll">
                <div class="eid-timeline-grid">
                    @forelse ($historyActivities as $item)
                        @php
                            $searchText = mb_strtolower($item['title'] . ' ' . $item['description'] . ' ' . $item['time']);
                        @endphp
                        <article class="eid-event eid-history-item" data-history-text="{{ $searchText }}">
                            <div class="eid-event-row">
                                <span class="eid-dot" style="background:#38bdf8;"></span>
                                <div style="flex:1;min-width:0;">
                                    <div class="eid-event-head">
                                        <span class="eid-event-title">{{ $item['title'] }}</span>
                                        <time class="eid-event-time">{{ $item['time'] }}</time>
                                    </div>
                                    <p class="eid-event-desc">{{ $item['description'] }}</p>
                                </div>
                            </div>
                        </article>
                    @empty
                        <article class="eid-event" id="eidHistoryEmptyState">
                            <p class="eid-event-desc" style="margin-top:0;">No print status updates yet.</p>
                        </article>
                    @endforelse
                </div>
                <div id="eidHistoryNoResults" class="eid-history-no-results">
                    No print status history matched your search.
                </div>
            </div>
        </section>

        <section class="eid-panel eid-top-panel">
            <h2 class="eid-top-title">Top Offices</h2>
            <div class="eid-top-list">
                @forelse ($topOffices as $office)
                    <div class="eid-top-row">
                        <span>{{ $office['office'] }}</span>
                        <span class="eid-top-count">{{ number_format($office['total']) }}</span>
                    </div>
                @empty
                    <p style="margin:0;color:#94a3b8;font-size:13px;">No generated IDs yet.</p>
                @endforelse
            </div>
        </section>
    </div>

    @once
        @push('scripts')
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script>
                (() => {
                    const labels = @json($months);
                    const printedSeries = @json($printedSeries);
                    const inProgressSeries = @json($inProgressSeries);
                    const cancelledSeries = @json($cancelledSeries);

                    const barLabels = @json($barLabels);
                    const barPrinted = @json($barPrinted);
                    const barInProgress = @json($barInProgress);
                    const barCancelled = @json($barCancelled);

                    const donutData = @json([$printedIds, $inProgress, $cancelled]);

                    const axisColor = '#7b8aa1';
                    const gridColor = 'rgba(100, 116, 139, 0.14)';

                    const commonOptions = {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                labels: { color: '#cbd5e1' },
                            },
                            tooltip: {
                                backgroundColor: '#0f172a',
                                titleColor: '#e2e8f0',
                                bodyColor: '#e2e8f0',
                                borderColor: 'rgba(148, 163, 184, .25)',
                                borderWidth: 1,
                            },
                        },
                        scales: {
                            x: { ticks: { color: axisColor }, grid: { color: gridColor } },
                            y: { ticks: { color: axisColor }, grid: { color: gridColor }, beginAtZero: true },
                        },
                    };

                    const lineCanvas = document.getElementById('overviewLineChart');
                    if (lineCanvas) {
                        const overviewChart = new Chart(lineCanvas, {
                            type: 'line',
                            data: {
                                labels,
                                datasets: [
                                    {
                                        label: 'Printed',
                                        data: printedSeries,
                                        borderColor: '#8b5cf6',
                                        backgroundColor: 'rgba(139, 92, 246, .16)',
                                        tension: 0.42,
                                        fill: false,
                                        pointRadius: 3,
                                        pointHoverRadius: 5,
                                    },
                                    {
                                        label: 'In Progress',
                                        data: inProgressSeries,
                                        borderColor: '#22d3ee',
                                        backgroundColor: 'rgba(34, 211, 238, .16)',
                                        tension: 0.42,
                                        fill: false,
                                        pointRadius: 3,
                                        pointHoverRadius: 5,
                                    },
                                    {
                                        label: 'Cancelled',
                                        data: cancelledSeries,
                                        borderColor: '#ef4444',
                                        backgroundColor: 'rgba(239, 68, 68, .16)',
                                        tension: 0.42,
                                        fill: false,
                                        pointRadius: 3,
                                        pointHoverRadius: 5,
                                    },
                                ],
                            },
                            options: commonOptions,
                        });

                        document.querySelectorAll('.chart-range-btn').forEach((button) => {
                            button.addEventListener('click', () => {
                                document.querySelectorAll('.chart-range-btn').forEach((candidate) => {
                                    candidate.classList.remove('active');
                                });

                                button.classList.add('active');

                                const range = button.dataset.range;
                                let slice = 12;
                                if (range === 'week') slice = 8;
                                if (range === 'day') slice = 6;

                                overviewChart.data.labels = labels.slice(-slice);
                                overviewChart.data.datasets[0].data = printedSeries.slice(-slice);
                                overviewChart.data.datasets[1].data = inProgressSeries.slice(-slice);
                                overviewChart.data.datasets[2].data = cancelledSeries.slice(-slice);
                                overviewChart.update();
                            });
                        });
                    }

                    const donutCanvas = document.getElementById('userReportDonut');
                    if (donutCanvas) {
                        new Chart(donutCanvas, {
                            type: 'doughnut',
                            data: {
                                labels: ['Printed', 'In Progress', 'Cancelled'],
                                datasets: [
                                    {
                                        data: donutData,
                                        backgroundColor: ['#8b5cf6', '#22d3ee', '#ef4444'],
                                        borderWidth: 0,
                                        hoverOffset: 6,
                                    },
                                ],
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                cutout: '72%',
                                plugins: {
                                    legend: {
                                        position: 'bottom',
                                        labels: { color: '#cbd5e1' },
                                    },
                                    tooltip: {
                                        backgroundColor: '#0f172a',
                                        titleColor: '#e2e8f0',
                                        bodyColor: '#e2e8f0',
                                    },
                                },
                            },
                        });
                    }

                    const barCanvas = document.getElementById('activityBarChart');
                    if (barCanvas) {
                        new Chart(barCanvas, {
                            type: 'bar',
                            data: {
                                labels: barLabels,
                                datasets: [
                                    {
                                        label: 'Printed',
                                        data: barPrinted,
                                        backgroundColor: 'rgba(139, 92, 246, 0.75)',
                                        borderRadius: 8,
                                    },
                                    {
                                        label: 'In Progress',
                                        data: barInProgress,
                                        backgroundColor: 'rgba(34, 211, 238, 0.75)',
                                        borderRadius: 8,
                                    },
                                    {
                                        label: 'Cancelled',
                                        data: barCancelled,
                                        backgroundColor: 'rgba(239, 68, 68, 0.75)',
                                        borderRadius: 8,
                                    },
                                ],
                            },
                            options: {
                                ...commonOptions,
                                scales: {
                                    x: { stacked: false, ticks: { color: axisColor }, grid: { display: false } },
                                    y: { beginAtZero: true, ticks: { color: axisColor }, grid: { color: gridColor } },
                                },
                            },
                        });
                    }

                    const historySearchInput = document.getElementById('eidHistorySearch');
                    if (historySearchInput) {
                        const historyItems = Array.from(document.querySelectorAll('.eid-history-item'));
                        const emptyStateCard = document.getElementById('eidHistoryEmptyState');
                        const noResultsBox = document.getElementById('eidHistoryNoResults');

                        historySearchInput.addEventListener('input', () => {
                            const query = historySearchInput.value.trim().toLowerCase();
                            let visibleCount = 0;

                            historyItems.forEach((item) => {
                                const text = item.getAttribute('data-history-text') || '';
                                const visible = query === '' || text.includes(query);

                                item.style.display = visible ? '' : 'none';
                                if (visible) {
                                    visibleCount++;
                                }
                            });

                            if (emptyStateCard) {
                                emptyStateCard.style.display = query === '' && historyItems.length === 0 ? '' : 'none';
                            }

                            if (noResultsBox) {
                                noResultsBox.style.display = query !== '' && visibleCount === 0 ? 'block' : 'none';
                            }
                        });
                    }

                    const AUTO_REFRESH_SECONDS = 20;
                    if (AUTO_REFRESH_SECONDS > 0) {
                        setInterval(() => {
                            window.location.reload();
                        }, AUTO_REFRESH_SECONDS * 1000);
                    }
                })();
            </script>
        @endpush
    @endonce
</x-filament-panels::page>
