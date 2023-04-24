<?php

namespace App\Http\Controllers;

use App\Models\Achievement;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use App\Models\Task;
use App\Models\SolvedTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UsersController extends Controller
{
    /**
     * Check if the task exists
     *
     * @param  int  $id
     * @return user
     */
    public function user($id)
    {
        $user = User::find($id);

        if (!$user) return $this->fail('User not found', 404);

        return $user;
    }

    /**
     * Check if the username exists
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function checkUsername(Request $request)
    {
        $query = User::where('username', $request->username);

        if ($request->user()) {
            $query->where('id', '!=', $request->user()->id);
        }

        $user = $query->first();

        return $this->respond('Check Username', 200, [
            'username' => $request->username,
            'exists' => $user ? true : false
        ]);
    }

    /**
     * get top 10 Users .
     *
     * @param  int  $limit
     * @return \Illuminate\Http\Response
     */
    public function topUsers($limit = 10)
    {
        $users = User::where('points', '>', 0)->orderBy('points', 'DESC')->limit($limit)->get();
        $total_points = Task::all(['points']);
        $total_points = array_sum(array_column($total_points->toArray(), 'points'));
        // Append Level To Users
        foreach ($users as $index => $user) {
            // $achievment = Achievement::where('points', '<=', $user->points)->orderBy('points', 'DESC')->first();
            // if ($achievment) {
            //     $user['level'] = $achievment->title;
            // } else {
            //     $user['level'] = 'There\'s No Achievments Yet';
            // }

            // User Level
            $user_percentage = ($total_points ? ($user->points / $total_points) * 100 : 0);
            switch (true) {
                case $user_percentage <= 0:
                    $level = ['code' => 0, 'level' => 'Noop'];
                    break;

                case $user_percentage < 5:
                    $level = ['code' => 1, 'level' => 'Keyboard Kiddie'];
                    break;

                case $user_percentage < 15:
                    $level = ['code' => 2, 'level' => 'Hacker'];
                    break;

                case $user_percentage < 25:
                    $level = ['code' => 3, 'level' => 'Cyber geek'];
                    break;

                case $user_percentage < 45:
                    $level = ['code' => 4, 'level' => 'Silent Hacker'];
                    break;

                case $user_percentage < 85:
                    $level = ['code' => 5, 'level' => 'System Killer'];
                    break;

                case $user_percentage <= 100:
                    $level = ['code' => 6, 'level' => 'Deadeye'];
                    break;
            }
            $users[$index]['level'] = $level;

        }

        return $this->respond('Top 10 Users', 200, $users);
    }

    public function getAllUsers(){
        $users = User::with(['team'])->get();

        $total_points = Task::all(['points']);
        $total_points = array_sum(array_column($total_points->toArray(), 'points'));

        foreach($users as $index => $user) {
            // User Rank
            $topUsers = User::orderBy('points', 'DESC')->get()->toArray();
            $topUsersIndexes = array_column($topUsers, 'id');
            $users[$index]['rank'] = array_search($user['id'], $topUsersIndexes) + 1;

            // User Level
            $user_percentage = ($total_points ? ($user->points / $total_points) * 100 : 0);
            switch (true) {
                case $user_percentage <= 0:
                    $level = ['code' => 0, 'level' => 'Noop'];
                    break;

                case $user_percentage < 5:
                    $level = ['code' => 1, 'level' => 'Keyboard Kiddie'];
                    break;

                case $user_percentage < 15:
                    $level = ['code' => 2, 'level' => 'Hacker'];
                    break;

                case $user_percentage < 25:
                    $level = ['code' => 3, 'level' => 'Cyber geek'];
                    break;

                case $user_percentage < 45:
                    $level = ['code' => 4, 'level' => 'Silent Hacker'];
                    break;

                case $user_percentage < 85:
                    $level = ['code' => 5, 'level' => 'System Killer'];
                    break;

                case $user_percentage <= 100:
                    $level = ['code' => 6, 'level' => 'Deadeye'];
                    break;
            }
            $users[$index]['level'] = $level;

            // Total Solved Challenges
            $users[$index]['solved_challenges'] = SolvedTask::where('user_id', $user['id'])->count();

        }


        return $this->respond('All Users', 200, $users);
    }

    public function toggleStatus(Request $request)
    {
        $validated = $request->validate(['user_id' => 'required|integer|exists:users,id',
        ]);

        $user = User::find($validated['user_id']);
        $toggle = User::where('id', $user->id)->update(['is_active' => !($user->is_active)]);

        return $this->respond('Change User Status', 200, $toggle);
    }

    public function changePassword(Request $request)
    {
        $validated = $request->validate(['user_id' => 'required|integer|exists:users,id',
            'old_password' => 'required',
            'password' => 'required|confirmed'
        ]);

        $user = $this->user($validated['user_id']);

        if (!Hash::check($request->old_password, $user->password)) $this->fail('Old Password is Wrong');
        $user->update(['password' => Hash::make($validated['password'])]);

        return $this->respond('Change User Password', 200);

    }

    /**
     * get All Subscribed Users .
     *
     * @return \Illuminate\Http\Response
     */
    public function subscribers()
    {
        $ids = Subscription::where('expires_at', '>', Carbon::now()->format('Y-m-d'))->distinct()->pluck('user_id');

        $users = User::whereIn('id', $ids)->get();
        foreach($users AS $user) {
            $user->subscribtion = Subscription::with('plan')->where('user_id', $user->id)->orderBy('expires_at', 'Desc')->first();
        }

        return $this->respond('All Subscribed Users', 200, $users);
    }


}
