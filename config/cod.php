<?php

return [
    /*
    |--------------------------------------------------------------------------
    | How attributed COD / splits are shown per delivery stop (informational UI).
    |--------------------------------------------------------------------------
    |
    | proportional_merchandise — Split order totals by each package's merchandise share.
    |
    | single_collection_final_delivery — Full order COD and ledger-style splits count only on the
    | package delivered last (latest delivery_completed_at). Earlier stops show ₱0 until the order completes.
    |
    */
    'allocation_policy' => env('COD_ALLOCATION_POLICY', 'proportional_merchandise'),

];
