<?php
/**
 * Created by PhpStorm.
 * User: weeki
 * Date: 2019/5/4
 * Time: 0:12
 */

namespace App\Model;


use Weekii\Lib\Database\Model;

class Member extends Model
{
    protected $table = 'user';

    protected $primaryKey = 'id';

    public function card()
    {
        return $this->hasMany(Card::class, 'user_id', 'id');
    }
}