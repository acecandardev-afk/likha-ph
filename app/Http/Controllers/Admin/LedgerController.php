<?php

namespace App\Http\Controllers\Admin;

use App\Models\LedgerJournal;

class LedgerController extends AdminController
{
    public function index()
    {
        $journals = LedgerJournal::query()
            ->with(['order.customer', 'order.artisan', 'lines'])
            ->latest('posted_at')
            ->paginate(25);

        return view('admin.ledger.index', compact('journals'));
    }

    public function show(LedgerJournal $journal)
    {
        $journal->load(['lines', 'order.customer', 'order.artisan']);

        return view('admin.ledger.show', compact('journal'));
    }
}
