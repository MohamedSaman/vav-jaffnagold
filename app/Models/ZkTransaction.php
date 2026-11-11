<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Read-only model for ZKBio Time iclock_transaction table.
 * Contains raw fingerprint punch logs from biometric devices.
 */
class ZkTransaction extends Model
{
    protected $table = 'iclock_transaction';

    public $timestamps = false;

    protected $guarded = ['*'];

    protected $casts = [
        'punch_time' => 'datetime',
        'upload_time' => 'datetime',
    ];

    /**
     * Get the employee this transaction belongs to.
     */
    public function employee()
    {
        return $this->belongsTo(ZkEmployee::class, 'emp_code', 'emp_code');
    }
}
