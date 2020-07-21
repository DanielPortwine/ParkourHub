<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateMovement;
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
        if (!empty($request['spots']) && ($tab == null || $tab === 'spots')) {
            $spots = $movement->spots()->paginate(20, ['*'], 'spots')->fragment('content');
        } else if ($tab == null || $tab === 'reviews') {
            $spots = $movement->spots()->limit(4)->get();
        }

        return view('movements.view', [
            'movement' => $movement,
            'spots' => $spots,
            'tab' => $tab,
        ]);
    }

    public function create(CreateMovement $request)
    {
        $movement = new Movement;
        $movement->category_id = $request['category'];
        $movement->user_id = Auth::id();
        $movement->name = $request['name'];
        $movement->description = $request['description'];
        if (!empty($request['youtube'])){
            $youtube = explode('t=', str_replace('https://youtu.be/?', '', str_replace('&', '', str_replace('https://www.youtube.com/watch?v=', '', $request['youtube']))));
            $movement->youtube = $youtube[0];
            $movement->youtube_start = $youtube[1] ?? null;
        } else if (!empty($request['video'])) {
            $video = $request->file('video');
            $movement->video = Storage::url($video->store('videos/movements', 'public'));
            $movement->video_type = $video->extension();
        }
        $movement->save();

        $movement->spots()->attach($request['spot'], ['user_id' => Auth::id()]);

        return redirect()->back()->with('status', 'Successfully created movement');
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
        if (!empty($request['youtube'])){
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
