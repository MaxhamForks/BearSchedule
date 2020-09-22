<?php

namespace App\Http\Controllers\Timeline;

use App\Helper\Handlebars;
use App\Helper\TimelineHelper;
use App\Http\Controllers\Controller;
use App\Http\Services\Settings\Account;
use App\Http\Services\Timeline\Timeline;
use App\Models\Project;
use App\Models\ProjectOption;
use App\Models\Timeline\Group;
use App\Models\Timeline\Item;
use App\Models\Timeline\ItemLink;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;

class TimelineAjaxController extends Controller
{
    /**
     * @var Timeline $logicClass
     */
    protected $logicClass = Timeline::class;

    public function destroyGroup(Request $request)
    {
        if (!$request->has('id')) {
            return response()->ajax(null, 'id not set.', 400);
        }
        if (!Group::find($request->get('id'))->delete()) {
            return response()->ajax(null, 'Delete not possible', 400);
        }
        return response()->ajax(null, 'Delete successful', 200);
    }

    public function destroyItem(Request $request)
    {
        if (!$request->has('id')) {
            return response()->ajax(null, 'id not set.', 400);
        }
        if (!Item::find($request->get('id'))->delete()) {
            return response()->ajax(null, 'Delete not possible', 400);
        }
        return response()->ajax(null, 'Delete successful', 200);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getData(Request $request)
    {
        if (!$request->has('project')) {
            return response()->ajax(null, 'Id not set', 400);
        }
        $project_id = $request->get('project');
        $options = $this->logicClass->getOptions($project_id);

        return response()->timeline([
            'groups' => $this->logicClass->getGroups($project_id),
            'items' => $this->logicClass->getItems($project_id),
            'options' => $options,
        ], 200);
    }

    public function getShareLink(Request $request)
    {
        if (!$request->has('project')) {
            return response()->ajax(null, 'Id not set', 400);
        }
        $project = $this->logicClass->getShareLink(
            auth()->user(),
            $request->get('project')
            );
        if($project === null) {
            return response()->ajax(null, 'Unknown Error', 400);
        }
        return response()->ajax($project, 'Success', 200);
    }

    public function deleteShareLink(Request $request)
    {
        if (!$request->has('project')) {
            return response()->ajax(null, 'Id not set', 400);
        }
        $project_id = $request->get('project');
        $project = auth()->user()->projects()->find($project_id);
        if($project === null) {
            return response()->ajax(null, 'Unknown Error', 400);
        }
        $project->share = null;
        $project->save();
        return response()->ajax(null, 'Success', 200);

    }



    public function setGroup(Request $request)
    {
        if (!$request->has('title') || empty($request->get('title'))) {
            return response()->ajax(null, 'Title not set.', 400);
        }
        if (!$request->has('project_id')) {
            return response()->ajax(null, 'Id not set', 400);
        }
        if (!$request->has('id') || empty($request->get('id'))) {
            $group = new Group;
            $group->visible = true;
        } else {
            $group = Group::find($request->get('id'));
        }

        $group = $this->fillModelFillableByRequest($group, $request);

        if ($group->save()) {
            return response()->ajax($group, 'Saved successfully', 200);
        }
        return response()->ajax(null, 'Error can not save.', 400);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function setItem(Request $request)
    {
        $validatedData = $request->validate([
            'project_id' => 'required|int',
            'title' => 'required|min:3',
            'group' => 'required',
            'start' => 'required|min:3',
        ]);
        if ($request->get('start') === 'Invalid Date'
        ) {
            return response()->ajax(null, 'Start not set.', 422);
        }

        if (!$request->has('id') || empty($request->get('id'))) {
            $item = new Item;
        } else {
            $item = Item::find($request->get('id'));
        }
        $item = $this->fillModelFillableByRequest($item, $request, ['start', 'end']);

        if ($request->has('color')) {
            if( $request->get('color')['id'] == 'default') {
                $item->style = $this->logicClass->getStyle(null);
            }
            else if(isset($request->get('color')['style'])) {
                $item->style = $this->logicClass->getStyle($request->get('color')['style']);
            }
        } else {
            $item->style = $this->logicClass->getStyle(null);
        }

        if ($item->save()) {
            if ($request->has('links')) {
                $this->logicClass->saveLinks($request->get('links'), $item->id);
            }
            $responseItem = Item::with('links')->find($item->id);
            return response()->ajax($responseItem, 'Saved successfully', 200);
        }
        return response()->ajax(null, 'Error can not save.', 400);

    }

    private function convertToDateTime(string $string): Carbon
    {
        $stringParts = explode(' (', $string);
        return Carbon::parse($stringParts[0]);
    }

    private function fillModelFillableByRequest($model, Request $request, $dateTimeCasts = [])
    {
        foreach ($model->getFillable() as $fillable) {
            if ($request->has($fillable) && !empty($request->get($fillable))) {
                if (in_array($fillable, $dateTimeCasts)) {
                    $model->{$fillable} = $this->convertToDateTime($request->get($fillable));
                } else {
                    $model->{$fillable} = $request->get($fillable);
                }
            }
        }
        return $model;
    }

}
