@php
    $statusSummary = $this->getPrintStatusSummary();
    $recentPrintTracker = $this->getRecentPrintTracker();
@endphp

<x-filament-panels::page>
    <div style="display: grid; gap: 1rem;">
        <section>
            <h2 style="font-size: 1rem; font-weight: 700; margin-bottom: .5rem;">Printing status summary</h2>
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th style="border: 1px solid #374151; padding: .5rem; text-align: left;">Status</th>
                        <th style="border: 1px solid #374151; padding: .5rem; text-align: right;">Count</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="border: 1px solid #374151; padding: .5rem;">In Progress</td>
                        <td style="border: 1px solid #374151; padding: .5rem; text-align: right;">{{ number_format($statusSummary['in_progress']) }}</td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #374151; padding: .5rem;">Done Printing</td>
                        <td style="border: 1px solid #374151; padding: .5rem; text-align: right;">{{ number_format($statusSummary['done_printing']) }}</td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #374151; padding: .5rem;">Cancelled</td>
                        <td style="border: 1px solid #374151; padding: .5rem; text-align: right;">{{ number_format($statusSummary['cancelled']) }}</td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #374151; padding: .5rem; font-weight: 700;">Total</td>
                        <td style="border: 1px solid #374151; padding: .5rem; text-align: right; font-weight: 700;">{{ number_format($statusSummary['total']) }}</td>
                    </tr>
                </tbody>
            </table>
        </section>

        <section>
            <h2 style="font-size: 1rem; font-weight: 700; margin-bottom: .5rem;">Recent ID print tracker</h2>
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th style="border: 1px solid #374151; padding: .5rem; text-align: left;">ID Number</th>
                        <th style="border: 1px solid #374151; padding: .5rem; text-align: left;">Employee</th>
                        <th style="border: 1px solid #374151; padding: .5rem; text-align: left;">Office</th>
                        <th style="border: 1px solid #374151; padding: .5rem; text-align: left;">Status</th>
                        <th style="border: 1px solid #374151; padding: .5rem; text-align: left;">Last Updated</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentPrintTracker as $row)
                        <tr>
                            <td style="border: 1px solid #374151; padding: .5rem;">{{ $row['id_number'] }}</td>
                            <td style="border: 1px solid #374151; padding: .5rem;">{{ $row['full_name'] }}</td>
                            <td style="border: 1px solid #374151; padding: .5rem;">{{ $row['office_name'] }}</td>
                            <td style="border: 1px solid #374151; padding: .5rem;">
                                {{ \App\Models\EmployeeId::getPrintStatusLabel((string) $row['print_status']) }}
                            </td>
                            <td style="border: 1px solid #374151; padding: .5rem;">{{ $row['updated_at'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="border: 1px solid #374151; padding: .5rem;">No IDs found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>
    </div>
</x-filament-panels::page>
