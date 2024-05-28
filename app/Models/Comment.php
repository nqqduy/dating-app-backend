<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;


    protected $table = 'comments';
    protected $primaryKey = 'id';

    public $timestamps = true;

    protected $fillable = [
        'id',
        'content',
        'date',
        'created_at',
        'updated_at',
        "userId",
        "postId"
    ];

    public function post()
    {
        return $this->belongsTo(Post::class, 'postId');
    }
    
    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }
}