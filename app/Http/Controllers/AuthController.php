<?php

namespace App\Http\Controllers;

use JWTAuth;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTFactory;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)  {
        try {
            $name = $request->input('name');
            $username = $request->input('username');
            $password = $request->input('password');
            $avatar = $request->input('avatar');

            $isExistUsername = DB::table('users')->where('username', $username)->first();
            if($isExistUsername) {
                return response()->json(
                    [
                        'message' => 'Username đã tồn tại'
                    ], 
                    400
                );
            }

            $insertUser = [
                "name" => $name,
                "password" => Hash::make($password),
                "username" => $username,
                "avatar" => $avatar,
                "bio" => null
            ];

            DB::table('users')->insert($insertUser);

            return response()->json(
                [
                    'message' => 'Đăng ký thành công',
                    "data" => 1
                ], 200
            );
        } catch(QueryException $e) {
            return response()->json(
                [
                    'message' => 'Có lỗi xảy ra'
                ], 500
            );
        }
        
    }

    public function login(Request $request)
    {
        $user_name = $request->input('username');
        $password = $request->input('password');

        $user = User::where('username', $user_name)->first();
        if(!$user) {
            return response()->json(
                [
                    'message' => 'Tên đăng nhập hoặc mật khẩu không đúng'
                ], 401);
        }
        if (!Hash::check($password, $user->password)) {
            return response()->json(
                [
                    'message' => 'Tên đăng nhập hoặc mật khẩu không đúng'
                ], 401);
        }

        $payload = JWTFactory::sub($user->id)->make();
        $token = JWTAuth::encode($payload);

        return response()->json([
            'message' => 'Successfully',
            'data' => [
                'access_token' => $token->get(),
                'token_type' => 'jwt',
                'expires_in' => auth()->factory()->getTTL() * 60,
                'name' => $user->name,
                'avatar' => $user->avatar,
                'username' => $user->username,
                'id' => $user->id
            ]
        ], 200);
    }
}