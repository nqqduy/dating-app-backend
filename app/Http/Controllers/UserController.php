<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
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
        ->groupBy('users.id')
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
        $user = User::
            where('id', $id)
            ->first();
        $friendOfTwoPeople = DB::table('friends')
            ->where(function($query) use ($id, $userId) {
                $query->where(function($query) use ($id, $userId) {
                    $query->where('requestId', $id)
                          ->where('responseId', $userId);
                })->orWhere(function($query) use ($id, $userId) {
                    $query->where('requestId', $userId)
                          ->where('responseId', $id);
                });
            })->get();

        $friends = DB::table('friends')->where('status', 'APPROVED')->where(function ($query) use ($id) {
                $query->where('requestId', '=', $id)
                    ->OrWhere('responseId', '=', $id);
            })->leftJoin('users as requestUser', 'requestUser.id', '=', 'friends.requestId')
                ->leftJoin('users as responseUser', 'responseUser.id', '=', 'friends.responseId')->get([
                    'requestUser.id as requestUserId',
                    'requestUser.name as requestUserName',
                    'responseUser.id as responseUserId',
                    'responseUser.name as responseUserName',
                ]);
        $friends = $friends->map(function ($friend) use ($id) {
            if($friend->responseUserId != $id) {
                return $friend->responseUserName;
            } 
            return $friend->requestUserName;
        });
        
        $isFriend = false;
        $pendingStatus = null;
        
        if (!$friendOfTwoPeople->isEmpty()) {
            foreach ($friendOfTwoPeople as $friend) {
                if ($friend->status === 'APPROVED') {
                    $isFriend = true;
                    break;
                }
                $pendingStatus = $friend->status;
            }
        }
        
        $formattedResult = [
                'id' => $user->id,
                'name' => $user->name,
                'avatar' => $user->avatar,
                'bio' => $user->bio,
                'isFriend' => $isFriend,
                'status' => $pendingStatus,
                'friends'  => $friends,
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

        if($status) {
            $query->where('status', $status);
        }

        $result =  $query->join('users', 'friends.requestId', '=', 'users.id')
            ->where('responseId', $userId)
            ->orderBy('friends.created_at', 'DESC')
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
        $userId = $request->jwtUserId;
        $query = DB::table('friends')
        ->where(function($query) use ($id, $userId) {
            $query->where(function($query) use ($id, $userId) {
                $query->where('requestId', $id)
                      ->where('responseId', $userId);
            })->orWhere(function($query) use ($id, $userId) {
                $query->where('requestId', $userId)
                      ->where('responseId', $id);
            });
        });
        if($action === 'ACCEPT') {
            $query->update([
                'status' => 'APPROVED'
            ]);
        } elseif ($action === 'DECLINE') {
            $query->delete();
        }
        return response()->json(
            [
                'message' => 'Successfully',
                'data' => 1
            ]);
    }

    public function send_message(Request $request, $id) {
        $content = $request->input('content');
        $senderId = $request->jwtUserId;
        $receiveId = $id;

        $insertData = [
            "content" => $content,
            "senderId" => $senderId,
            "receiveId" => $receiveId,
            "created_at" => Carbon::now('Asia/Ho_Chi_Minh')
        ];

        DB::table('messenger')->insert($insertData);

        return response()->json(
            [
                'message' => 'Successfully',
                'data' => 1
            ]);
    }

    public function get_list_message(Request $request, $id) {
        $ownId = $request->jwtUserId;
        $chatId = $id;

        $messages = DB::table('messenger')->where(function($query) use ($ownId, $chatId) {
            $query->where(function($query) use ($ownId, $chatId) {
                $query->where('senderId', $ownId)
                      ->where('receiveId', $chatId);
            })->orWhere(function($query) use ($ownId, $chatId) {
                $query->where('senderId', $chatId)
                      ->where('receiveId', $ownId);
            });
        })
        ->leftJoin('users as sender', 'messenger.senderId', '=', 'sender.id')
        ->leftJoin('users as receiver', 'messenger.receiveId', '=', 'receiver.id')
        ->select(
            'messenger.id as id',
            'messenger.content as content',
            'messenger.created_at as date',
            'sender.name as senderName',
            'sender.id as senderId',
            'sender.avatar as senderAvatar',
            'receiver.name as receiverName',
            'receiver.avatar as receiverAvatar',
            'receiver.avatar as receiverId'
        )
        ->orderBy('messenger.created_at', 'ASC')
        ->get();
        

        foreach ($messages as $message) {
            $isMessageRightSide = $ownId === $message->senderId;
            if($isMessageRightSide) {
                $message->side = 'RIGHT';
            } else {
                $message->side = 'LEFT';
            }
        }
        return response()->json(
            [
                'message' => 'Successfully',
                'data' => $messages
            ]);
    }
}
