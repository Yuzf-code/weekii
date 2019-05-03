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
    protected $table = 'member';

    protected $primaryKey = 'id';
}