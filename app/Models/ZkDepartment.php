<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Read-only model for ZKBio Time personnel_department table.
 */
class ZkDepartment extends Model
{
    protected $table = 'personnel_department';

    public $timestamps = false;

    protected $guarded = ['*'];

    /**
     * Get employees in this department.
     */
    public function employees()
    {
        return $this->hasMany(ZkEmployee::class, 'department_id');
    }
}
