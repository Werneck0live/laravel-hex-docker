<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SkillModel extends Model
{
    use HasFactory;
    protected $table = 'skills';
    protected $fillable = ['name', 'level', 'years_experience', 'tags', 'status_id'];
    protected $casts = ['tags' => 'array'];
}
