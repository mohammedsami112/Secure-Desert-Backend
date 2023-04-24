<?php

namespace App\Http\Controllers;

use App\Models\Achievement;
use App\Traits\CanUploadImage;
use Illuminate\Http\Request;

class AchievementsController extends Controller
{
    use CanUploadImage;
    /**
     * Check if the achievement exists
     *
     * @param  int  $id
     * @return Achievement
     */
    public function achievement($id)
    {
        $achievement = Achievement::find($id);

        if (! $achievement) {
            return $this->fail('Achievement not found', 404);
        }

        return $achievement;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $achievements = Achievement::all();

        return $this->respond('All Achievements', 200, $achievements);
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
            'tile' => 'required|string',
            'badge' => 'image|mimes:png,jpg,jpeg,webp',
            'points' => 'required|numeric',
        ]);

        // Upload Image if Exists
        if ($request->hasFile('badge')) {
            $filePath = $this->uploadImage($request, 'badge', public_path('uploads/badges'));
        }

        $achievement = Achievement::create($validated + ['badge' => $filePath ?? '']);

        return $this->respond('Achievement created', 201, $achievement);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $achievement = $this->achievement($id);

        return $this->respond('Achievement found', 200, $achievement);
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
        $achievement = $this->achievement($id);
        $validated = $request->validate([
            'tile' => 'required|string',
            'badge' => 'image|mimes:png,jpg,jpeg,webp',
            'points' => 'required|numeric',
        ]);

        // Upload Image if Exists
        if ($request->hasFile('badge')) {
            $filePath = $this->uploadImage($request, 'badge', public_path('uploads/badges'));
        }

        $achievement->update($validated + ['badge' => $filePath ?? '']);

        return $this->respond('Achievement updated', 200, $achievement);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $achievement = $this->achievement($id);
        $achievement->delete();

        return $this->respond('Achievement deleted', 200);
    }
}
