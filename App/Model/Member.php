<?php

namespace App\Model;


use More\Src\Lib\Database\Model;

class Member extends Model
{
    protected $table = 'user';

    protected $primaryKey = 'id';

    public function card()
    {
        return $this->hasMany(Card::class, 'user_id', 'id');
    }
}