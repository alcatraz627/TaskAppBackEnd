<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model {
    use SoftDeletes;
    // protected $dateFormat="Y-m-d H:i";
    protected $fillable = ['title', 'description', 'status', 'created_by', 'assigned_to', 'due_date'];

    protected $hidden = ['deleted_at'];

    protected $casts = [
        // 'due_date' => 'date_format:Y-m-d\TH:i'
        'due_date' => 'datetime:Y-m-d\TH:i'
    ];

    // protected $with = ['user'];

    // public function setDueDateAttribute($value) {
    //     return date ("Y-d-m H:i:s", strtotime($value));
    // }
}

