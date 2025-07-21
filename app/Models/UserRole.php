<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    use HasFactory;
    protected  $table = 'user_roles';
    protected $primaryKey = null;

    public $incrementing = false;

    public $timestamps = false;
    protected $fillable = [
        'user_id',
        'role_id'
    ];
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime:d-M-Y H:m:s',
            'password' => 'hashed',
            'created_at' => 'datetime:d-M-Y H:m:s',
            'updated_at' => 'datetime:d-M-Y H:m:s'
        ];
    }
    public function role(){
        return $this->belongsTo(Role::class,'role_id','id');
    }
        public function user(){
        return $this->belongsTo(User::class,'user_id','id');
    }

}
