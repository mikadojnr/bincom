<?php

namespace App\Http\Controllers;

use App\Models\AnnouncedPuResult;
use App\Models\Lga;
use App\Models\Party;
use App\Models\PollingUnit;
use App\Models\Ward;
use Illuminate\Http\Request;

class ResultController extends Controller
{
    /**
     * The dump only contains Local Government Areas of Delta State.
     */
    private const STATE_ID = 25;

    /**
     * Question 1: result announced at a single polling unit.
     */
    public function pollingUnit(Request $request)
    {
        $lgas = $this->lgas();

        $lga = $this->lga($request->query('lga_id'));
        $wards = $lga ? $this->wardsOf($lga) : collect();

        $ward = ($lga && $request->filled('ward_uniqueid'))
            ? Ward::where('uniqueid', $request->query('ward_uniqueid'))->where('lga_id', $lga->lga_id)->first()
            : null;

        $units = $ward
            ? PollingUnit::where('uniquewardid', $ward->uniqueid)->orderBy('polling_unit_name')->get()
            : collect();

        $unit = ($ward && $request->filled('polling_unit_uniqueid'))
            ? PollingUnit::where('uniqueid', $request->query('polling_unit_uniqueid'))->where('uniquewardid', $ward->uniqueid)->first()
            : null;

        $results = $unit
            ? AnnouncedPuResult::where('polling_unit_uniqueid', $unit->uniqueid)
                ->orderBy('party_abbreviation')
                ->get()
            : collect();

        return view('polling-unit', compact(
            'lgas', 'lga', 'wards', 'ward', 'units', 'unit', 'results'
        ));
    }

    /**
     * Question 2: total of all polling unit results under an LGA.
     * Deliberately does NOT read the announced_lga_results table.
     */
    public function lgaSummary(Request $request)
    {
        $lgas = $this->lgas();
        $lga = $this->lga($request->query('lga_id'));

        $totals = collect();

        if ($lga) {
            $totals = AnnouncedPuResult::query()
                ->join('polling_unit', 'polling_unit.uniqueid', '=', 'announced_pu_results.polling_unit_uniqueid')
                ->where('polling_unit.lga_id', $lga->lga_id)
                ->groupBy('announced_pu_results.party_abbreviation')
                ->selectRaw('announced_pu_results.party_abbreviation as party, SUM(announced_pu_results.party_score) as total')
                ->orderBy('announced_pu_results.party_abbreviation')
                ->get();
        }

        return view('lga-summary', compact('lgas', 'lga', 'totals'));
    }

    /**
     * Question 3: store results for every party of a new polling unit.
     */
    public function create()
    {
        return view('new-polling-unit', [
            'lgas' => $this->lgas(),
            'parties' => Party::orderBy('id')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'lga_id' => ['required', 'integer'],
            'ward_uniqueid' => ['required', 'integer'],
            'polling_unit_name' => ['required', 'string', 'max:50'],
            'polling_unit_number' => ['nullable', 'string', 'max:50'],
            'entered_by_user' => ['required', 'string', 'max:50'],
            'scores' => ['required', 'array'],
            'scores.*' => ['nullable', 'integer', 'min:0'],
        ]);

        $lga = Lga::where('lga_id', $data['lga_id'])->where('state_id', self::STATE_ID)->firstOrFail();
        $ward = Ward::where('uniqueid', $data['ward_uniqueid'])->where('lga_id', $lga->lga_id)->firstOrFail();

        $nextId = (int) PollingUnit::where('uniquewardid', $ward->uniqueid)->max('polling_unit_id') + 1;

        $unit = PollingUnit::create([
            'polling_unit_id' => $nextId,
            'ward_id' => $ward->ward_id,
            'lga_id' => $lga->lga_id,
            'uniquewardid' => $ward->uniqueid,
            'polling_unit_number' => $data['polling_unit_number'] ?? null
                ?: sprintf('DT%02d%02d%03d', $lga->lga_id, $ward->ward_id, $nextId),
            'polling_unit_name' => $data['polling_unit_name'],
            'entered_by_user' => $data['entered_by_user'],
            'date_entered' => now(),
            'user_ip_address' => $request->ip(),
        ]);

        $scores = $request->input('scores', []);
        $now = now()->toDateTimeString();
        $rows = [];

        foreach (Party::all() as $party) {
            $rows[] = [
                'polling_unit_uniqueid' => (string) $unit->uniqueid,
                'party_abbreviation' => $party->code,
                'party_score' => (int) ($scores[$party->code] ?? 0),
                'entered_by_user' => $data['entered_by_user'],
                'date_entered' => $now,
                'user_ip_address' => $request->ip(),
            ];
        }

        AnnouncedPuResult::insert($rows);

        return redirect()
            ->route('pu.result', [
                'lga_id' => $lga->lga_id,
                'ward_uniqueid' => $ward->uniqueid,
                'polling_unit_uniqueid' => $unit->uniqueid,
            ])
            ->with('success', "Polling unit \"{$unit->polling_unit_name}\" was saved with results for ".count($rows).' parties.');
    }

    /**
     * Chained combo boxes: wards that belong to the chosen LGA.
     */
    public function wards(int $lgaId)
    {
        return Ward::where('lga_id', $lgaId)
            ->orderBy('ward_name')
            ->get(['uniqueid', 'ward_name'])
            ->map(fn ($ward) => ['id' => $ward->uniqueid, 'name' => $ward->ward_name]);
    }

    /**
     * Chained combo boxes: polling units inside the chosen ward.
     */
    public function units(int $wardUniqueid)
    {
        return PollingUnit::where('uniquewardid', $wardUniqueid)
            ->orderBy('polling_unit_name')
            ->get(['uniqueid', 'polling_unit_name', 'polling_unit_number'])
            ->map(fn ($unit) => [
                'id' => $unit->uniqueid,
                'name' => $unit->polling_unit_name,
                'number' => $unit->polling_unit_number,
            ]);
    }

    private function lgas()
    {
        return Lga::where('state_id', self::STATE_ID)->orderBy('lga_name')->get();
    }

    private function lga($id)
    {
        return $id
            ? Lga::where('lga_id', $id)->where('state_id', self::STATE_ID)->first()
            : null;
    }

    private function wardsOf(Lga $lga)
    {
        return Ward::where('lga_id', $lga->lga_id)->orderBy('ward_name')->get();
    }
}
