<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response([
            'posts' => Post::orderby('created_at', 'desc')
                ->with('user:id,name,image')
                ->withcount('comments', 'likes')
                ->with('likes', function ($like) {
                    return $like->where('user_id', auth()->user()->id)
                        ->select('id', 'user_id', 'post_id')
                        ->get();
                })
                ->get()
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
    public function store(Request $request)
    {
        $request->validate([
            'body' => 'required',
            'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $image = $this->saveImg($request->file('image'), 'posts');
        } else {
            $image = null;
        }

        $post = Post::create([
            'user_id' => auth()->user()->id,
            'body' => $request->body,
            'image' => $image,
        ]);

        return response([
            'post' => $post,
            'message' => 'Post created successfully',
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
        return response([
            'post' => Post::find($id)->with('user:id,name,image')->withcount('comments', 'likes')->get()
        ], 200);
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
        $post = Post::find($id);

        if (!$post) {
            return response([
                'message' => 'Post not found'
            ], 401);
        }

        if ($post->user_id !== auth()->user()->id) {
            return response([
                'message' => 'Acces denied'
            ], 401);
        }

        $request->validate([
            'body' => 'required',
            'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($request->image) {
            $imageName = time() . '.' . $request->image->extension();

            $request->image->move(public_path('images'), $imageName);
        } else {
            $imageName = $post->image;
        }

        $post->update([
            'body' => $request->body,
            'image' => $imageName,
        ]);

        return response([
            'post' => $post,
            'message' => 'Post updated successfully'
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $post = Post::find($id);

        if (!$post) {
            return response([
                'message' => 'Post not found'
            ], 401);
        }

        if ($post->user_id !== auth()->user()->id) {
            return response([
                'message' => 'Acces denied'
            ], 401);
        }

        if ($post->image) {
            unlink(public_path('images/' . $post->image));
        }

        $post->comments()->delete();
        $post->likes()->delete();
        $post->delete();

        return response([
            'message' => 'Post deleted successfully'
        ], 200);
    }
}
