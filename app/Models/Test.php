<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Test extends Model
{
	protected $table = 'test';
    //
	const STATE_EMPLOY = 0;//使用中
	const STATE_FAILURE = 1;//已失效

	const STATE_MENU = [
		self::STATE_EMPLOY => '使用中',
		self::STATE_FAILURE => '已失效',
	];

	protected $fillable = [
		'id',
		'name',
		'title',
	];

}
