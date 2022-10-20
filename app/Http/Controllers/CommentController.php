<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function home($id)
    {
        $post = Post::find($id);

        return response([
            'comments' => $post->comments()->with('user:id,name,image')->get()
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $id)
    {
        $post = Post::find($id);

        if (!$post) {
            return response([
                'message' => 'Post not found'
            ], 401);
        }

        $request->validate([
            'comment' => 'required|string',
        ]);

        Comment::create([
            'user_id' => auth()->user()->id,
            'post_id' => $id,
            'comment' => $request->comment,
        ]);

        return response([
            'message' => 'Comment created successfully'
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $comment = Comment::find($id);

        if (!$comment) {
            return response([
                'message' => 'Comment not found'
            ], 401);
        }

        if ($comment->user_id !== auth()->user()->id) {
            return response([
                'message' => 'Acces denied'
            ], 401);
        }

        $request->validate([
            'comment' => 'required|string',
        ]);

        $comment->update([
            'comment' => $request->comment,
        ]);

        return response([
            'message' => 'Comment updated successfully'
        ], 201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $comment = Comment::find($id);

        if (!$comment) {
            return response([
                'message' => 'Comment not found'
            ], 401);
        }

        if ($comment->user_id !== auth()->user()->id) {
            return response([
                'message' => 'Acces denied'
            ], 401);
        }

        $comment->delete();

        return response([
            'message' => 'Comment deleted successfully'
        ], 201);
    }
}
