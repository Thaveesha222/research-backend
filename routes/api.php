<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

//Create a new match
Route::post('/create-match',[\App\Http\Controllers\MatchController::class,'create_match']);

//Get all existing matches
Route::get('/all-match',[\App\Http\Controllers\MatchController::class,'get_matches']);

//Get match details by match id
Route::get('/match-details/{match_id}',[\App\Http\Controllers\MatchController::class,'get_match_details_by_match_id']);

//Get match details by match id and batting_side
Route::get('/match-details/{match_id}/{batting_side}',[\App\Http\Controllers\MatchController::class,'get_match_details_by_match_id_and_batting_side']);

//Get match details by match id and batting_side and over
Route::get('/match-details/{match_id}/{batting_side}/{over}',[\App\Http\Controllers\MatchController::class,'get_match_details_by_match_id_and_batting_side_and_over']);

//Get match details by match id and batting_side and over and ball
Route::get('/match-details/{match_id}/{batting_side}/{over}/{ball}',[\App\Http\Controllers\MatchController::class,'get_match_details_by_match_id_and_batting_side_and_over_and_ball']);

//Submit commentary
Route::post('/submit-commentary',[\App\Http\Controllers\MatchController::class,'submit_commentary']);

//Submit correction
Route::post('/rectify-commentary',[\App\Http\Controllers\MatchController::class,'rectify_commentary']);
