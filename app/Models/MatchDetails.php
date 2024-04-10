<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MatchDetails extends Model
{
    use HasFactory;

    protected $table = 'match_details';

    protected $fillable = ['match_id', 'batting_side', 'over', 'commentary', 'predicted_runs', 'predicted_run_type', 'is_result_invalid', 'actual_runs', 'actual_run_type'];
}
