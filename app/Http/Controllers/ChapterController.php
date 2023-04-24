<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use Illuminate\Http\Request;

class ChapterController extends Controller
{
    /**
     * Check if the chapter exists
     *
     * @param  int  $id
     * @return chapter
     */
    public function chapter($id)
    {
        $chapter = Chapter::find($id);

        if (!$chapter) {
            return $this->fail('Chapter not found', 404);
        }

        return $chapter;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $chapters = Chapter::all();

        return $this->respond('All Chapters', 200, $chapters);
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
            'course_id' => 'required|integer|exists:courses,id'
        ]);

        $chapter = Chapter::create($validated);

        return $this->respond('Chapter created', 201, $chapter);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $chapter = $this->chapter($id);

        return $this->respond('Chapter found', 200, $chapter);
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
        $chapter = $this->chapter($id);
        $validated = $request->validate([
            'title' => 'required|string',
            'course_id' => 'required|integer|exists:courses,id'
        ]);

        $chapter->update($validated);

        return $this->respond('Chapter updated', 200, $chapter);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $chapter = $this->chapter($id);
        $chapter->delete();

        return $this->respond('Chapter deleted', 200);
    }
}
