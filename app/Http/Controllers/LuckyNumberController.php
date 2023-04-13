<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LuckyNumber;

class LuckyNumberController extends Controller
{
    //
    public function generate(Request $request)
    {
        $_errFn = function () {

            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized Access!',

            ], 401);
        };

        $user = auth()->user();
        if (!is_null($user)) {

            $numRand = rand(1, 1000);

            LuckyNumber::create([
                'phone_number' => $user['phone_number'],
                'num' => $numRand,
            ]);

            $winState = 0;

            switch ($numRand) {
                case $numRand > 900:
                    $winState = 80;
                    break;
                case $numRand > 600:
                    $winState = 60;
                    break;
                case $numRand > 300:
                    $winState = 20;
                    break;
                default:
                    $winState = 10;
            }

            return response()->json([
                'status' => 'success',
                'message' => "Now {$numRand} is your number!",
                'num' => $numRand,
                'state' => 'You '.($numRand%2==0?'win':'lose').': '. "{$winState}% rate",
           
            ], 200);
        }
        return  $_errFn();
    }
    public function history(Request $request){
        $_errFn = function () {

            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized Access!',

            ], 401);
        };

        $user = auth()->user();
        if (!is_null($user)) {

            $lNumbers = LuckyNumber::where('phone_number', $user['phone_number'])->get('num');
            $lNumbersTotal = count($lNumbers);

            return response()->json([
                'status' => 'success',
                'message' => "You have {$lNumbersTotal} lucky numbers!",
                'l-numbers' => $lNumbers,
            ], 200);
        }
        return  $_errFn();
    }
}

