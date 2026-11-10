<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProjectResource;
use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $projects = QueryBuilder::for(Project::class)
            ->where('user_id', $request->user()->id)
            ->defaultSort('id')
            ->allowedFilters([
                'name',
                AllowedFilter::exact('budget'),
                AllowedFilter::exact('start_date'),
                AllowedFilter::exact('target_completion_date'),
                AllowedFilter::scope('date_range'),
            ])
            ->allowedSorts(['id', 'name', 'budget', 'start_date', 'target_completion_date'])
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('projects/index', [
            'projects' => ProjectResource::collection($projects),
        ]);
    }

    public function create()
    {
        return Inertia::render('projects/create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'budget' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'target_completion_date' => 'required|date|after:start_date',
        ]);

        $project = new Project($validated);
        $project->user_id = $request->user()->id;
        $project->save();

        return redirect()->route('projects.index')->with('success', 'Project created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Project $project)
    {
        abort_unless($request->user()->id === $project->user_id, 403);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Project $project)
    {
        abort_unless($request->user()->id === $project->user_id, 403);

        return Inertia::render('projects/edit', [
            'project' => $project,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project)
    {
        abort_unless($request->user()->id === $project->user_id, 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'budget' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'target_completion_date' => 'required|date|after:start_date',
        ]);

        $project->update($validated);

        return redirect()->route('projects.index')->with('success', 'Project updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Project $project)
    {
        abort_unless($request->user()->id === $project->user_id, 403);

        $project->delete();

        return redirect()->route('projects.index')->with('success', 'Project deleted successfully.');
    }
}
