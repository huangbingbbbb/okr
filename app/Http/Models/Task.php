<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $primaryKey = 'id'; // or null
    public $incrementing = false;
    public function __construct(array $attributes = [])
    {
        if(config('app.env') == 'production'){
            $this->table = 'okr_task';
        }else{
            $this->table = 'okr_task';
        }
        parent::__construct($attributes);
    }
}
