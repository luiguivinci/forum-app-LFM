<?php

namespace App\Http\Controllers\Feed;

use App\Http\Controllers\Controller;
use App\Http\Requests\PostRequest;
use App\Models\comment;
use App\Models\Feed;
use App\Models\Like;
use Illuminate\Http\Request;

class FeedController extends Controller
{
    public function index() {
        $feeds = Feed::with('user')->latest()->get();
        return response([
            'feeds' => $feeds,
        ], 200);
    }
    
    public function store(PostRequest $request) {
        // Valida y obtiene los datos validados
        $validatedData = $request->validated();

        // Verifica si el usuario está autenticado
        if(auth()->check()) {
            // Crea el feed utilizando los datos validados
            auth()->user()->feeds()->create([
                'content' => $validatedData['content']
            ]);

            return response([
                'message' => 'success',
            ], 201);
        } else {
            return response([
                'message' => 'Unauthorized',
            ], 401);
        }
    }

    public function likePost($feed_id) {
        // Select feed with feed id
        $feed = Feed::whereId($feed_id)->first();

        if(!$feed) {
            return response([
                'message' => '404 not found'
            ], 500);
        }

        // Unliked post
        $unliked_post = Like::where('user_id', auth()->id())->where('feed_id', $feed_id)->delete();
        if($unliked_post) {
            return response([
                'message' => 'unliked'
            ], 200);
        }

        // Liked post
        $like_post = Like::create([
            'user_id' => auth()->id(),
            'feed_id' => $feed_id
        ]);
        if($like_post) {
            return response([
                'message' => 'liked'
            ], 200);
        }
    }

    public function comment(Request $request, $feed_id) {

        $request->validate([
            'body' => 'required'
        ]);

        $comment = Comment::create([
            'user_id' => auth()->id(),
            'feed_id' => $feed_id,
            'body' => $request->body
        ]);

        return response([
            'message' => 'success'
        ], 201);
    }

    public function getComments($feed_id) {
        $comments = Comment::with('feed')->with('user')->whereFeedId($feed_id)->latest()->get();

        return response([
            'comments' => $comments
        ], 200);
    }


}
