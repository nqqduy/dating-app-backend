<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Friend extends Model
{
    use HasFactory;


    protected $table = 'friends';
    protected $primaryKey = 'id';

    public $timestamps = true;

    protected $fillable = [
        'id',
        'requestId',
        'responseId',
        'status',
        'created_at',
        'updated_at',
    ];

    public function requestUser() {
        return $this->belongsTo(User::class, 'requestId');
    }

    public function responseUser() {
        return $this->belongsTo(User::class, 'responseId');
    }
}