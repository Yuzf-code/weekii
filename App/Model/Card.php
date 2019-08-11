<?php
/**
 * Created by PhpStorm.
 * User: weeki
 * Date: 2019/8/11
 * Time: 20:02
 */

namespace App\Model;


use Weekii\Lib\Database\Model;

class Card extends Model
{
    protected $table = 'card';

    protected $primaryKey = 'id';
}