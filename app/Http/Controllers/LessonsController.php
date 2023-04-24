<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\Request;

class LessonsController extends Controller
{
    /**
     * Check if the team exists
     *
     * @param  int  $id
     * @return Course
     */
    public function lesson($id)
    {
        $lesson = Lesson::find($id);

        if (! $lesson) {
            return $this->fail('Lesson not found', 404);
        }

        return $lesson;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $lessons = Lesson::all();

        return $this->respond('All lessons', 200, $lessons);
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
            'url' => 'required',
            'chapter_id' => 'required|numeric|exists:chapters,id',
        ]);

        $lesson = lesson::create($validated);

        return $this->respond('Lesson created', 201, $lesson);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $lesson = $this->lesson($id);

        return $this->respond('Lesson found', 200, $lesson);
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
        $lesson = $this->lesson($id);
        $validated = $request->validate([
            'title' => 'required|string',
            'description' => 'string',
            'url' => 'required',
            'chapter_id' => 'required|numeric|exists:chapters,id'
        ]);

        $lesson->update($validated);

        return $this->respond('Lesson updated', 200, $lesson);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $lesson = $this->lesson($id);
        $lesson->delete();

        return $this->respond('Lesson deleted', 200);
    }
}
