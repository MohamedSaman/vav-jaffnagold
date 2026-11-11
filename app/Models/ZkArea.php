<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Read-only model for ZKBio Time personnel_area table.
 */
class ZkArea extends Model
{
    protected $table = 'personnel_area';

    public $timestamps = false;

    protected $guarded = ['*'];

    /**
     * Get employees assigned to this area.
     */
    public function employees()
    {
        return $this->belongsToMany(ZkEmployee::class, 'personnel_employee_area', 'area_id', 'employee_id');
    }
}
