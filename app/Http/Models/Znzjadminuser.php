<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class Znzjadminuser extends Model
{
    protected $primaryKey = null; // or null
    public $incrementing = false;
    protected $connection = 'mysql3';
    public function __construct(array $attributes = [])
    {
        if(config('app.env') == 'production'){ // 中南项目
            $this->table = 'znzj_adminusers';
        }else{
            $this->table = 'znzj_adminusers';
        }
        parent::__construct($attributes);
    }
}
