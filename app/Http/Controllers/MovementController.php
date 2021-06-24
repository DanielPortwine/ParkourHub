<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\Follower;
use App\Http\Requests\CreateMovement;
use App\Http\Requests\LinkEquipment;
use App\Http\Requests\LinkExercise;
use App\Http\Requests\LinkMovements;
use App\Http\Requests\UpdateMovement;
use App\Models\Movement;
use App\Models\MovementCategory;
use App\Models\MovementField;
use App\Models\MovementType;
use App\Models\Report;
use App\Models\Spot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
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

        $movements = Movement::withCount(['spots', 'moves'])
            ->with(['spots', 'moves', 'reports', 'user'])
            ->type($request['type'] ?? null)
            ->dateBetween([
                'from' => $request['date_from'] ?? null,
                'to' => $request['date_to'] ?? null
            ])
            ->category($request['category'] ?? null)
            ->exercise($request['exercise'] ?? null)
            ->equipment($request['equipment'] ?? null)
            ->search($request['search'] ?? false)
            ->orderBy($sort[0], $sort[1])
            ->paginate(20)
            ->appends(request()->query());

        $movementCategories = MovementCategory::get();
        $equipments = Equipment::get();

        return view('content_listings', [
            'title' => 'Movements',
            'content' => $movements,
            'component' => 'movement',
            'create' => true,
            'movementCategories' => $movementCategories,
            'equipments' => $equipments,
        ]);
    }

    public function view($id, $tab = null)
    {
        $movement = Movement::withTrashed()
            ->with([
                'category',
                'fields',
                'reports',
                'spots',
                'equipment',
                'progressions',
                'advancements',
                'exercises',
                'moves',
            ])
            ->where('id', $id)
            ->first();

        if (empty($movement) || ($movement->deleted_at !== null && Auth::id() !== $movement->user_id)) {
            return view('errors.404');
        }

        $spots = null;
        $progressions = null;
        $advancements = null;
        $exercises = null;
        $moves = null;
        $equipment = null;
        $progressionID = null;
        $advancementID = null;
        $history = null;
        $baselineFields = null;
        if (!empty($request['spots']) && (($tab == null && $movement->type_id === 1) || $tab === 'spots')) {
            $spots = $movement->spots()
                ->withCount('views')
                ->with(['reviews', 'reports', 'hits', 'user'])
                ->paginate(20, ['*'], 'spots');
        } else if (($tab == null && $movement->type_id === 1) || $tab === 'spots') {
            $spots = $movement->spots()
                ->withCount('views')
                ->with(['reviews', 'reports', 'hits', 'user'])
                ->limit(4)
                ->get();
        }
        if (!empty($request['equipment']) && (($tab == null && $movement->type_id === 2) || $tab === 'equipment')) {
            $equipment = $movement->equipment()
                ->withCount(['movements'])
                ->with(['movements', 'reports', 'user'])
                ->paginate(20, ['*'], 'equipment');
        } else if (($tab == null && $movement->type_id === 2) || $tab === 'equipment') {
            $equipment = $movement->equipment()
                ->withCount(['movements'])
                ->with(['movements', 'reports', 'user'])
                ->limit(4)
                ->get();
        }
        if (!empty($request['progressions']) && $tab === 'progressions') {
            $progressions = $movement->progressions()->paginate(20, ['*'], 'progressions');
        } else if ($tab === 'progressions') {
            $progressions = $movement->progressions()->limit(4)->get();
        }
        if (!empty($request['advancements']) && $tab === 'advancements') {
            $advancements = $movement->advancements()->paginate(20, ['*'], 'advancements');
        } else if ($tab === 'advancements') {
            $advancements = $movement->advancements()->limit(4)->get();
        }
        if (!empty($request['exercises']) && $tab === 'exercises') {
            $exercises = $movement->exercises()->paginate(20, ['*'], 'exercises');
        } else if ($tab === 'exercises') {
            $exercises = $movement->exercises()->limit(4)->get();
        }
        if (!empty($request['moves']) && $tab === 'moves') {
            $moves = $movement->moves()->paginate(20, ['*'], 'moves');
        } else if ($tab === 'moves') {
            $moves = $movement->moves()->limit(4)->get();
        }
        if (!empty($request['history']) && $tab === 'history') {
            $history = $movement->workouts()->where('user_id', Auth::id())->whereNotNull('recorded_workout_id')->orderBy('created_at', 'desc')->paginate(20, ['*'], 'history');
        } else if ($tab === 'history') {
            $history = $movement->workouts()->where('user_id', Auth::id())->whereNotNull('recorded_workout_id')->orderBy('created_at', 'desc')->limit(4)->get();
        }
        if ($tab === 'baseline') {
            if (count(Auth::user()->baselineMovementFields()->where('movement_id', $id)->get())) {
                $baselineFields = Auth::user()->baselineMovementFields()->withPivot('value', 'movement_id')->where('movement_id', $id)->get();
            } else {
                $baselineFields = $movement->fields;
            }
        }
        $linkableEquipment = null;
        $linkableMovements = null;
        $movementCategories = null;
        $movementFields = null;
        switch ($tab) {
            case null:
            case 'equipment':
                $linkableEquipment = Equipment::get();
                break;
            case 'progressions':
                $advancementID = $movement->id;
                $linkableMovements = Movement::where('id', '!=', $id)
                    ->whereNotIn('id', array_merge($movement->progressions()->pluck('movements.id')->toArray(), $movement->advancements()->pluck('movements.id')->toArray()))
                    ->where('type_id', $movement->type_id)
                    ->orderBy('category_id')
                    ->get();
                $movementCategories = Cache::remember('movement_categories_' . $movement->type_id, 86400, function() use($movement) {
                    return MovementCategory::where('type_id', $movement->type_id)->get();
                });
                $movementFields = Cache::remember('movement_fields', 86400, function() {
                    return MovementField::get();
                });
                break;
            case 'advancements':
                $progressionID = $movement->id;
                $linkableMovements = Movement::where('id', '!=', $id)
                    ->whereNotIn('id', array_merge($movement->progressions()->pluck('movements.id')->toArray(), $movement->advancements()->pluck('movements.id')->toArray()))
                    ->where('type_id', $movement->type_id)
                    ->orderBy('category_id')
                    ->get();
                $movementCategories = Cache::remember('movement_categories_' . $movement->type_id, 86400, function() use($movement) {
                    return MovementCategory::where('type_id', $movement->type_id)->get();
                });
                $movementFields = Cache::remember('movement_fields', 86400, function() {
                    return MovementField::get();
                });
                break;
            case 'exercises':
                $linkableMovements = Movement::with(['type'])
                    ->where('id', '!=', $id)
                    ->whereNotIn('id', $movement->exercises()->pluck('movements.id')->toArray())
                    ->whereHas('type', function($q) {
                        return $q->where('name', 'Exercise');
                    })
                    ->orderBy('category_id')
                    ->get();
                $movementCategories = Cache::remember('movement_categories_' . $movement->type_id, 86400, function() use($movement) {
                    return MovementCategory::where('type_id', $movement->type_id)->get();
                });
                $movementFields = Cache::remember('movement_fields', 86400, function() {
                    return MovementField::get();
                });
                break;
            case 'moves':
                $linkableMovements = Movement::with(['type'])
                    ->where('id', '!=', $id)
                    ->whereNotIn('id', $movement->moves()->pluck('movements.id')->toArray())
                    ->whereHas('type', function($q) {
                        return $q->where('name', 'Move');
                    })
                    ->orderBy('category_id')
                    ->get();
                $movementCategories = Cache::remember('movement_categories_' . $movement->type_id, 86400, function() use($movement) {
                    return MovementCategory::where('type_id', $movement->type_id)->get();
                });
                $movementFields = Cache::remember('movement_fields', 86400, function() {
                    return MovementField::get();
                });
                break;
        }
        $linkType = '';
        switch($movement->type_id) {
            case 1:
                $linkType = 'move';
                break;
            case 2:
                $linkType = 'exercise';
                break;
        }

        return view('movements.view', [
            'originalMovement' => $movement,
            'linkType' => $linkType,
            'progressionID' => $progressionID,
            'advancementID' => $advancementID,
            'spots' => $spots,
            'progressions' => $progressions,
            'advancements' => $advancements,
            'exercises' => $exercises,
            'moves' => $moves,
            'equipments' => $equipment,
            'history' => $history,
            'baselineFields' => $baselineFields,
            'tab' => $tab,
            'linkableEquipment' => $linkableEquipment,
            'linkableMovements' => $linkableMovements,
            'movementCategories' => $movementCategories,
            'movementFields' => $movementFields,
        ]);
    }

    public function create()
    {
        $movementTypes = MovementType::with(['categories'])->get();
        $movementFields = MovementField::get();

        return view('movements.create', [
            'movementTypes' => $movementTypes,
            'movementFields' => $movementFields,
        ]);
    }

    public function store(CreateMovement $request)
    {
        $movement = new Movement;
        $movement->category_id = $request['category'];
        $movement->user_id = $movement->creator_id = Auth::id();
        $movement->type_id = $request['type'];
        $movement->name = $request['name'];
        $movement->description = $request['description'];
        $movement->visibility = $request['visibility'] ?: 'private';
        if (!empty($request['youtube'])) {
            $youtube = explode('t=', str_replace(['https://youtu.be/', 'https://www.youtube.com/watch?v=', '&', '?'], '', $request['youtube']));
            $movement->youtube = $youtube[0];
            $movement->youtube_start = $youtube[1] ?? null;
        } else if (!empty($request['video'])) {
            $video = $request->file('video');
            $movement->video = Storage::url($video->store('videos/movements', 'public'));
            $movement->video_type = $video->extension();
        }
        $movement->save();

        foreach ($request['fields'] as $field) {
            $movement->fields()->attach($field);
        }

        if (!empty($request['spot'])) {
            $movement->spots()->attach($request['spot'], ['user_id' => Auth::id()]);
        } else if (!empty($request['progression'])) {
            $movement->advancements()->attach($request['progression'], ['user_id' => Auth::id()]);
        } else if (!empty($request['advancement'])) {
            $movement->progressions()->attach($request['advancement'], ['user_id' => Auth::id()]);
        } else if(!empty($request['exercise'])) {
            $movement->exercises()->attach($request['exercise'], ['user_id' => Auth::id()]);
        } else if (!empty($request['move'])) {
            $movement->moves()->attach($request['move'], ['user_id' => Auth::id()]);
        }

        return redirect()->route('movement_view', $movement->id)->with('status', 'Successfully created movement');
    }

    public function edit($id)
    {
        $movement = Movement::with(['fields'])->where('id', $id)->first();
        if ($movement->user_id != Auth::id()) {
            return redirect()->route('movement_view', $id);
        }

        $movementFields = MovementField::get();

        return view('movements.edit', [
            'movement' => $movement,
            'movementFields' => $movementFields,
        ]);
    }

    public function update(UpdateMovement $request, $id)
    {
        if (!empty($request['delete'])) {
            return $this->delete($id, $request['redirect']);
        }

        $movement = Movement::with(['fields'])->where('id', $id)->first();
        if ($movement->user_id != Auth::id()) {
            return redirect()->route('movement_view', $id);
        }
        $movement->name = $request['name'];
        $movement->description = $request['description'];
        $movement->visibility = $request['visibility'] ?: 'private';
        if (!empty($request['youtube'])) {
            $youtube = explode('t=', str_replace(['https://youtu.be/', 'https://www.youtube.com/watch?v=', '&', '?'], '', $request['youtube']));
            $movement->youtube = $youtube[0];
            $movement->youtube_start = $youtube[1] ?? null;
            $movement->video = null;
        } else if (!empty($request['video'])) {
            $video = $request->file('video');
            $movement->video = Storage::url($video->store('videos/movements', 'public'));
            $movement->video_type = $video->extension();
            $movement->youtube = null;
            $movement->youtube_start = null;
        }
        $movement->save();

        $movementFields = $movement->fields()->pluck('movement_fields.id')->toArray();
        foreach ($request['fields'] as $field) {
            if (!in_array($field, $movementFields)) {
                $movement->fields()->attach($field);
            }
        }
        foreach ($movementFields as $movementField) {
            if (!in_array($movementField, $request['fields'])) {
                $movement->fields()->detach($movementField);
            }
        }

        return back()->with([
            'status' => 'Successfully updated movement',
            'redirect' => $request['redirect'],
        ]);
    }

    public function delete($id, $redirect = null)
    {
        $movement = Movement::where('id', $id)->first();
        if ($movement->user_id === Auth::id()) {
            $movement->delete();
        }

        if (!empty($redirect)) {
            return redirect($redirect)->with('status', 'Successfully deleted movement');
        }

        return back()->with('status', 'Successfully deleted movement');
    }

    public function recover(Request $request, $id)
    {
        $movement = Movement::onlyTrashed()->where('id', $id)->first();

        if (empty($movement) || $movement->user_id !== Auth::id()) {
            return back();
        }

        $movement->restore();

        return back()->with('status', 'Successfully recovered movement.');
    }

    public function remove(Request $request, $id)
    {
        $movement = Movement::withTrashed()->where('id', $id)->first();

        if ($movement->user_id !== Auth::id() && !Auth::user()->hasPermissionTo('remove content')) {
            return back();
        }

        if (!empty($movement->video)) {
            Storage::disk('public')->delete(str_replace('storage/', '', $movement->video));
        }

        $movement->forceDelete();

        return back()->with('status', 'Successfully removed movement forever.');
    }

    public function report(Movement $movement)
    {
        $movement->report();

        return back()->with('status', 'Successfully reported movement');
    }

    public function discardReports(Movement $movement)
    {
        $movement->discardReports();

        return back()->with('status', 'Successfully discarded reports against this content');
    }

    public function linkProgression(LinkMovements $request)
    {
        if ($request['progression'] === $request['advancement']) {
            return back()->with('status', 'You can\'t link a movement with itself');
        }
        $movement = Movement::with(['advancements', 'progressions'])->where('id', $request['progression'])->first();
        if (!empty($movement->advancements()->where('advancement_id', $request['advancement'])->first()) || !empty($movement->progressions()->where('progression_id', $request['progression'])->first())) {
            return back()->with('status', 'Movements already linked');
        }
        $movement->advancements()->attach($request['advancement'], ['user_id' => Auth::id()]);

        return back()->with('status', 'Successfully linked movements');
    }

    public function unlinkProgression(LinkMovements $request)
    {
        $movement = Movement::with(['advancements'])->where('id', $request['progression'])->first();
        if (empty($movement->advancements()->where('advancement_id', $request['advancement'])->first())) {
            return back()->with('status', 'These movements aren\'t linked');
        }
        $movement->advancements()->detach($request['advancement']);

        return back()->with('status', 'Successfully unlinked movements');
    }

    public function linkExercise(LinkExercise $request)
    {
        if ($request['move'] === $request['exercise']) {
            return back()->with('status', 'You can\'t link a movement with itself');
        }
        $move = Movement::with(['exercises', 'moves'])->where('id', $request['move'])->first();
        if (!empty($move->exercises()->where('exercise_id', $request['exercise'])->first()) || !empty($move->moves()->where('move_id', $request['move'])->first())) {
            return back()->with('status', 'Movements already linked');
        }
        $move->exercises()->attach($request['exercise'], ['user_id' => Auth::id()]);

        return back()->with('status', 'Successfully linked exercise to movement');
    }

    public function unlinkExercise(LinkExercise $request)
    {
        $move = Movement::with(['exercises'])->where('id', $request['move'])->first();
        if (empty($move->exercises()->where('exercise_id', $request['exercise'])->first())) {
            return back()->with('status', 'These movements aren\'t linked');
        }
        $move->exercises()->detach($request['exercise']);

        return back()->with('status', 'Successfully unlinked exercise from movement');
    }

    public function linkEquipment(LinkEquipment $request)
    {
        $movement = Movement::with(['equipment'])->where('id', $request['movement'])->first();
        if (!empty($movement->equipment()->where('equipment_id', $request['equipment'])->first())) {
            return back()->with('status', 'Exercise and equipment already linked');
        }
        $movement->equipment()->attach($request['equipment'], ['user_id' => Auth::id()]);

        return back()->with('status', 'Successfully linked equipment to exercise');
    }

    public function unlinkEquipment(LinkEquipment $request)
    {
        $movement = Movement::with(['equipment'])->where('id', $request['movement'])->first();
        if (empty($movement->equipment()->where('equipment_id', $request['equipment'])->first())) {
            return back()->with('status', 'This movement and equipment aren\'t linked');
        }
        $movement->equipment()->detach($request['equipment']);

        return back()->with('status', 'Successfully unlinked equipment from exercise');
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

    public function setMovementBaseline(Request $request)
    {
        $baselineFields = Auth::user()->baselineMovementFields()->withPivot('movement_id')->where('movement_id', $request['movement'])->get();

        if (count($baselineFields)) {
            foreach ($baselineFields as $baselineField) {
                $baselineField->pivot->value = $request['fields'][$baselineField->id];
                $baselineField->save();
            }
        } else {
            $baselineFields = Auth::user()->baselineMovementFields();
            foreach ($request['fields'] as $id => $value) {
                $baselineFields->attach($id, ['value' => $value, 'movement_id' => $request['movement']]);
            }
        }

        return back()->with('status', 'Successfully set your baseline for this movement');
    }
}
