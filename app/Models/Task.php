<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model {
    use SoftDeletes;
    // protected $dateFormat="Y-m-d H:i";
    protected $fillable = ['title', 'description', 'status', 'created_by', 'assigned_to', 'due_date'];
    protected $casts = [
        'due_date' => 'datetime:Y-m-d\TH:i'
    ];
}

