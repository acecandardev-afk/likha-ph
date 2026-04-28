<?php

namespace App\Http\Controllers\Artisan;

use App\Models\LedgerJournal;

class LedgerController extends ArtisanController
{
    /**
     * Read-only ledger entries for this maker's orders after delivery settlement.
     */
    public function index()
    {
        $artisan = $this->getArtisan();

        $journals = LedgerJournal::query()
            ->whereHas('order', fn ($q) => $q->where('artisan_id', $artisan->id))
            ->with(['order.customer', 'lines'])
            ->latest('posted_at')
            ->paginate(25);

        return view('artisan.ledger.index', compact('journals'));
    }

    public function show(LedgerJournal $journal)
    {
        $this->authorizeArtisanOrder($journal);

        $journal->load(['lines', 'order.customer']);

        return view('artisan.ledger.show', compact('journal'));
    }

    protected function authorizeArtisanOrder(LedgerJournal $journal): void
    {
        $journal->loadMissing('order');
        if ($journal->order === null || (int) $journal->order->artisan_id !== (int) $this->getArtisan()->id) {
            abort(403);
        }
    }
}
