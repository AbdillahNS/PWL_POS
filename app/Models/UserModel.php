<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class UserModel extends Model
{
    use HasFactory;

    protected $table = 'm_user'; //Mendefinikan nama tabel yang digunakan oleh model ini
    protected $primaryKey = 'user_id'; // Mendefinikan primary key tabel ini

    // @var array
    protected $fillable = ['level_id', 'username', 'nama'];
}