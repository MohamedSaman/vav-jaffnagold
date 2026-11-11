<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Read-only model for ZKBio Time personnel_employee table.
 * Do NOT write to this table — it's managed by ZKBio.
 */
class ZkEmployee extends Model
{
    protected $table = 'personnel_employee';

    public $timestamps = false;

    protected $guarded = ['id'];

    protected $casts = [
        'hire_date' => 'date',
        'birthday' => 'date',
        'is_active' => 'boolean',
        'enable_payroll' => 'boolean',
    ];

    protected static function booted()
    {
        static::saving(function ($model) {
            $now = \Carbon\Carbon::now();
            if (\Illuminate\Support\Facades\Schema::hasColumn('personnel_employee', 'change_time')) {
                $model->change_time = $now;
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('personnel_employee', 'update_time')) {
                $model->update_time = $now;
            }
        });
    }

    /**
     * Get the department this employee belongs to.
     */
    public function department()
    {
        return $this->belongsTo(ZkDepartment::class, 'department_id');
    }

    /**
     * Get the position of this employee.
     */
    public function position()
    {
        return $this->belongsTo(ZkPosition::class, 'position_id');
    }

    /**
     * Get the areas assigned to this employee.
     */
    public function areas()
    {
        return $this->belongsToMany(ZkArea::class, 'personnel_employee_area', 'employee_id', 'area_id');
    }

    /**
     * Get all punch transactions for this employee.
     */
    public function transactions()
    {
        return $this->hasMany(ZkTransaction::class, 'emp_id');
    }

    /**
     * Get HR salary records.
     */
    public function hrSalaries()
    {
        return $this->hasMany(HrSalary::class, 'zk_employee_id');
    }

    /**
     * Get employee HR settings (working hours, salary rates).
     */
    public function hrSetting()
    {
        return $this->hasOne(HrEmployeeSetting::class, 'emp_code', 'emp_code');
    }

    /**
     * Get full name.
     */
    public function getFullNameAttribute(): string
    {
        $first = $this->first_name ?? '';
        $last = $this->last_name ?? '';

        // ZKBio sometimes duplicates first_name into last_name
        if ($first === $last || empty($last)) {
            return $first;
        }

        return trim("{$first} {$last}");
    }

    /**
     * Get the primary area name.
     */
    public function getPrimaryAreaAttribute(): string
    {
        return $this->areas->first()?->area_name ?? 'Unassigned';
    }
}
