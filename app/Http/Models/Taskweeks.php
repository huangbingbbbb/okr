<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class Taskweeks extends Model
{
    protected $primaryKey = 'id'; // or null
    public $incrementing = false;
    public function __construct(array $attributes = [])
    {
        if(config('app.env') == 'production'){
            $this->table = 'okr_taskweeks';
        }else{
            $this->table = 'okr_taskweeks';
        }
        parent::__construct($attributes);
    }
}
