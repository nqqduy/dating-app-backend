<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PostController extends Controller
{
    public function create(Request $request) {
        try {
            $authorId = $request->jwtUserId;
            $content = $request->input('content');
            $title = $request->input('title');

            $insertData = [
                "title" => $title,
                "content" => $content,
                "authorId" => $authorId,
                "date" => Carbon::now()
            ];

            DB::table('posts')->insert($insertData);

            return response()->json([
                'message' => 'Thêm thành công',
                'data' => 1
            ]);
        } catch(QueryException $e) {
            return response()->json(
                [
                    'message' => 'Có lỗi xảy ra'
                ], 500
            );
        }
    }

    public function find_many(Request $request) {
        $pageIndex = $request->input('pageIndex', 1); 
        $pageSize = $request->input('pageSize', 10); 
        $userId = $request->input('userId', null); 

        $query = Post::with(['author', 'comments.user'])
            ->orderBy('created_at', 'DESC')
            ->select('posts.id', 'posts.content', 'posts.date', 'authorId');

        if($userId) {
            $query = $query->where('authorId', '=', $userId);
        }
            
        $post_list = $query->paginate($pageSize, ['*'], 'page', $pageIndex);

        $formattedResult = $post_list->getCollection()->map(function ($post) {
            return [
                'id' => $post->id,
                'content' => $post->content,
                'date' => $post->date,
                'author' => $post->author->name,
                'authorId' => $post->author->id,

                'comments' => $post->comments->map(function ($comment) {
                    return [
                        'id' => $comment->id,
                        'content' => $comment->content,
                        'date' => $comment->date,
                        'user' => $comment->user->name
                    ];
                })->toArray()
            ];
        })->toArray();

        $post_list->setCollection(collect($formattedResult));

        return response()->json(
            [
                'message' => 'Successfully',
                'data' => $post_list
            ]);
    }

    public function create_comment(Request $request, $id) {
        $userId = $request->jwtUserId;
        $content = $request->input('content');

        $insertData = [
            'userId' => $userId,
            'content' => $content,
            'date' => Carbon::now(),
            'postId' => $id
        ];

        DB::table('comments')->insert($insertData);

        return response()->json(
            [
                'message' => 'Thành công',
                'data' => 1
            ]);
    }
}
