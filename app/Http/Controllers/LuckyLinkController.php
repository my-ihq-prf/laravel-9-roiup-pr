<?php

namespace App\Http\Controllers;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Payload;

use App\Models\LuckyLink;
use App\Models\User;

class LuckyLinkController extends Controller
{

    public function generate(Request $request)
    {
        $user = auth()->user();
        if (!is_null($user)) {
            $newUrl = base64_encode(Hash::make('__' . $user['phone_number']));
            $llnk = LuckyLink::where('phone_number', $user['phone_number'])->first();
            if (is_null($llnk)) {

                $llnk = LuckyLink::create([
                    'phone_number' => $user['phone_number'],
                    'url' => $newUrl,
                ]);
            } else {
                $llnk->where('phone_number', $user['phone_number'])->update(['url' => $newUrl]);
            }

            if (!is_null($llnk)) {

                return response()->json([
                    'status' => 'success',
                    'message' => 'Lucky link was successfully generated!',
                    'link' => $llnk['url'],
                ], 200);
            }
        }
        return response()->json([
            'status' => 'error',
            'message' => 'No user or no "Lucky link" generated!',
        ], 200);
    }

    public function deactivate(Request $request)
    {

        $_errFn = function () {
            return response()->json([
                'status' => 'error',
                'message' => 'No user or no "Lucky link" found!',
            ], 200);
        };


        $user = auth()->user();
        if (!is_null($user)) {

            $llnk = LuckyLink::where('phone_number', $user['phone_number']);
            if (!is_null($llnk)&&count($llnk->get('phone_number')->toArray())) {
                $llnk->delete();
                return response()->json([
                    'status' => 'success',
                    'message' => 'Lucky link was successfully deactivated!',
                ], 200);
            }
        }
        return $_errFn();
    }

    public function copy(Request $request)
    {
        $user = auth()->user();
        if (!is_null($user)) {
            // auth()->logout(true);
            $llnk = LuckyLink::where('phone_number', $user['phone_number'])->first();
            if (!is_null($llnk)) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Lucky link was successfully received!',
                    'link' => $llnk['url'],
                ], 200);
            } else {

                return response()->json([
                    'status' => 'error',
                    'message' => 'Lucky link not exist!',
                ], 200);
            }
        }
    }
    public function index(Request $request)
    {
        $llnk = $request->segment(1);
        if (!is_null($llnk)) {

            $llnk = LuckyLink::where('url', $llnk)->first();
            // $llnk =$llnk;
            if (is_null($llnk)) {
                $user = auth()->user();
                if (!is_null($user)) {
                    auth()->logout(true);
                }
                return view('app');
            }
        } else {
            return view('app');
        }
    }
    public function check(Request $request)
    {
        $llnk = $request->segment(3);
        if (!is_null($llnk)) {
            $_errFn = function () {


                return response()->json([
                    'status' => 'error',
                    'message' => 'No user or no "Lucky link" found!',
                ], 401);
            };

            $llnk = LuckyLink::where('url', $llnk)->first();
            // $llnk =$llnk;
            if (!is_null($llnk)) {
                $user = User::where('phone_number', $llnk['phone_number'])->first();

                if (!is_null($user)) {
                    // auth()->logout(true);
                    $token = auth()->tokenById($user['id']);
                    if (!$token) {
                        return $_errFn();
                    } else {
                        return response(json_encode([
                            'status' => 'success',
                            'message' => 'User login successfully',
                            'user' => $user,
                        ]), 200, [
                            'Content-Type' => 'application/json',
                            'Authorization' => 'Bearer ' . $token
                        ]);
                    }
                }
            } else {
                return $_errFn();
            }
        }
    }
}
