<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class ZnzjUsers extends Model
{
    protected $primaryKey = null; // or null
    public $incrementing = false;
    protected $connection = 'mysql3';
    public function __construct(array $attributes = [])
    {
        if(config('app.env') == 'production'){ // 中南项目
            $this->table = 'v_Myuser';
        }else{
            $this->table = 'v_Myuser';
        }
        parent::__construct($attributes);
    }
}
