<?php

namespace Database\Seeders;

use App\Models\MatchModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;


class MatchesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Seed matches table
        $this->seedMatches();

        // Seed match_details table
        $this->seedMatchDetails();
    }

    private function seedMatches()
    {
        $faker = Faker::create();
        // Define the number of matches you want to create
        $numMatches = 10;

        for ($i = 1; $i <= $numMatches; $i++) {
            $country1 = $faker->country;
            $country2 = $faker->country;
            $matchName = $country1 . '_vs_' . $country2;

            DB::table('matches')->insert([
                'match_name' => $matchName,
                'country_1' => $country1,
                'country_2' => $country2,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function seedMatchDetails()
    {
        $faker = Faker::create();

        // Get all match IDs
        $matches = MatchModel::all();

        foreach ($matches as $match) {
            // Define the number of match details you want to create for each match

            $countries = [$match->country_1, $match->country_2];

            foreach ($countries as $country) {
                $overs = 20;

                for ($i = 0; $i < $overs; $i++) {

                    for ($x = 0.1; $x <= 0.6; $x = $x + 0.1) {
                        $over = $i + $x;

                        $commentary = $faker->sentence();;
                        $predictedRuns = rand(0, 6);
                        $predictedRunType = Arr::random(['normal', 'legby', 'wicket', 'wide']);
                        $isInvalid = rand(0, 10) > 8 ? 1 : 0;
                        $isResultInvalid = $isInvalid;
                        $actualRuns = $isInvalid ? rand(0, 6) : null;
                        $actualRunType = $isInvalid ? Arr::random(['normal', 'legby', 'wicket', 'wide']) : null;

                        DB::table('match_details')->insert([
                            'match_id' => $match->id,
                            'batting_side' => $country,
                            'over' => $over,
                            'commentary' => $commentary,
                            'predicted_runs' => $predictedRuns,
                            'predicted_run_type' => $predictedRunType,
                            'is_result_invalid' => $isResultInvalid,
                            'actual_runs' => $actualRuns,
                            'actual_run_type' => $actualRunType,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }
    }
}
