@php
    $monthlyActivitySummary = $this->getMonthlyActivitySummary();
    $createdThisMonth = $this->getCreatedThisMonthCount();
    $monthlyCreatedIds = $this->getMonthlyCreatedIds();
    $historyTracker = $this->getHistoryTracker();
@endphp

<x-filament-panels::page>
    <style>
        .tracker-dashboard {
            display: grid;
            gap: 1rem;
            color: #e5e7eb;
        }

        .tracker-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 1rem;
        }

        .tracker-card {
            background: linear-gradient(180deg, rgba(17, 24, 39, .95) 0%, rgba(15, 23, 42, .95) 100%);
            border: 1px solid rgba(55, 65, 81, .75);
            border-radius: 1rem;
            padding: 1rem 1.1rem;
            box-shadow: 0 18px 40px rgba(2, 6, 23, .35);
        }

        .tracker-card__label {
            font-size: .75rem;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #9ca3af;
            margin: 0;
        }

        .tracker-card__value {
            margin: .45rem 0 0;
            font-size: 1.9rem;
            font-weight: 700;
            line-height: 1.1;
            color: #f8fafc;
        }

        .tracker-card--created { border-color: rgba(59, 130, 246, .4); }
        .tracker-card--updated { border-color: rgba(99, 102, 241, .4); }
        .tracker-card--deleted { border-color: rgba(239, 68, 68, .4); }
        .tracker-card--total { border-color: rgba(16, 185, 129, .4); }

        .tracker-table-card {
            background: rgba(17, 24, 39, .92);
            border: 1px solid rgba(55, 65, 81, .7);
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 18px 40px rgba(2, 6, 23, .28);
        }

        .tracker-table-card__head {
            padding: .9rem 1rem;
            border-bottom: 1px solid rgba(55, 65, 81, .7);
        }

        .tracker-table-card__title {
            margin: 0;
            color: #f8fafc;
            font-weight: 600;
            font-size: .95rem;
        }

        .tracker-table-wrap {
            overflow-x: auto;
        }

        .tracker-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 680px;
        }

        .tracker-table th,
        .tracker-table td {
            padding: .72rem .9rem;
            border-bottom: 1px solid rgba(55, 65, 81, .55);
            text-align: left;
            font-size: .86rem;
        }

        .tracker-table th {
            color: #cbd5e1;
            background: rgba(15, 23, 42, .72);
            font-weight: 600;
        }

        .tracker-table td {
            color: #e5e7eb;
            vertical-align: top;
        }

        .tracker-table tr:last-child td {
            border-bottom: none;
        }

        .text-right {
            text-align: right !important;
        }

        .event-badge {
            display: inline-flex;
            align-items: center;
            border-radius: 9999px;
            padding: .2rem .6rem;
            font-size: .72rem;
            font-weight: 600;
            border: 1px solid transparent;
        }

        .event-created {
            background: rgba(59, 130, 246, .18);
            color: #93c5fd;
            border-color: rgba(59, 130, 246, .35);
        }

        .event-updated {
            background: rgba(99, 102, 241, .18);
            color: #c7d2fe;
            border-color: rgba(99, 102, 241, .35);
        }

        .event-deleted {
            background: rgba(239, 68, 68, .2);
            color: #fecaca;
            border-color: rgba(239, 68, 68, .35);
        }

        .muted-row {
            color: #94a3b8;
        }

        @media (max-width: 1024px) {
            .tracker-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 640px) {
            .tracker-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="tracker-dashboard">
        <section class="tracker-grid">
            <article class="tracker-card tracker-card--created">
                <p class="tracker-card__label">IDs Created (This Month)</p>
                <p class="tracker-card__value">{{ number_format($monthlyActivitySummary['created']) }}</p>
            </article>
            <article class="tracker-card tracker-card--updated">
                <p class="tracker-card__label">IDs Updated (Info Changes)</p>
                <p class="tracker-card__value">{{ number_format($monthlyActivitySummary['updated']) }}</p>
            </article>
            <article class="tracker-card tracker-card--deleted">
                <p class="tracker-card__label">IDs Deleted (This Month)</p>
                <p class="tracker-card__value">{{ number_format($monthlyActivitySummary['deleted']) }}</p>
            </article>
            <article class="tracker-card tracker-card--total">
                <p class="tracker-card__label">Created Quick Total</p>
                <p class="tracker-card__value">{{ number_format($createdThisMonth) }}</p>
            </article>
        </section>

        <section class="tracker-table-card">
            <header class="tracker-table-card__head">
                <h2 class="tracker-table-card__title">Monthly Created IDs</h2>
            </header>
            <div class="tracker-table-wrap">
                <table class="tracker-table">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th class="text-right">Created IDs</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($monthlyCreatedIds as $row)
                            <tr>
                                <td>{{ $row['month'] }}</td>
                                <td class="text-right">{{ number_format($row['total']) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="muted-row">No monthly data found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="tracker-table-card">
            <header class="tracker-table-card__head">
                <h2 class="tracker-table-card__title">History Tracker (Information Updates)</h2>
            </header>
            <div class="tracker-table-wrap">
                <table class="tracker-table">
                    <thead>
                        <tr>
                            <th>ID Number</th>
                            <th>Employee</th>
                            <th>Event</th>
                            <th>Details</th>
                            <th>Changed By</th>
                            <th>Changed At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($historyTracker as $row)
                            <tr>
                                <td>{{ $row['id_number'] }}</td>
                                <td>{{ $row['employee_name'] }}</td>
                                <td>
                                    @php
                                        $eventClass = match ($row['event']) {
                                            'Created' => 'event-created',
                                            'Deleted' => 'event-deleted',
                                            default => 'event-updated',
                                        };
                                    @endphp
                                    <span class="event-badge {{ $eventClass }}">{{ $row['event'] }}</span>
                                </td>
                                <td>{{ $row['details'] }}</td>
                                <td>{{ $row['changed_by'] }}</td>
                                <td>{{ $row['changed_at'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="muted-row">No history found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-filament-panels::page>
