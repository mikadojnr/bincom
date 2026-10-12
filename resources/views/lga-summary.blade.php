@extends('layouts.app')

@section('title', 'LGA Summed Result')

@section('content')
    <div class="card">
        <h2>LGA Summed Result</h2>
        <p class="muted">
            This is the sum of every polling unit result under the selected Local Government. It is computed from the
            polling unit results only &mdash; the announced LGA result table is not used here.
        </p>

        <form method="GET" action="{{ route('lga.summary') }}">
            <div class="grid">
                <div>
                    <label for="lga">Local Government</label>
                    <select name="lga_id" id="lga" onchange="this.form.submit()">
                        <option value="">-- Select LGA --</option>
                        @foreach ($lgas as $item)
                            <option value="{{ $item->lga_id }}" @selected(optional($lga)->lga_id == $item->lga_id)>{{ $item->lga_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <noscript><div class="actions"><button type="submit">Show Result</button></div></noscript>
        </form>
    </div>

    @if ($lga)
        <div class="card">
            <h2>{{ $lga->lga_name }}</h2>
            <p class="muted">Summed total of all polling unit results under this Local Government.</p>

            @if ($totals->isEmpty())
                <p class="empty">No polling unit results were found for this Local Government.</p>
            @else
                <table>
                    <thead><tr><th>Party</th><th class="num">Summed Score</th></tr></thead>
                    <tbody>
                        @foreach ($totals as $row)
                            <tr>
                                <td class="party">{{ $row->party }}</td>
                                <td class="num">{{ number_format($row->total) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr><td>Total</td><td class="num">{{ number_format($totals->sum('total')) }}</td></tr>
                    </tfoot>
                </table>
            @endif
        </div>
    @endif
@endsection
