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
use App\Scopes\LinkVisibilityScope;
use App\Scopes\VisibilityScope;
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
                'updated' => 'updated_at',
            ];
            $sortParams = explode('_', $request['sort']);
            $sort = [$fieldMapping[$sortParams[0]], $sortParams[1]];
        }

        $movements = Movement::withCount(['spots', 'moves'])
            ->with(['spots', 'moves', 'reports', 'user'])
            ->type($request['movementType'] ?? null)
            ->dateBetween([
                'from' => $request['date_from'] ?? null,
                'to' => $request['date_to'] ?? null
            ])
            ->category($request['category'] ?? null)
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

    public function view(Request $request, $id, $tab = 'comments')
    {
        $movement = Movement::withTrashed()
            ->withoutGlobalScope(VisibilityScope::class)
            ->withGlobalScope('linkVisibility', LinkVisibilityScope::class)
            ->with([
                'category',
                'fields',
                'reports',
                'comments',
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
            abort(404);
        }

        switch ($tab) {
            case 'comments':
                $comments = $movement->comments()
                    ->with(['reports', 'user'])
                    ->orderByDesc('created_at')
                    ->paginate(20, ['*']);
                break;
            case 'spots':
                $spots = $movement->spots()
                    ->withCount('views')
                    ->with(['reviews', 'reports', 'hits', 'user'])
                    ->paginate(20, ['*']);
                break;
            case 'equipment':
                $equipment = $movement->equipment()
                    ->withCount(['movements'])
                    ->with(['movements', 'reports', 'user'])
                    ->paginate(20, ['*']);
                break;
            case 'progressions':
                $progressions = $movement->progressions()->paginate(20, ['*']);
                break;
            case 'advancements':
                $advancements = $movement->advancements()->paginate(20, ['*']);
                break;
            case 'exercises':
                $exercises = $movement->exercises()->paginate(20, ['*']);
                break;
            case 'moves':
                $moves = $movement->moves()->paginate(20, ['*']);
                break;
            case 'history':
                $history = $movement->workouts()
                    ->where('user_id', Auth::id())
                    ->whereNotNull('recorded_workout_id')
                    ->orderBy('created_at', 'desc')
                    ->paginate(20, ['*']);
            /*case 'baseline':
                if (count(Auth::user()->baselineMovementFields()->where('movement_id', $id)->get())) {
                    $baselineFields = Auth::user()->baselineMovementFields()->withPivot('value', 'movement_id')->where('movement_id', $id)->get();
                } else {
                    $baselineFields = $movement->fields;
                }
                break;*/
        }

        switch ($tab) {
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
        $linkType = strtolower($movement->type->name);

        return view('movements.view', [
            'originalMovement' => $movement,
            'linkType' => $linkType,
            'progressionID' => $progressionID ?? null,
            'advancementID' => $advancementID ?? null,
            'comments' => $comments ?? null,
            'spots' => $spots ?? null,
            'progressions' => $progressions ?? null,
            'advancements' => $advancements ?? null,
            'exercises' => $exercises ?? null,
            'moves' => $moves ?? null,
            'equipments' => $equipment ?? null,
            'history' => $history ?? null,
            //'baselineFields' => $baselineFields ?? null,
            'tab' => $tab,
            'linkableEquipment' => $linkableEquipment ?? null,
            'linkableMovements' => $linkableMovements ?? null,
            'movementCategories' => $movementCategories ?? null,
            'movementFields' => $movementFields ?? null,
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
        $movement->link_access = $request['link_access'] ?? false;
        if (!empty($request['youtube'])) {
            $youtube = explode('t=', str_replace(['https://youtu.be/', 'https://www.youtube.com/watch?v=', '&', '?'], '', $request['youtube']));
            $movement->youtube = $youtube[0];
            $movement->youtube_start = $youtube[1] ?? null;
        } else if (!empty($request['video'])) {
            $video = $request->file('video');
            $movement->video = Storage::url($video->store('videos/movements', 'public'));
            $movement->video_type = $video->extension();
        }
        if (!empty($request['thumbnail'])) {
            $movement->thumbnail = Storage::url($request->file('thumbnail')->store('images/movements', 'public'));
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
        $movement->link_access = $request['link_access'] ?? false;
        if (!empty($request['youtube'])) {
            Storage::disk('public')->delete(str_replace('storage/', '', $movement->video));
            $youtube = explode('t=', str_replace(['https://youtu.be/', 'https://www.youtube.com/watch?v=', '&', '?'], '', $request['youtube']));
            $movement->youtube = $youtube[0];
            $movement->youtube_start = $youtube[1] ?? null;
            $movement->video = null;
        } else if (!empty($request['video'])) {
            Storage::disk('public')->delete(str_replace('storage/', '', $movement->video));
            $video = $request->file('video');
            $movement->video = Storage::url($video->store('videos/movements', 'public'));
            $movement->video_type = $video->extension();
            $movement->youtube = null;
            $movement->youtube_start = null;
        }
        if (!empty($request['thumbnail'])) {
            Storage::disk('public')->delete(str_replace('storage/', '', $movement->thumbnail));
            $movement->thumbnail = Storage::url($request->file('thumbnail')->store('images/movements', 'public'));
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
        $movement = Movement::withoutGlobalScope(VisibilityScope::class)->where('id', $id)->first();
        if ($movement->user_id === Auth::id()) {
            $movement->delete();
        } else {
            return redirect()->route('movement_view', $movement->id);
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
        $movement = Movement::withTrashed()->withoutGlobalScope(VisibilityScope::class)->where('id', $id)->first();

        if (empty($movement) || ($movement->user_id !== Auth::id() && !Auth::user()->hasPermissionTo('remove content'))) {
            return back();
        }

        if (!empty($movement->video)) {
            Storage::disk('public')->delete(str_replace('storage/', '', $movement->video));
        }
        if (!empty($movement->thumbnail)) {
            Storage::disk('public')->delete(str_replace('storage/', '', $movement->thumbnail));
        }

        $movement->forceDelete();

        return back()->with('status', 'Successfully removed movement forever.');
    }

    public function report($id)
    {
        $movement = Movement::where('id', $id)->first();

        $movement->report();

        return back()->with('status', 'Successfully reported movement');
    }

    public function discardReports($id)
    {
        $movement = Movement::withoutGlobalScope(VisibilityScope::class)->where('id', $id)->first();

        if (empty($movement) || !Auth::user()->hasPermissionTo('manage reports')) {
            return back();
        }

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
        $advancement = $movement->advancements()->withPivot('user_id')->wherePivot('advancement_id', $request['advancement'])->first();
        if ($movement->user_id !== Auth::id() && $advancement->pivot->user_id !== Auth::id() && $advancement->user_id !== Auth::id()) {
            return back();
        }
        if (empty($advancement)) {
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
        $exercise = Movement::where('id', $request['exercise'])->first();
        if (empty($move) || empty($exercise) || $move->type->name === 'Exercise' || $exercise->type->name === 'Move') {
            return back();
        }
        if (!empty($move->exercises()->where('exercise_id', $request['exercise'])->first())) {
            return back()->with('status', 'Movements already linked');
        }
        $move->exercises()->attach($request['exercise'], ['user_id' => Auth::id()]);

        return back()->with('status', 'Successfully linked exercise to movement');
    }

    public function unlinkExercise(LinkExercise $request)
    {
        $move = Movement::with(['exercises'])->where('id', $request['move'])->first();
        $exercise = Movement::where('id', $request['exercise'])->first();
        if (empty($move->exercises()->where('exercise_id', $request['exercise'])->first())) {
            return back()->with('status', 'These movements aren\'t linked');
        }
        if ($move->user_id !== Auth::id() && $move->exercises()->withPivot('user_id')->wherePivot('exercise_id', $request['exercise'])->first()->pivot->user_id !== Auth::id() && $exercise->user_id !== Auth::id()) {
            return back();
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
        $equipment = Equipment::where('id', $request['equipment'])->first();
        if (empty($movement->equipment()->where('equipment_id', $request['equipment'])->first())) {
            return back()->with('status', 'This movement and equipment aren\'t linked');
        }
        if ($movement->user_id !== Auth::id() && $movement->equipment()->withPivot('user_id')->wherePivot('equipment_id', $request['equipment'])->first()->pivot->user_id !== Auth::id() && $equipment->user_id !== Auth::id()) {
            return back();
        }
        $movement->equipment()->detach($request['equipment']);

        return back()->with('status', 'Successfully unlinked equipment from exercise');
    }

    public function officialise($id)
    {
        if (!Auth::user()->hasPermissionTo('officialise')) {
            return back();
        }

        $movement = Movement::where('id', $id)->first();
        $movement->user_id = Auth::id();
        $movement->official = true;
        $movement->save();

        return back()->with('status', 'Successfully officialised movement');
    }

    public function unofficialise($id)
    {
        if (!Auth::user()->hasPermissionTo('officialise')) {
            return back();
        }

        $movement = Movement::where('id', $id)->first();
        $movement->user_id = $movement->creator_id;
        $movement->official = false;
        $movement->save();

        return back()->with('status', 'Successfully unofficialised movement');
    }

    /*public function setMovementBaseline(Request $request)
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
    }*/
}
