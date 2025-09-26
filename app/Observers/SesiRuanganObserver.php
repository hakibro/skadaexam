<?php

namespace App\Observers;

use App\Models\SesiRuangan;

class SesiRuanganObserver
{
    /**
     * Handle the SesiRuangan "created" event.
     */
    public function created(SesiRuangan $sesiRuangan): void
    {
        //
    }

    /**
     * Handle the SesiRuangan "updated" event.
     */
    public function updated(SesiRuangan $sesiRuangan): void
    {
        //
    }

    /**
     * Handle the SesiRuangan "deleted" event.
     */
    public function deleted(SesiRuangan $sesiRuangan): void
    {
        //
    }

    /**
     * Handle the SesiRuangan "restored" event.
     */
    public function restored(SesiRuangan $sesiRuangan): void
    {
        //
    }

    /**
     * Handle the SesiRuangan "force deleted" event.
     */
    public function forceDeleted(SesiRuangan $sesiRuangan): void
    {
        //
    }
    public function retrieved(SesiRuangan $sesi)
    {
        $sesi->checkAutoStart()->checkAutoEnd();
    }

    public function saving(SesiRuangan $sesi)
    {
        $sesi->checkAutoStart()->checkAutoEnd();
    }
}
