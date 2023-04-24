<?php

namespace App\Http\Controllers;

use App\Models\Invite;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Http\Request;

class TeamMembersController extends Controller
{
    /**
     * Check if the team member exists
     *
     * @param  int  $id
     * @param  \App\Models\User|null $user
     * @return TeamMember
     */
    public function teamMember($id, $user = null)
    {
        $teamMember = TeamMember::find($id);

        if (! $teamMember) {
            return $this->fail('Team member not found', 404);
        }

        // if ($user && ($user != $teamMember->user() || $user != $teamMember->team()->admin())) {
        //     return $this->fail('Not Authorized', 403);
        // }

        return $teamMember;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($team)
    {
        // Edited By Mohammed
        $members = TeamMember::where('team_id', $team)->with(['user' => function ($query) {$query->select('id', 'name');}])->get();

        // foreach ($members as $member) {
        //     $member->user_name = $member::with('user');
        // }

        return $this->respond('All team members', 200, $members);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param int $teamId
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, int $team)
    {
        $user = User::where('username', $request->username)->first();
        if (!$user) {
            return $this->fail('Username Not Found');
        }

        $request['username'] = $user->id;

        $validated = $request->validate([
            'username' => 'required|exists:users,id|unique:team_members,user_id',

        ], [
            'username.unique' => "This User Already Has A Team"
        ]);

        $user_id = User::where('id', $validated['username'])->first()->id;
        if (TeamMember::where('user_id', $user_id)->exists()) {
            return $this->fail('User already has a team.');
        }

        $invite = Invite::create([
            'user_id' => $validated['username'],
            'team_id' => $team,
        ]);

        return $this->respond('Team Member Invited', 201, $invite);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $teamId
     * @param  int  $memberId
     * @return \Illuminate\Http\Response
     */
    public function show($teamId, $memberId)
    {
        $member = $this->teamMember($memberId);

        return $this->respond('Team member', 200, $member);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $teamId
     * @param  int  $memberId
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $team, $member)
    {
        $member = $this->teamMember($member, $request->user());
        $member->delete();

        return $this->respond('Team member deleted', 200);
    }
}
