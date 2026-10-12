@extends('layouts.app')

@section('title', 'New Polling Unit')

@section('content')
    <form method="POST" action="{{ route('pu.store') }}">
        @csrf

        <div class="card">
            <h2>New Polling Unit</h2>
            <p class="muted">Create a polling unit and record the score of every party in one step.</p>

            <div class="grid">
                <div>
                    <label for="lga">Local Government</label>
                    <select name="lga_id" id="lga">
                        <option value="">-- Select LGA --</option>
                        @foreach ($lgas as $item)
                            <option value="{{ $item->lga_id }}" @selected(old('lga_id') == $item->lga_id)>{{ $item->lga_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="ward">Ward</label>
                    <select name="ward_uniqueid" id="ward" @disabled(true)>
                        <option value="">-- Select Ward --</option>
                    </select>
                </div>
            </div>

            <div class="grid" style="margin-top:14px">
                <div>
                    <label for="polling_unit_name">Polling Unit Name</label>
                    <input type="text" id="polling_unit_name" name="polling_unit_name" value="{{ old('polling_unit_name') }}" maxlength="50" required>
                </div>
                <div>
                    <label for="polling_unit_number">Polling Unit Number <span class="muted">(optional)</span></label>
                    <input type="text" id="polling_unit_number" name="polling_unit_number" value="{{ old('polling_unit_number') }}" maxlength="50">
                </div>
                <div>
                    <label for="entered_by_user">Entered By</label>
                    <input type="text" id="entered_by_user" name="entered_by_user" value="{{ old('entered_by_user') }}" maxlength="50" required>
                </div>
            </div>
        </div>

        <div class="card">
            <h2>Party Scores</h2>
            <p class="muted">Enter the votes for each party. Leave blank if there were none.</p>

            <table>
                <thead><tr><th>Party</th><th class="num">Score</th></tr></thead>
                <tbody>
                    @foreach ($parties as $party)
                        <tr>
                            <td class="party">{{ $party->partyid }}</td>
                            <td class="num">
                                <input type="number" min="0" name="scores[{{ $party->code }}]"
                                       value="{{ old('scores.'.$party->code, 0) }}"
                                       style="width:140px; text-align:right; padding:7px 10px; border:1px solid #cbd5e1; border-radius:6px;">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="actions"><button type="submit">Save Results</button></div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    (function () {
        const lga = document.getElementById('lga');
        const ward = document.getElementById('ward');
        const wardsUrl = "{{ url('/ajax/wards') }}";
        const previousWard = "{{ old('ward_uniqueid') }}";

        lga.addEventListener('change', async function () {
            if (!lga.value) {
                fillSelect(ward, [], '-- Select Ward --');
                return;
            }
            fillSelect(ward, await fetchOptions(wardsUrl + '/' + lga.value), '-- Select Ward --', previousWard);
        });

        if (lga.value) {
            lga.dispatchEvent(new Event('change'));
        }
    })();
</script>
@endpush
