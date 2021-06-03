<?php

namespace App\Http\Controllers\Api;

use App\Models\Project;
use App\Models\Timeline\Group;
use App\Models\Timeline\Item;
use Ramsey\Uuid\Uuid;

class SyncController
{
    private \Illuminate\Support\Collection $groups;
    private \Illuminate\Support\Collection $items;

    public function import()
    {
        $project = Project::where('external_id', request()->get('external_id'))->firstOrNew();
        $project->name = request()->get('title');
        $project->share = Uuid::uuid4();
        $project->external_id = request()->get('external_id');
        $project->save();

        $project->attachUniqueUsers(collect(auth()->user()->id), ['role' => 'ADMIN']);
        $project->refresh();

        $this->groups = collect();
        $this->items = collect();

        collect(request()->get('groups'))->each(function ($groupData) use ($project) {


            $group = Group::where('external_id', $groupData['external_id'])->firstOrNew();
            $group->title = $groupData['title'];
            $group->content = $groupData['content'];
            $group->show_share = $groupData['show_share'];
            $group->order = $groupData['order'];
            $group->setAttribute('visible', $groupData['order']);
            $group->external_id = $groupData['external_id'];
            $group->project_id = $project->id;
            $group->save();
            $this->groups->add($group);

            if (isset($groupData['items']) && is_array($groupData['items'])) {
                collect($groupData['items'])->each(function ($itemData) use ($group, $project) {

                    $item = Item::where('external_id', $itemData['external_id'])->firstOrNew();
                    $item->title = $itemData['title'];
                    $item->subtitle = $itemData['subtitle'];
                    $item->start = $itemData['start'];
                    $item->end = $itemData['end'];
                    $item->selectable = $itemData['selectable'];
                    $item->is_series = $itemData['is_series'];
                    $item->type = $itemData['type'];
                    $item->status = $itemData['status'];
                    $item->external_id = $itemData['external_id'];
                    $item->group = $group->id;
                    $item->project_id = $project->id;
                    $item->save();

                    $this->items->add($item);
                });
            }
        });

        return response()->json([
            'project' => $project,
            'groups' => $this->groups,
            'items' => $this->items,
        ]);
    }
}
