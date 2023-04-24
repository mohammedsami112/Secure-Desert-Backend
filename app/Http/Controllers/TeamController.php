<?php

namespace App\Http\Controllers;

use App\Traits\CanUploadImage;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;
use App\Traits\Notify;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    use CanUploadImage, Notify;
    /**
     * Check if the team exists
     *
     * @param  int  $id
     * @param  \App\Models\User|null $user
     * @return Team
     */
    public function team($id, $user = null)
    {
        $team = Team::find($id);

        if (! $team) {
            return $this->fail('Team not found', 404);
        }

        if ($user && $team->admin_id !== $user->id) {
            return $this->fail('Not Authorized', 403);
        }

        return $team;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $teams = Team::all();

        foreach($teams as $index => $team) {
            $teams[$index]['total_members'] = TeamMember::where('team_id', $team['id'])->count();
        }

        return $this->respond('All teams', 200, $teams);
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
            'name' => 'required|string|unique:teams,name',
            'slug' => 'string|unique:teams',
            'country' => 'string|size:2',
            'description' => 'string',
            'avatar' => 'image|mimes:png,jpg,jpeg,webp'
        ]);

        // Upload Image if Exists
        if ($request->hasFile('avatar')) {
            $filePath = $this->uploadImage($request, 'avatar', public_path('uploads/teams'));
        }

        $validated['avatar'] = $filePath ?? '';

        $team = Team::create($validated + ['admin_id' => $request->user()->id]);

        TeamMember::create([
            'team_id' => $team->id,
            'user_id' => $request->user()->id,
            'role' => TeamMember::ROLE_ADMIN,
        ]);

        return $this->respond('Team created', 201, $team);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $team = $this->team($id);

        return $this->respond('Team found', 200, $team);
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
        $team = $this->team($id, $request->user());

        $validated = $request->validate([
            'name' => 'string',
            'country' => 'string|size:2',
        ]);

        $team->update($validated);

        return $this->respond('Team updated', 200, $team);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {

        $team = $this->team($id, $request->user());
        if ($request->user()->id !== $team->admin_id) return $this->fail('Unauthorised Action', 401);

        $team->delete();
        foreach ($team->members->pluck('user_id') as $id) {
            $user = User::where('id', $id)->firstOrFail();
            $user->firebase_token ? $this->notifyOne($user->firebase_token, "Your team has been removed", "You are no longer related to a team") : '';
            TeamMember::where('team_id', $team->id)->where('user_id', $id)->delete();
        }
        return $this->respond('Team deleted', 200);
    }
}
