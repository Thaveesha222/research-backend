<?php

namespace App\Http\Controllers;

use App\Models\MatchDetails;
use App\Models\MatchModel;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MatchController extends Controller
{
    public function create_match(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'match_name' => 'required',
            'country_1' => 'required',
            'country_2' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        return MatchModel::create([
            'match_name' => $request->match_name,
            'country_1' => $request->country_1,
            'country_2' => $request->country_2,
        ]);
    }

    public function get_matches()
    {
        return MatchModel::all();
    }

    public function get_match_details_by_match_id($match_id)
    {
        return MatchDetails::where('match_id', $match_id)->get();
    }

    public function get_match_details_by_match_id_and_batting_side($match_id, $batting_side)
    {
        return MatchDetails::where('match_id', $match_id)
            ->where('batting_side', $batting_side)
            ->get();
    }

    public function get_match_details_by_match_id_and_batting_side_and_over($match_id, $batting_side, $over)
    {
        $over = ((integer)$over);
        return MatchDetails::where('match_id', $match_id)
            ->where('batting_side', $batting_side)
            ->where('over', '>=', $over)
            ->where('over', '<', $over + 1)
            ->get();
    }

    public function get_match_details_by_match_id_and_batting_side_and_over_and_ball($match_id, $batting_side, $over, $ball)
    {
        $over = ((integer)$over) + (((integer)$ball) / 10);
        return MatchDetails::where('match_id', $match_id)
            ->where('batting_side', $batting_side)
            ->where('over', '=', $over)
            ->get();
    }

    public function submit_commentary(Request $request)
    {
        $validator_1 = Validator::make($request->all(), [
            'match_id' => 'required|integer|exists:matches,id',
            'commentary' => 'required|string',
        ]);


        $match = MatchModel::with('match_details')->find($request->match_id);
        $match_details = $match->match_details;
        $commentary = $request->commentary;

        $validator_2 = Validator::make($request->all(), [
            'batting_side' => 'required|string|in:' . $match->country_1 . ',' . $match->country_2,
        ]);

        $batting_side = $request->batting_side;
        $min_over = $match_details->where('batting_side', $batting_side)->max('over') ?? 0.1;
        $max_over = ($match_details->where('batting_side', $batting_side)->max('over') ?? 0.1) + 0.1;

        $validator_3 = Validator::make($request->all(), [
            'batting_side' => 'required|string|in:' . $match->country_1 . ',' . $match->country_2,
            'over' => ['required', 'regex:/^\d+\.(0|1|2|3|4|5)$/', 'numeric', 'min:' . $min_over, 'max:' . $max_over],
        ]);

        if ($validator_1->fails() || $validator_2->fails() || $validator_3->fails()) {
            return response()->json(['errors' => $validator_1->errors()->toArray() + $validator_2->errors()->toArray() + $validator_3->errors()->toArray()], 422);
        }


        //invoking model
        $client = new Client([
            'base_uri' => 'http://commentary_predictor:5001/', // Update the URI if necessary
            'timeout' => 2.0,
        ]);

        try {
            $response = $client->post('predict_run_type', [
                'json' => [
                    'commentary' => $commentary,
                ]
            ]);

            $predicted_run_type = json_decode($response->getBody()->getContents(), true)['predicted_runs'];
            $run_type = $this->mapRunType($predicted_run_type);

            $response = $client->post('predict_run_count', [
                'json' => [
                    'commentary' => $commentary,
                ]
            ]);

            $predicted_run_count = json_decode($response->getBody()->getContents(), true)['predicted_runs'][0][0];

        } catch (\GuzzleHttp\Exception\RequestException $e) {
            return $e->getMessage();
        }


        //Make ML model prediction
        return MatchDetails::updateOrCreate(
            [
                'match_id' => $match->id,
                'batting_side' => $batting_side,
                'over' => $request->over
            ],
            [
                'match_id' => $match->id,
                'batting_side' => $batting_side,
                'over' => $request->over,
                'commentary' => $commentary,
                'predicted_runs' => $predicted_run_count, //Replace by value produced by ML,
                'predicted_run_type' => $run_type, // Replace by value produced by ML
                'is_result_invalid' => 0,
                'actual_runs' => null,
                'actual_run_type' => null
            ]
        );
    }

    public function rectify_commentary(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'match_detail_id' => 'required|integer|exists:match_details,id',
            'actual_runs' => 'required|numeric|min:0|max:6',
            'actual_run_type' => 'required|string|in:normal,lb,wicket,wide',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $match_detail = MatchDetails::find($request->match_detail_id);

        $match_detail->is_result_invalid = 1;
        $match_detail->actual_runs = $request->actual_runs;
        $match_detail->actual_run_type = $request->actual_run_type;

        $match_detail->save();

        return $match_detail;
    }

    // Method to map predicted run type to description
    private function mapRunType($predicted_run_type)
    {
        $mapping = [
            0 => 'wicket',
            1 => 'normal',
            2 => 'lb',
            3 => 'wide',
            4 => 'nb',
        ];
        return $mapping[$predicted_run_type] ?? 'Unknown'; // Return 'Unknown' if run type is not found in mapping
    }
}
