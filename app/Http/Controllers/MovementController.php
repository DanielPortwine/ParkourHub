<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateMovement;
use App\Http\Requests\LinkMovements;
use App\Http\Requests\UpdateMovement;
use App\Movement;
use App\MovementCategory;
use App\Report;
use App\Spot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MovementController extends Controller
{
    public function listing(Request $request)
    {
        $sort = ['created_at', 'desc'];
        if (!empty($request['sort'])) {
            $fieldMapping = [
                'date' => 'created_at',
            ];
            $sortParams = explode('_', $request['sort']);
            $sort = [$fieldMapping[$sortParams[0]], $sortParams[1]];
        }

        $movements = Movement::withCount('spots')
            ->dateBetween([
                'from' => $request['date_from'] ?? null,
                'to' => $request['date_to'] ?? null
            ])
            ->category($request['category'] ?? null)
            ->orderBy($sort[0], $sort[1])
            ->paginate(20);

        return view('content_listings', [
            'title' => 'Movements',
            'content' => $movements,
            'component' => 'movement',
        ]);
    }

    public function view($id, $tab = null)
    {
        $movement = Movement::with(['spots', 'category'])->where('id', $id)->first();
        $spots = null;
        $progressions = null;
        $advancements = null;
        $progressionID = null;
        $advancementID = null;
        if (!empty($request['spots']) && ($tab == null || $tab === 'spots')) {
            $spots = $movement->spots()->paginate(20, ['*'], 'spots')->fragment('content');
        } else if ($tab == null || $tab === 'spots') {
            $spots = $movement->spots()->limit(4)->get();
        }
        if (!empty($request['progressions']) && $tab === 'progressions') {
            $progressions = $movement->progressions()->paginate(20, ['*'], 'progressions')->fragment('content');
        } else if ($tab === 'progressions') {
            $progressions = $movement->progressions()->limit(4)->get();
        }
        if (!empty($request['advancements']) && $tab === 'advancements') {
            $advancements = $movement->advancements()->paginate(20, ['*'], 'advancements')->fragment('content');
        } else if ($tab === 'advancements') {
            $advancements = $movement->advancements()->limit(4)->get();
        }
        switch ($tab) {
            case 'progressions':
                $advancementID = $movement->id;
                break;
            case 'advancements':
                $progressionID = $movement->id;
                break;
        }

        return view('movements.view', [
            'movement' => $movement,
            'progressionID' => $progressionID,
            'advancementID' => $advancementID,
            'spots' => $spots,
            'progressions' => $progressions,
            'advancements' => $advancements,
            'tab' => $tab,
        ]);
    }

    public function create(CreateMovement $request)
    {
        $movement = new Movement;
        $movement->category_id = $request['category'];
        $movement->user_id = $movement->creator_id = Auth::id();
        $movement->name = $request['name'];
        $movement->description = $request['description'];
        if (!empty($request['youtube'])) {
            $youtube = explode('t=', str_replace('https://youtu.be/?', '', str_replace('&', '', str_replace('https://www.youtube.com/watch?v=', '', $request['youtube']))));
            $movement->youtube = $youtube[0];
            $movement->youtube_start = $youtube[1] ?? null;
        } else if (!empty($request['video'])) {
            $video = $request->file('video');
            $movement->video = Storage::url($video->store('videos/movements', 'public'));
            $movement->video_type = $video->extension();
        }
        $movement->save();

        if (!empty($request['spot'])) {
            $movement->spots()->attach($request['spot'], ['user_id' => Auth::id()]);
        } else if (!empty($request['progression'])) {
            $movement->advancements()->attach($request['progression'], ['user_id' => Auth::id()]);
        } else if (!empty($request['advancement'])) {
            $movement->progressions()->attach($request['advancement'], ['user_id' => Auth::id()]);
        }

        return back()->with('status', 'Successfully created movement');
    }

    public function edit($id)
    {
        $movement = Movement::where('id', $id)->first();
        if ($movement->user_id != Auth::id()) {
            return redirect()->route('movement_view', $id);
        }

        return view('movements.edit', ['movement' => $movement]);
    }

    public function update(UpdateMovement $request, $id)
    {
        $movement = Movement::where('id', $id)->first();
        if ($movement->user_id != Auth::id()) {
            return redirect()->route('movement_view', $id);
        }
        $movement->name = $request['name'];
        $movement->description = $request['description'];
        if (!empty($request['youtube'])) {
            $youtube = explode('t=', str_replace('https://youtu.be/?', '', str_replace('&', '', str_replace('https://www.youtube.com/watch?v=', '', $request['youtube']))));
            $movement->youtube = $youtube[0];
            $movement->youtube_start = $youtube[1] ?? null;
        } else if (!empty($request['video'])) {
            $video = $request->file('video');
            $movement->video = Storage::url($video->store('videos/movements', 'public'));
            $movement->video_type = $video->extension();
        }
        $movement->save();

        return back()->with('status', 'Successfully updated movement.');
    }

    public function delete($id)
    {
        $movement = Movement::where('id', $id)->first();
        if ($movement->user_id === Auth::id()) {
            $movement->delete();
        }

        return redirect()->route('movement_listing');
    }

    public function report($id)
    {
        $report = new Report;
        $report->reportable_id = $id;
        $report->reportable_type = 'App\Movement';
        $report->user_id = Auth::id();
        $report->save();

        return back()->with('status', 'Successfully reported Movement.');
    }

    public function deleteReported($id)
    {
        Movement::where('id', $id)->first()->forceDelete();

        return redirect()->route('movement_listing')->with('status', 'Successfully deleted Movement and its related content.');
    }

    public function link(LinkMovements $request)
    {
        if ($request['progression'] === $request['advancement']) {
            return back()->with('status', 'You can\'t link a movement with itself');
        }
        $movement = Movement::where('id', $request['progression'])->first();
        if (!empty($movement->advancements()->where('advancement_id', $request['advancement'])->first()) || !empty($movement->progressions()->where('progression_id', $request['progression'])->first())) {
            return back()->with('status', 'Movements already linked');
        }
        $movement->advancements()->attach($request['advancement'], ['user_id' => Auth::id()]);

        return back()->with('status', 'Successfully linked movements');
    }

    public function unlink(LinkMovements $request)
    {
        $movement = Movement::where('id', $request['progression'])->first();
        if (empty($movement->advancements()->where('advancement_id', $request['advancement'])->first())) {
            return back()->with('status', 'These movements aren\'t linked');
        }
        $movement->advancements()->detach($request['advancement']);

        return back()->with('status', 'Successfully unlinked movements');
    }

    public function officialise($id)
    {
        if (Auth::id() !== 1) {
            return back();
        }

        $movement = Movement::where('id', $id)->first();
        $movement->user_id = 1;
        $movement->official = true;
        $movement->save();

        return back()->with('status', 'Successfully officialised movement');
    }

    public function unofficialise($id)
    {
        if (Auth::id() !== 1) {
            return back();
        }

        $movement = Movement::where('id', $id)->first();
        $movement->user_id = $movement->creator_id;
        $movement->official = false;
        $movement->save();

        return back()->with('status', 'Successfully unofficialised movement');
    }

    public function getMovements(Request $request)
    {
        if (!$request->ajax()) {
            return back();
        }

        $spot = Spot::with(['movements'])->where('id', $request['spot'])->first();
        $spotMovements = [];
        if (!empty($spot)) {
            $spotMovements = $spot->movements()->orderBy('category_id')->pluck('movements.id')->toArray();
        }
        $movements = Movement::with(['category'])->whereNotIn('id', $spotMovements)->get();
        $results = [];
        foreach ($movements as $movement) {
            $results[] = [
                'id' => $movement->id,
                'text' => '[' . $movement->category->name . '] ' . $movement->name,
            ];
        }

        if (count($results) > 0) {
            array_unshift($results, ['id' => '0', 'text' => '']);
        }

        return $results;
    }

    public function getMovementCategories(Request $request)
    {
        if (!$request->ajax()) {
            return back();
        }

        $movementCategories = MovementCategory::select(['id', 'name as text'])->get()->toArray();

        return $movementCategories;
    }
}
