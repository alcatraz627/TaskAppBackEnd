<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model {
    use SoftDeletes;
    protected $fillable = ['title', 'description', 'status', 'created_by', 'assigned_to', 'due_date'];
}

