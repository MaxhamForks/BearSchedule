<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\ProjectApiRequest;
use App\Models\Project;
use Ramsey\Uuid\Uuid;

class ProjectController
{
    public function store(ProjectApiRequest $request)
    {
        $project = new Project();
        $project->share = Uuid::uuid4();
        $project->name = $request->get('name');
        $project->save();

        $project->users()->attach($request->getApiUser(), ['role' => 'ADMIN']);

        return response('', 201);
    }

    public function update(ProjectApiRequest $request)
    {
        $project = $request->project();
        $project->name = $request->name;
        $project->save();

        return $project;
    }
}
