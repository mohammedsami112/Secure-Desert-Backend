<?php

namespace App\Http\Controllers;

use App\Models\SolvedTask;
use App\Models\Task;
use App\Models\User;
use App\Traits\CanUploadImage;
use App\Traits\Notify;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Crypt;

class TaskController extends Controller
{
    use CanUploadImage, Notify;

    /**
     * Check if the task exists
     *
     * @param  int  $id
     * @return Team
     */
    public function task($id)
    {
        $task = Task::find($id);

        if (! $task) {
            return $this->fail('Task not found', 404);
        }

        return $task;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // $tasks = Task::all();

        // Edited By Mohammed
        $tasks = Task::query()
        ->with(['taskCategory' => function($query) {$query->select('id', 'title');}])
        ->get();

        // Added By Mohammed
        foreach($tasks as $task) {
            if ($request->admin) {
                $task['answer'] = $task['answer'] ? Crypt::decryptString($task['answer']) : '';
            } else {
                unset($task['answer']);
            }
        }
        return $this->respond('All tasks', 200, $tasks);
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
            'points' => 'required|numeric',
            'answer' => 'required|string',
            'link' => 'string',
            'level' => 'required|integer',
            'description' => 'string',
            'wirte_up_title' => 'string',
            'wirte_up_content' => 'string',
            'wirte_up_thumbnail' => 'image|mimes:png,jpg,jpeg,webp',
            'attachment' => 'file|mimes:pdf,zip,rar,gzip,doc,docx,xls,xlsx,ppt,pptx',
            'category' => 'required|exists:categories,id'
        ]);

        // Upload wirte_up_thumbnail if Exists
        if ($request->hasFile('wirte_up_thumbnail')) {
            $writeUp_filePath = $this->uploadImage($request, 'wirte_up_thumbnail', public_path('uploads/tasks/write_ups'));
        }

        // Upload Attachment if Exists
        if ($request->hasFile('attachment')) {
            $attachmentPath = $this->uploadImage($request, 'attachment', public_path('uploads/tasks/attachments'));
        }

        $validated['wirte_up_thumbnail'] = $writeUp_filePath ?? '';
        $validated['attachment'] = $attachmentPath ?? '';
        $validated['answer'] = Crypt::encryptString($validated['answer']);

        $task = Task::create($validated);

        $this->notifyAll("New Task", $task->title);

        return $this->respond('Task created', 201, $task);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $task = $this->task($id);

        $task->answer = Crypt::decryptString($task->answer);

        return $this->respond('Task found', 200, $task);
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
        $task = $this->task($id);
        $validated = $request->validate(['title' => 'required|string',
            'points' => 'required|numeric',
            'answer' => 'required|string',
            'link' => 'string',
            'level' => 'required|integer',
            'description' => 'string',
            'wirte_up_title' => 'string',
            'wirte_up_content' => 'string',
            'wirte_up_thumbnail' => 'image|mimes:png,jpg,jpeg,webp',
            'attachment' => 'file|mimes:pdf,zip,rar,gzip,doc,docx,xls,xlsx,ppt,pptx',
            'category' => 'required|exists:categories,id'
        ]);


        // Upload wirte_up_thumbnail if Exists
        if ($request->hasFile('wirte_up_thumbnail')) {
            $writeUp_filePath = $this->uploadImage($request, 'wirte_up_thumbnail', public_path('uploads/tasks/write_ups'));
        }

        // Upload Attachment if Exists
        if ($request->hasFile('attachment')) {
            $attachmentPath = $this->uploadImage($request, 'attachment', public_path('uploads/tasks/attachments'));
        }

        $validated['wirte_up_thumbnail'] = $writeUp_filePath ?? $task->wirte_up_thumbnail;
        $validated['attachment'] = $attachmentPath ?? $task->attachment;
        $validated['answer'] = Crypt::encryptString($validated['answer']);

        $task->update($validated);

        return $this->respond('Task updated', 200, $task);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $task = $this->task($id);
        $task->delete();

        return $this->respond('Task deleted', 200);
    }

    /**
     * solve The task.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function solve(Request $request)
    {
        $task = $this->task($request->task_id);
        $validated = $request->validate([
            'task_id' => 'required|integer|exists:tasks,id',
            'user_id' => 'required|integer|exists:users,id',
            'attachment' => 'image|mimes:png,jpg,jpeg,webp,pdf,txt,doc,docx,xls,xlsx,ppt,pptx',
            'content' => 'required|string',
        ]);

        // Upload Attachment if Exists
        if ($request->hasFile('attachment')) {
            $filePath = $this->uploadImage($request, 'attachment', public_path('uploads/solved_tasks'));
        }

        if (Crypt::decryptString($task->answer) != $validated['content']) {
            return $this->fail('Incorrect Answer');
        }

        // Add Task Points To User
        $user = User::find($validated['user_id']);
        $user->points = $user->points + $task->points;
        $user->save();

        $solve = SolvedTask::create($validated + ['attachment' => $filePath ?? '']);

        return $this->respond('Task Submitted', 200, $solve);
    }
}
