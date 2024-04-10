<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MatchModel extends Model
{
    use HasFactory;

    protected $table = 'matches';

    protected $fillable = ['match_name', 'country_1', 'country_2'];

    public function match_details()
    {
        return $this->hasMany(MatchDetails::class,'match_id','id');
    }
}
