<?php

namespace App\Http\Controllers;

use App\Models\Achievement;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\SolvedTask;
use App\Models\Subscription;
use App\Models\Task;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    /**
     * Check if the task exists
     *
     * @param  int  $id
     * @return user
     */
    public function user($id)
    {
        $user = User::with(['team', 'courses'])->where('id', $id)->first();

        if (!$user) return $this->fail('User not found', 404);

        $subscription = Subscription::where('user_id', $user->id)->orderBy('expires_at', 'Desc')->first();
        $user['subscribtion'] = [
            'status' => $subscription && $subscription->expires_at->isFuture(),
            'days_left' => $subscription ? $subscription->expires_at->diffInDays(Carbon::now()) : 0,
            'days' => $subscription ? $subscription->days : 0
        ];

        $total_points = Task::all(['points']);
        $total_points = array_sum(array_column($total_points->toArray(), 'points'));
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
        $user['level'] = $level;

        $easy_tasks_ids = Task::where('level', 1)->get(['id']);
        $med_tasks_ids = Task::where('level', 2)->get(['id']);
        $hard_tasks_ids = Task::where('level', 3)->get(['id']);
        $warrior_tasks_ids = Task::where('level', 4)->get(['id']);
        $easy_solved_tasks = SolvedTask::where('user_id', $id)->whereIn('id', $easy_tasks_ids)->count();
        $med_solved_tasks = SolvedTask::where('user_id', $id)->whereIn('id', $med_tasks_ids)->count();
        $hard_solved_tasks = SolvedTask::where('user_id', $id)->whereIn('id', $hard_tasks_ids)->count();
        $warrior_solved_tasks = SolvedTask::where('user_id', $id)->whereIn('id', $warrior_tasks_ids)->count();
        $user['solved_challenges'] = [
            ['level' => 1, 'num' => $easy_solved_tasks],
            ['level' => 2, 'num' => $med_solved_tasks],
            ['level' => 3, 'num' => $hard_solved_tasks],
            ['level' => 4, 'num' => $warrior_solved_tasks]
        ];

        $user['acheivments'] = Achievement::where('points', '<=', $user->points)->get();

        if ($user->team) {
            $team_members = TeamMember::where('team_id', $user->team->id)->with('user')->get();
            $user->team['members'] = $team_members;
        }

        $topUsers = User::orderBy('points', 'DESC')->get()->toArray();
        $topUsersIndexes = array_column($topUsers, 'id');
        $user['rank'] = array_search($id, $topUsersIndexes) + 1;


        return $user;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show()
    {

        $id = Auth::id();
        $user = $this->user($id);

        return $this->respond('User found', 200, $user);
    }

    /**
     * get the achivments.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function userAchievements()
    {
        $user_id = Auth::id();
        $user = $this->user($user_id);

        $achievments = Achievement::where('points', '<=', $user->points)->get();

        return $this->respond('All User Achievments', 200, $achievments);
    }

    /**
     * get the courses.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function userCourses()
    {
        $user_id = Auth::id();

        $userCourses = CourseEnrollment::where('user_id', $user_id)->get();
        foreach ($userCourses as $course) {
            $course['course_data'] = Course::query()
            ->with(['lessons' => function ($query) {$query->select('id', 'title', 'course_id', 'duration', 'url', 'description');}])
            ->with(['category' => function ($query) {$query->select('id', 'title');}])
            ->find($course->course_id);
        }

        return $this->respond('All User Courses', 200, $userCourses);
    }

    /**
     * get the user solved tasks.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function userTasksAchievment()
    {
        $user_id = Auth::id();

        $categories = Category::all();
        $userAchievments = [];

        foreach($categories AS $category){
            $query = Task::where('category', $category->id);
            $numberOfTasks = $query->count();
            $tasksIds = $query->pluck('id')->toArray();

            $numberOfSbmittedTasks = SolvedTask::whereIn('task_id', $tasksIds)
                                                ->where('user_id', $user_id)
                                                ->count();

            // $userAchievments[$category->title] = ['total' => $numberOfTasks, 'solved' => $numberOfSbmittedTasks];
            $userAchievments[] = ['category_id' => $category->id, 'category_title' => $category->title, 'total' => $numberOfTasks, 'solved' => $numberOfSbmittedTasks];
        }


        return $this->respond('Number of Solved Tasks With Number of All tasks in each Category', 200, $userAchievments);
    }

    /**
     * get the courses.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function userTasks()
    {
        $user_id = Auth::id();

        $categories = Category::with('tasks')->get(['id', 'title']);

        foreach ($categories as $category) {
            $tasks_ids = array_column($category->tasks->toArray(), 'id');
            $solvedTasks = SolvedTask::with('task')
                ->where('user_id', $user_id)
                ->whereIn('task_id', $tasks_ids)
                ->get();

            $category['total'] = count($category->tasks);
            $category['solved_tasks_number'] = count($solvedTasks);
            $category['solved_tasks'] = $solvedTasks;

            unset($category->id);
            unset($category->tasks);
        }

        return $this->respond('All Categories With Tasks and User Solved Tasks', 200, $categories);
    }

    /**
     * get the teams.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function userTeams()
    {
        $user_id = Auth::id();

        $ids = TeamMember::where('user_id', $user_id)->pluck('team_id');
        $teams = collect($ids)->map(fn ($id) => Team::find($id));

        return $this->respond('All User Teams', 200, $teams);
    }

    /**
     * get the teams.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function userAdminTeams()
    {
        $user_id = Auth::id();

        $teams = Team::where('admin_id', $user_id)->get();

        return $this->respond('All User Teams', 200, $teams);
    }

}
