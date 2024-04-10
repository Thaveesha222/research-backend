<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('match_details', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\MatchModel::class,'match_id');
            $table->double('over');
            $table->longText('commentary');
            $table->integer('predicted_runs');
            $table->string('predicted_run_type');
            $table->boolean('is_result_invalid')->default(false);
            $table->integer('actual_runs')->nullable();
            $table->string('actual_run_type')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('match_details');
    }
};
