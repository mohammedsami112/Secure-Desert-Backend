<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\Course;
use App\Models\User;
use App\Traits\CanUploadImage;
use App\Traits\Notify;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BlogsController extends Controller
{
    use Notify, CanUploadImage;
    /**
     * Check if the team exists
     *
     * @param  int  $id
     * @return Course
     */
    // Edited By Mohammed
    public function blog($id)
    {
        $blog = Blog::find($id);

        if (! $blog) {
            return $this->fail('Blog not found', 404);
        }



        // if ($user && !$user->subscribed_at) {
        //     return $this->fail('Not Authorized', 403);
        // }

        return $blog;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // if (!Auth::user()->subscribed_at) return $this->fail('Not Authorized', 403); // Edit By Mohammed

        // $blogs = Blog::all(); // Edited By Mohammed

        $blogs = Blog::query()
        ->with(['admin' => function ($query) {$query->select('id', 'name');}])
        ->get();



        return $this->respond('All Blogs', 200, $blogs);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'topic' => 'required|string',
            'image' => 'image|mimes:png,jpg,jpeg,webp',
            'content' => 'required|min:20', // Edited By Mohammed
            'admin' => 'required|numeric',
            'team' => 'numeric', // Edited By Mohammed
        ]);

        // Upload Image if Exists
        if ($request->hasFile('image')) {
            $filePath = $this->uploadImage($request, 'image', public_path('uploads/blogs'));
        }

        // $blog = Blog::create($validated + ['image' => $filePath ?? '']);

        // Edited By Mohammed
        $validated['image'] = $filePath;
        $blog = Blog::create($validated);

        $tokens = User::whereNotNull('firebase_token')->pluck('firebase_token');

        $this->notifyMany($tokens, "New Blog", $blog->title);

        return $this->respond('Blog created', 201, $blog);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $blog = $this->blog($id); // Edited By Mohammed

        return $this->respond('Blog found', 200, $blog);
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
        $blog = $this->blog($id);
        $validated = $request->validate([
            'title' => 'required|string',
            'topic' => 'required|string',
            'image' => 'image|mimes:png,jpg,jpeg,webp',
            'content' => 'required|min:20', // Edited By Mohammed
            'admin' => 'required|numeric',
            'team' => 'numeric', // Edited By Mohammed
        ]);

        // Upload Image if Exists
        if ($request->hasFile('image')) {
            $filePath = $this->uploadImage($request, 'image', public_path('uploads/blogs'));
        }

        // $blog->update($validated + ['image' => $filePath ?? '']);

        // Edited By Mohammed
        $validated['image'] = $filePath ?? $blog->image;

        $blog->update($validated);


        return $this->respond('Blog updated', 200, $blog);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $blog = $this->blog($id);
        $blog->delete();

        return $this->respond('Blog deleted', 200);
    }
}
