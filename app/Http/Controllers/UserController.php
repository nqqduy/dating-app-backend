<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserController extends Controller
{

    public function find_many(Request $request) {
        $userId = $request->jwtUserId;
        $pageIndex = $request->input('pageIndex', 1); 
        $pageSize = $request->input('pageSize', 10); 
        $isFriend = $request->input('isFriend');

        $query = DB::table('users')
        ->leftJoin('friends as requestFriends', 'requestFriends.requestId', '=', 'users.id')
        ->leftJoin('friends as responseFriends', 'responseFriends.responseId', '=', 'users.id')
        ->select(
            'users.id',
            'users.name',
            'users.avatar',
            'requestFriends.status as requestFriendsStatus',
            'responseFriends.status as responseFriendsStatus',
        )
        ->where('users.id', '<>', $userId);
    
        if($isFriend === 'true') {
            $query->where(function($query) {
                $query->where('requestFriends.status', '=', 'APPROVED')
                    ->orWhere('responseFriends.status', '=', 'APPROVED');
            });
        } elseif($isFriend === 'false') {
            $query->where(function($query) {
                $query->where('requestFriends.status', '=', 'PENDING')
                    ->orWhere('responseFriends.status', '=', 'PENDING');
            });
        }
        
        $users = $query->paginate($pageSize, ['*'], 'page', $pageIndex);
        
        foreach ($users as $user) {
            $user->isFriend = ($user->requestFriendsStatus === 'APPROVED' || $user->responseFriendsStatus === 'APPROVED');
            unset($user->requestFriendsStatus, $user->responseFriendsStatus);
        }
    
        return response()->json(
            [
                'message' => 'Successfully',
                'data' => $users
            ]);
    }

    // {
    //     id: 1,
    //     name: "Tác giả 1",
    //     bio: "Đây là thông tin của tác giả 1.",
    //     friends: ["Tác giả 2", "Tác giả 3"],
    //       isFriend : true
    //   }
    public function find_one(Request $request, $id) {
        $userId = $request->jwtUserId;
        $user = DB::table('users')->where('id', '=', $id)
            ->select(
                'users.id',
                'users.name',
                ''
            )
            ->first();
    }
}
