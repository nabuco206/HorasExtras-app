<?php

namespace App\Observers;

use App\Models\TblLider;
use App\Models\TblPersona;

class TblLiderObserver
{
    /**
     * Handle the TblLider "creating" event.
     */
    public function creating(TblLider $tblLider): void
    {
        if ($tblLider->persona_id) {
            $persona = TblPersona::find($tblLider->persona_id);
            if ($persona) {
                $tblLider->username = $persona->UserName;
            }
        }
    }

    /**
     * Handle the TblLider "updating" event.
     */
    public function updating(TblLider $tblLider): void
    {
        if ($tblLider->isDirty('persona_id') && $tblLider->persona_id) {
            $persona = TblPersona::find($tblLider->persona_id);
            if ($persona) {
                $tblLider->username = $persona->UserName;
            }
        }
    }

    /**
     * Handle the TblLider "created" event.
     */
    public function created(TblLider $tblLider): void
    {
        //
    }

    /**
     * Handle the TblLider "updated" event.
     */
    public function updated(TblLider $tblLider): void
    {
        //
    }

    /**
     * Handle the TblLider "deleted" event.
     */
    public function deleted(TblLider $tblLider): void
    {
        //
    }

    /**
     * Handle the TblLider "restored" event.
     */
    public function restored(TblLider $tblLider): void
    {
        //
    }

    /**
     * Handle the TblLider "force deleted" event.
     */
    public function forceDeleted(TblLider $tblLider): void
    {
        //
    }
}
