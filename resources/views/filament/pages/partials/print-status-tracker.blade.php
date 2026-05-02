<div style="display: grid; gap: 1rem;">
    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr>
                <th style="border: 1px solid #374151; padding: .5rem; text-align: left;">Status</th>
                <th style="border: 1px solid #374151; padding: .5rem; text-align: right;">Total IDs</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="border: 1px solid #374151; padding: .5rem;">In Progress</td>
                <td style="border: 1px solid #374151; padding: .5rem; text-align: right;">{{ number_format($statusSummary['in_progress'] ?? 0) }}</td>
            </tr>
            <tr>
                <td style="border: 1px solid #374151; padding: .5rem;">Done Printing</td>
                <td style="border: 1px solid #374151; padding: .5rem; text-align: right;">{{ number_format($statusSummary['done_printing'] ?? 0) }}</td>
            </tr>
            <tr>
                <td style="border: 1px solid #374151; padding: .5rem;">Cancelled</td>
                <td style="border: 1px solid #374151; padding: .5rem; text-align: right;">{{ number_format($statusSummary['cancelled'] ?? 0) }}</td>
            </tr>
        </tbody>
    </table>

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
            @forelse ($trackerRows as $row)
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
</div>
