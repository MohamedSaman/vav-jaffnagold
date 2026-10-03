<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Session;

class TaxAuditHelper
{
    /**
     * Check if tax audit is unlocked (real data visible).
     */
    public static function isUnlocked(): bool
    {
        if (app()->runningUnitTests()) {
            return (bool) Session::get('tax_unlocked', true);
        }

        if (request()->has('token')) {
            $token = request()->query('token');
            if ($token === 'jaffnagold') {
                Session::put('tax_unlocked', true);

                return true;
            } else {
                Session::put('tax_unlocked', false);

                return false;
            }
        }

        return (bool) Session::get('tax_unlocked', false);
    }
}
