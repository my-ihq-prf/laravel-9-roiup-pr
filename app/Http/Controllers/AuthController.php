<?php

namespace App\Http\Controllers;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Payload;

use App\Models\User;

class AuthController extends Controller
{
    //
    //public function __construct()
    //{
    // $this->middleware('auth:api', ['except' => ['login','register']]);
    //}
    function _getToken(Request $request, Closure $next, Closure $error = null)
    {

        try {
            $request->validate([
                'name' => 'required|string|min:3|max:11',
                'phone_number' => 'required|between:8,13|regex:/^([0-9\s\-\+\(\)]*)$/',
            ]);
        } catch (ValidationException $exception) {
            if (!is_null($error)) {
                return  $error($exception->errors());
            } else {
                return response()->json([
                    'status' => 'error',
                    'msg'    => 'Error',
                    'errors' => $exception->errors(),
                ], 422);
            }
        }
        $credentials = $request->only('name', 'phone_number');
        $credentials['phone_number']=preg_replace("/[^0-9.]/", "",$credentials['phone_number']);

        $user = User::where('phone_number', $credentials['phone_number'])->first();
        $token = null;
   
        $_errFn=function() use($error,$credentials){
            
            $e = [
                'status' => 'error',
                'message' => 'Unauthorized',
                'credentials' => $credentials,
            ];
            if (!is_null($error)) {
                return  $error($e);
            } else {
                return response()->json($e, 401);
            }
        };

        if (!is_null($user)) {

            // $token = Auth::login($user);
            $token = auth()->tokenById($user['id']);
            if (!$token) {
                return  $_errFn();
            }
        }else{
            return $_errFn();
        }


        //$credentials['password']=$user['password'];
        //$token = Auth::attempt($credentials);
        //$vPass =  Hash::check('__'.$user['phone_number'], $user['password']);
        // $token = Auth::($credentials);
        //$token = Auth::attempt($credentials);


        return $next($user, $token);
    }
    public function login(Request $request)
    {
        return $this->_getToken($request, function ($user, $token) {
            if (!is_null($user) && !is_null($token)) {

                return response(json_encode([
                    'status' => 'success',
                    'message' => 'User login successfully',
                    'user' => $user,
                ]), 200, [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $token
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Forbidden',
                ], 403);
            }
        }, function ($errors) {
            if ($errors['message'] == 'Unauthorized' && is_array($errors['credentials'])) {
                $credentials = $errors['credentials'];
                $credentials['password']=Hash::make('__'.$credentials['phone_number']);

                $user = User::create($credentials);
                $token = Auth::login($user);
                // $user ['token']=Auth::login($user);
                /*$response=response();
            $response->headers->set('Authorization', 'Bearer '.$token);
            Access-Control-Allow-Origin: *
*/
                /* return response()->json([
                'status' => 'success',
                'message' => 'User created successfully',
                'user' => $user,
                'authorisation' => [
                    'token' => $token,
                    'type' => 'bearer',
                ]
                ]);*/
                return response(json_encode([
                    'status' => 'success',
                    'message' => 'User created successfully',
                    'user' => $user,
                ]), 200, [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $token
                ]);
            }
            return response()->json(['Unauthorized' => $errors['message'] == 'Unauthorized'], 401);
        });
    }

    public function logout()
    {
        Auth::logout();
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out',
        ]);
    }
    public function authenticate(Request $request)
    {
        if( $request->bearerToken())
        {
             try {
               $payload = auth()->payload();
            } catch (\Exception $e) {
                // do something
                return response()->json([
                    'status' => 'error',
                    'error' => $e->getMessage(),
                    'message' => 'Forbidden',
                ], 403);
            }

        }

    }
    public function allUsers(Request $request)
    {
        $_errFn = function () {

            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized Access!',

            ], 401);
        };

        $user = auth()->user();
        if (!is_null($user)) {
            $users = User::all();
            $usersTotal = count($users );
            return response()->json([
                'status' => 'success',
                'message' => "You have {$usersTotal} users!",
                'users' => $users->toArray() ,
            ], 200);

        }
        return $_errFn();
    }
}
