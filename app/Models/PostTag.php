<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PostTag extends Model { protected $fillable = ['post_id','user_id','x_position','y_position']; protected $casts = ['x_position'=>'float','y_position'=>'float']; public function user(){ return $this->belongsTo(User::class); } public function post(){ return $this->belongsTo(Post::class); } }
