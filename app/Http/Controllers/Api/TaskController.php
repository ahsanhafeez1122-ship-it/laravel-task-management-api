<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource (all tasks for one project).
     */
    public function index(Request $request, string $projectId)
    {
        $project = $request->user()->projects()->findOrFail($projectId);

        return $project->tasks;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, string $projectId)
    {
        $project = $request->user()->projects()->findOrFail($projectId);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'in:pending,in_progress,completed',
            'priority' => 'in:low,medium,high',
            'due_date' => 'nullable|date',
        ]);

        $task = $project->tasks()->create($validated);

        return response()->json($task, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        return $this->findOwnedTask($request, $id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $task = $this->findOwnedTask($request, $id);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|in:pending,in_progress,completed',
            'priority' => 'sometimes|in:low,medium,high',
            'due_date' => 'nullable|date',
        ]);

        $task->update($validated);

        return response()->json($task);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $this->findOwnedTask($request, $id)->delete();

        return response()->json(null, 204);
    }

    /**
     * A task only belongs to the current user through its project,
     * so ownership is checked via that relationship before returning it.
     */
    private function findOwnedTask(Request $request, string $id): Task
    {
        return Task::whereHas('project', function ($query) use ($request) {
            $query->where('user_id', $request->user()->id);
        })->findOrFail($id);
    }
}
