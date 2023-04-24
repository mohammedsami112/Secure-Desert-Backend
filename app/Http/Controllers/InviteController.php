<?php

namespace App\Http\Controllers;

use App\Models\Invite;
use App\Models\TeamMember;
use Illuminate\Http\Request;

class InviteController extends Controller
{
    /**
     * Index
     *
     * @param Request $request
     * @return void
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $invites = Invite::where('user_id', $user->id)
            ->with('team')
            ->get()
            ->map(function ($invite) {
                return [
                    'id' => $invite->id,
                    'from' => $invite->team->name,
                    'status' => $invite->status,
                ];
            });

        return $this->respond('Invites', 200, $invites);
    }

    /**
     * Accept
     *
     * @param Request $request
     * @param int $invite
     * @return void
     */
    public function accept(Request $request, int $invite)
    {
        $invite = Invite::find($invite);

        if (! $invite) {
            return $this->fail('Invite not found', 404);
        }

        $user = $request->user();

        if ($user->id != $invite->user_id) {
            return $this->fail('Not Authorized', 403);
        }

        $teamMember = TeamMember::create([
            'user_id' => $user->id,
            'team_id' => $invite->team_id,
        ]);

        $invite->status = Invite::STATUS_ACCEPTED;
        $invite->save();

        return $this->respond('Invite accepted', 200, $teamMember);
    }

    public function reject(Request $request, $invite)
    {
        $invite = Invite::find($invite);

        if (! $invite) {
            return $this->fail('Invite not found', 404);
        }

        $user = $request->user();

        if ($user->id != $invite->user_id) {
            return $this->fail('Not Authorized', 403);
        }

        $invite->status = Invite::STATUS_REJECTED;
        $invite->save();

        return $this->respond('Invite rejected', 200);
    }
}
