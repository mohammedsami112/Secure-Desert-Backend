<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Traits\Notify;
use Illuminate\Http\Request;

class NewsController extends Controller
{

    // Added By Mohammed
    use Notify;


    /**
     * Check if the news exists
     *
     * @param  int  $id
     * @return News
     */
    public function news($id)
    {
        $news = News::find($id);

        if (! $news) {
            return $this->fail('News not found', 404);
        }

        return $news;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $news = News::all();

        return $this->respond('All news', 200, $news);
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
            'team_id' => 'integer',
            'title' => 'required|string',
            'content' => 'required',
        ]);

        $news = News::create($validated + ['admin_id' => $request->user()->id]);

        $this->notifyAll("New News", $news->title);

        return $this->respond('News created', 201, $news);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $news = $this->news($id);

        return $this->respond('News', 200, $news);
    }

    // Added By Mohammed
    public function update(Request $request, $id)
    {
        $news = $this->news($id);
        $validated = $request->validate([
            'title' => 'required|string',
            'content' => 'required',
        ]);

        $news->update($validated);

        return $this->respond('News updated', 200, $news);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $news = $this->news($id);

        $news->delete();

        return $this->respond('News deleted', 200);
    }
}
