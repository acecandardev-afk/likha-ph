<?php

namespace App\Http\Controllers\Admin;

use App\Models\LedgerJournal;
use App\Models\LedgerLine;
use App\Models\RiderRemittanceReport;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CodTreasuryController extends AdminController
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        $fromInput = $validated['date_from'] ?? null;
        $toInput = $validated['date_to'] ?? null;

        if ($fromInput === null && $toInput === null) {
            $from = now()->startOfMonth()->startOfDay();
            $to = now()->endOfDay();
        } elseif ($fromInput !== null && $toInput !== null) {
            $from = Carbon::parse($fromInput)->startOfDay();
            $to = Carbon::parse($toInput)->endOfDay();
            if ($from->greaterThan($to)) {
                return back()->withErrors(['date_from' => 'Start must be on or before end.']);
            }
        } else {
            return back()->withErrors(['date_from' => 'Provide both dates or leave both blank for this month.']);
        }

        $bucketTotals = LedgerLine::query()
            ->join('ledger_journals', 'ledger_lines.ledger_journal_id', '=', 'ledger_journals.id')
            ->where('ledger_journals.kind', LedgerJournal::KIND_DELIVERY_SETTLEMENT)
            ->whereBetween('ledger_journals.posted_at', [$from, $to])
            ->groupBy('ledger_lines.bucket')
            ->selectRaw('ledger_lines.bucket, SUM(ledger_lines.amount) as total')
            ->pluck('total', 'bucket');

        $journalCount = LedgerJournal::query()
            ->where('kind', LedgerJournal::KIND_DELIVERY_SETTLEMENT)
            ->whereBetween('posted_at', [$from, $to])
            ->count();

        $remittanceDeclared = (float) RiderRemittanceReport::query()
            ->whereBetween('report_date', [$from->toDateString(), $to->toDateString()])
            ->sum('cod_declared_total');

        $remittanceRows = RiderRemittanceReport::query()
            ->with('rider')
            ->whereBetween('report_date', [$from->toDateString(), $to->toDateString()])
            ->orderByDesc('report_date')
            ->limit(50)
            ->get();

        $ledgerCod = round((float) ($bucketTotals[LedgerLine::BUCKET_COD_COLLECTIBLE] ?? 0), 2);

        return view('admin.cod-treasury.index', compact(
            'from',
            'to',
            'bucketTotals',
            'journalCount',
            'remittanceDeclared',
            'remittanceRows',
            'ledgerCod'
        ));
    }
}
