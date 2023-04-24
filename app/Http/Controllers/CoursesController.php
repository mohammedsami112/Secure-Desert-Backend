<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\Course;
use App\Traits\CanUploadImage;
use Illuminate\Http\Request;

class CoursesController extends Controller
{
    use CanUploadImage;
    /**
     * Check if the course exists
     *
     * @param  int  $id
     * @return Course
     */
    public function course($id, $getRelations = false)
    {
        $course = Course::with(['category', 'chapters'])->where('id', $id)->first();

        if ($getRelations) {
            $chapters = $course->chapters;
            foreach ($chapters as $chapter) {
                $chapterCollection = Chapter::where('id', $chapter->id)->with('lessons')->get();
                $chapter->lessons = $chapterCollection[0]['lessons'];
            }
        }

        return $course;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $ids = Course::all(['id']);

        $courses = [];
        foreach ($ids as $id) {
            $courses[] = $this->course($id->id, true);
        }

        return $this->respond('All Courses', 200, $courses);
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
            'description' => 'string',
            'thumbnail' => 'image|mimes:png,jpg,jpeg,webp',
            'level' => 'required|numeric',
            'price' => 'required|numeric',
            'category_id' => 'required|integer|exists:categories,id',
            'hours' => 'integer|gte:0',
            'weeks' => 'string',
            'creator_name' => 'string',
            'creator_role' => 'string',
            'creator_bio' => 'string'
        ]);

        // Upload Image if Exists
        if ($request->hasFile('thumbnail')) {
            $filePath = $this->uploadImage($request, 'thumbnail', public_path('uploads/courses'));
        }

        $validated['thumbnail'] = $filePath ?? '';
        $course = Course::create($validated);

        return $this->respond('Course created', 201, $course);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $course = $this->course($id, true);

        return $this->respond('Course found', 200, $course);
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
        $course = $this->course($id);
        $validated = $request->validate([
            'title' => 'required|string',
            'description' => 'string',
            'thumbnail' => 'image|mimes:png,jpg,jpeg,webp',
            'level' => 'required|numeric',
            'price' => 'required|numeric',
            'category_id' => 'required|numeric|exists:categories,id',
            'hours' => 'numeric|gte:0',
            'weeks' => 'string',
            'creator_name' => 'string',
            'creator_role' => 'string',
            'creator_bio' => 'string'
        ]);

        // Upload Image if Exists
        if ($request->hasFile('thumbnail')) {
            $filePath = $this->uploadImage($request, 'thumbnail', public_path('uploads/courses'));
        }

        $validated['thumbnail'] = $filePath ?? $course->thumbnail;
        $course->update($validated);

        return $this->respond('Course updated', 200, $this->course($id));

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $course = $this->course($id);
        $course->delete();

        return $this->respond('Course deleted', 200);
    }
}
