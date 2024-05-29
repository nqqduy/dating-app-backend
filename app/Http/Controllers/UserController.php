<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use function Laravel\Prompts\table;

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

    public function find_one(Request $request, $id) {
        $userId = $request->jwtUserId;
        $user = User::with(['requestFriends' => function($query) {
            $query->where('status', 'APPROVED');
        }, 'requestFriends.requestUser', 'responseFriends' => function($query) {
            $query->where('status', 'APPROVED');
        }, 'responseFriends.responseUser'])
            ->where('id', $id)
            ->first();
        $isFriend = DB::table('friends')->where('status', 'APPROVED')
            ->where(function($query) use ($id, $userId) {
                $query->where(function($query) use ($id, $userId) {
                    $query->where('requestId', $id)
                          ->where('responseId', $userId);
                })->orWhere(function($query) use ($id, $userId) {
                    $query->where('requestId', $userId)
                          ->where('responseId', $id);
                });
            })->exists();
        
        $friendFromRequest = $user->requestFriends->map(function ($friend) {
            return $friend->requestUser->name;
        });
        
        $friendFromResponse = $user->responseFriends->map(function ($friend) {
            return $friend->responseUser->name;
        });

        $formattedResult = [
                'id' => $user->id,
                'name' => $user->name,
                'bio' => $user->bio,
                'isFriend' => $isFriend,
                'friends' => $friendFromRequest->merge($friendFromResponse)
            ];
        
        return response()->json(
            [
                'message' => 'Successfully',
                'data' => $formattedResult
            ]);
    }

    public function create_request_friends(Request $request, $id) {
        $userId = $request->jwtUserId;
        if($userId == $id) {
            return response()->json(
                [
                    'message' => 'Bạn không thể gửi yêu cầu kết bạn tới người này'
                ]); 
        }

        $insertData = [
            'requestId' => $userId,
            'responseId' => $id,
            'status' => 'PENDING'
        ];

        DB::table('friends')->insert($insertData);
        return response()->json(
            [
                'message' => 'Đã gửi yêu cầu kết bạn',
                'data' => 1
            ]);
    }

    public function find_many_friends_by_user(Request $request) {
        $userId = $request->jwtUserId;
        $status = $request->input('status', null);
        
        $query = DB::table('friends');
        print_r($status);
        if($status) {
            $query->where('status', $status);
        }

        $result =  $query->join('users', 'friends.requestId', '=', 'users.id')
            ->where('responseId', $userId)
            ->get([
                'friends.id as friendId',
                'friends.status',

                'users.id as userId',
                'users.name',
                'users.avatar',
            ]);

        return response()->json(
            [
                'message' => 'Successfully',
                'data' => $result
            ]);
    }

    public function accept_or_decline_request(Request $request, $id) {
        $action = $request->input('action', null);
        if($action === 'ACCEPT') {
            DB::table('friends')
            ->where('id', $id)
            ->update([
                'status' => 'APPROVED'
            ]);
        } elseif ($action === 'DECLINE') {
            DB::table('friends')
            ->where('id', $id)->delete();
        }
        return response()->json(
            [
                'message' => 'Successfully',
                'data' => 1
            ]);
    }
}
