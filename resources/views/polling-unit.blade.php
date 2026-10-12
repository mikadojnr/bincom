@extends('layouts.app')

@section('title', 'Polling Unit Result')

@section('content')
    <div class="card">
        <h2>Polling Unit Result</h2>
        <p class="muted">Pick a Local Government, then a Ward, then a Polling Unit to see the result announced there.</p>

        <form method="GET" action="{{ route('pu.result') }}">
            <div class="grid">
                <div>
                    <label for="lga">Local Government</label>
                    <select name="lga_id" id="lga">
                        <option value="">-- Select LGA --</option>
                        @foreach ($lgas as $item)
                            <option value="{{ $item->lga_id }}" @selected(optional($lga)->lga_id == $item->lga_id)>{{ $item->lga_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="ward">Ward</label>
                    <select name="ward_uniqueid" id="ward" @disabled($wards->isEmpty())>
                        <option value="">-- Select Ward --</option>
                        @foreach ($wards as $item)
                            <option value="{{ $item->uniqueid }}" @selected(optional($ward)->uniqueid == $item->uniqueid)>{{ $item->ward_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="unit">Polling Unit</label>
                    <select name="polling_unit_uniqueid" id="unit" @disabled($units->isEmpty())>
                        <option value="">-- Select Polling Unit --</option>
                        @foreach ($units as $item)
                            <option value="{{ $item->uniqueid }}" @selected(optional($unit)->uniqueid == $item->uniqueid)>{{ $item->polling_unit_name }} ({{ $item->polling_unit_number }})</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="actions"><button type="submit">Show Result</button></div>
        </form>
    </div>

    @if ($unit)
        <div class="card">
            <h2>{{ $unit->polling_unit_name }}</h2>
            <p class="muted">Polling unit number {{ $unit->polling_unit_number ?: '-' }} &middot; ID {{ $unit->uniqueid }}</p>
            <dl class="meta">
                <dt>Ward</dt><dd>{{ optional($ward)->ward_name ?? '-' }}</dd>
                <dt>Local Government</dt><dd>{{ optional($lga)->lga_name ?? '-' }}</dd>
            </dl>

            @if ($results->isEmpty())
                <p class="empty" style="margin-top:16px">No result has been recorded for this polling unit.</p>
            @else
                <table>
                    <thead><tr><th>Party</th><th class="num">Score</th></tr></thead>
                    <tbody>
                        @foreach ($results as $row)
                            <tr>
                                <td class="party">{{ $row->party_abbreviation }}</td>
                                <td class="num">{{ number_format($row->party_score) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr><td>Total</td><td class="num">{{ number_format($results->sum('party_score')) }}</td></tr>
                    </tfoot>
                </table>
            @endif
        </div>
    @endif
@endsection

@push('scripts')
<script>
    (function () {
        const lga = document.getElementById('lga');
        const ward = document.getElementById('ward');
        const unit = document.getElementById('unit');
        const wardsUrl = "{{ url('/ajax/wards') }}";
        const unitsUrl = "{{ url('/ajax/units') }}";

        lga.addEventListener('change', async function () {
            fillSelect(unit, [], '-- Select Polling Unit --');
            if (!lga.value) {
                fillSelect(ward, [], '-- Select Ward --');
                return;
            }
            fillSelect(ward, await fetchOptions(wardsUrl + '/' + lga.value), '-- Select Ward --');
        });

        ward.addEventListener('change', async function () {
            if (!ward.value) {
                fillSelect(unit, [], '-- Select Polling Unit --');
                return;
            }
            fillSelect(unit, await fetchOptions(unitsUrl + '/' + ward.value), '-- Select Polling Unit --');
        });
    })();
</script>
@endpush
