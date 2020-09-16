<?php

namespace App\Http\Controllers;

use App\Equipment;
use App\Http\Requests\CreateMovement;
use App\Http\Requests\LinkEquipment;
use App\Http\Requests\LinkExercise;
use App\Http\Requests\LinkMovements;
use App\Http\Requests\UpdateMovement;
use App\Movement;
use App\MovementCategory;
use App\MovementField;
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
            ->type($request['type'] ?? null)
            ->category($request['category'] ?? null)
            ->exercise($request['exercise'] ?? null)
            ->equipment($request['equipment'] ?? null)
            ->orderBy($sort[0], $sort[1])
            ->paginate(20);

        return view('content_listings', [
            'title' => 'Movements',
            'content' => $movements,
            'component' => 'movement',
            'create' => true,
        ]);
    }

    public function view($id, $tab = null)
    {
        $movement = Movement::with(['spots', 'exercises', 'equipment', 'category'])->where('id', $id)->first();
        $spots = null;
        $progressions = null;
        $advancements = null;
        $exercises = null;
        $moves = null;
        $equipment = null;
        $progressionID = null;
        $advancementID = null;
        if (!empty($request['spots']) && (($tab == null && $movement->type_id === 1) || $tab === 'spots')) {
            $spots = $movement->spots()->paginate(20, ['*'], 'spots')->fragment('content');
        } else if (($tab == null && $movement->type_id === 1) || $tab === 'spots') {
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
        if (!empty($request['exercises']) && $tab === 'exercises') {
            $exercises = $movement->exercises()->paginate(20, ['*'], 'exercises')->fragment('content');
        } else if ($tab === 'exercises') {
            $exercises = $movement->exercises()->limit(4)->get();
        }
        if (!empty($request['moves']) && $tab === 'moves') {
            $moves = $movement->moves()->paginate(20, ['*'], 'moves')->fragment('content');
        } else if ($tab === 'moves') {
            $moves = $movement->moves()->limit(4)->get();
        }
        if (!empty($request['equipment']) && (($tab == null && $movement->type_id === 2) || $tab === 'equipment')) {
            $equipment = $movement->equipment()->paginate(20, ['*'], 'equipment')->fragment('content');
        } else if (($tab == null && $movement->type_id === 2) || $tab === 'equipment') {
            $equipment = $movement->equipment()->limit(4)->get();
        }
        switch ($tab) {
            case 'progressions':
                $advancementID = $movement->id;
                break;
            case 'advancements':
                $progressionID = $movement->id;
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
            'tab' => $tab,
        ]);
    }

    public function create()
    {
        return view('movements.create');
    }

    public function store(CreateMovement $request)
    {
        $movement = new Movement;
        $movement->category_id = $request['category'];
        $movement->user_id = $movement->creator_id = Auth::id();
        $movement->type_id = $request['type'];
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

        return back()->with('status', 'Successfully created movement');
    }

    public function edit($id)
    {
        $movement = Movement::with(['fields'])->where('id', $id)->first();
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

    public function linkProgression(LinkMovements $request)
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

    public function unlinkProgression(LinkMovements $request)
    {
        $movement = Movement::where('id', $request['progression'])->first();
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
        $move = Movement::where('id', $request['move'])->first();
        if (!empty($move->exercises()->where('exercise_id', $request['exercise'])->first()) || !empty($move->moves()->where('move_id', $request['move'])->first())) {
            return back()->with('status', 'Movements already linked');
        }
        $move->exercises()->attach($request['exercise'], ['user_id' => Auth::id()]);

        return back()->with('status', 'Successfully linked exercise to movement');
    }

    public function unlinkExercise(LinkExercise $request)
    {
        $move = Movement::where('id', $request['move'])->first();
        if (empty($move->exercises()->where('exercise_id', $request['exercise'])->first())) {
            return back()->with('status', 'These movements aren\'t linked');
        }
        $move->exercises()->detach($request['exercise']);

        return back()->with('status', 'Successfully unlinked exercise from movement');
    }

    public function linkEquipment(LinkEquipment $request)
    {
        $movement = Movement::where('id', $request['movement'])->first();
        if (!empty($movement->equipment()->where('equipment_id', $request['equipment'])->first())) {
            return back()->with('status', 'Exercise and equipment already linked');
        }
        $movement->equipment()->attach($request['equipment'], ['user_id' => Auth::id()]);

        return back()->with('status', 'Successfully linked equipment to exercise');
    }

    public function unlinkEquipment(LinkEquipment $request)
    {
        $movement = Movement::where('id', $request['movement'])->first();
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

    public function getMovements(Request $request)
    {
        if (!$request->ajax()) {
            return back();
        }

        $results = [];
        switch ($request['link']) {
            case 'exerciseEquipment':
                $exercise = Movement::with(['equipment'])->where('id', $request['id'])->first();
                $exerciseEquipment = [];
                if (!empty($exercise)) {
                    $exerciseEquipment = $exercise->equipment()->pluck('equipment.id')->toArray();
                }
                $equipments = Equipment::whereNotIn('id', $exerciseEquipment)->get();
                foreach ($equipments as $equipment) {
                    $results[] = [
                        'id' => $equipment->id,
                        'text' => $equipment->name,
                    ];
                }
                break;
            case 'equipmentExercise':
                $equipment = Equipment::with(['movements'])->where('id', $request['id'])->first();
                $equipmentExercise = [];
                if (!empty($equipment)) {
                    $equipmentExercise = $equipment->movements()->pluck('movements.id')->toArray();
                }
                $exercises = Movement::where('type_id', $request['type'])->whereNotIn('id', $equipmentExercise)->get();
                foreach ($exercises as $exercise) {
                    $results[] = [
                        'id' => $exercise->id,
                        'text' => $exercise->name,
                    ];
                }
                break;
            case 'progressionAdvancement':
                $movement = Movement::with(['progressions', 'advancements'])->where('id', $request['id'])->first();
                $moveProgressions = $moveAdvancements = [];
                if (!empty($movement)) {
                    $moveProgressions = $movement->progressions()->orderBy('category_id')->where('type_id', $request['type'])->pluck('movements.id')->toArray();
                    $moveAdvancements = $movement->advancements()->orderBy('category_id')->where('type_id', $request['type'])->pluck('movements.id')->toArray();
                }
                $exercises = Movement::with(['category'])->where('type_id', $request['type'])->where('id', '!=', $request['id'])->whereNotIn('id', array_merge($moveProgressions, $moveAdvancements))->get();
                foreach ($exercises as $exercise) {
                    $results[] = [
                        'id' => $exercise->id,
                        'text' => '[' . $exercise->category->name . '] ' . $exercise->name,
                    ];
                }
                break;
            case 'moveExercise':
                $move = Movement::with(['exercises'])->where('id', $request['id'])->first();
                $moveExercises = [];
                if (!empty($move)) {
                    $moveExercises = $move->exercises()->orderBy('category_id')->where('type_id', 1)->pluck('movements.id')->toArray();
                }
                $exercises = Movement::with(['category'])->where('type_id', 1)->whereNotIn('id', $moveExercises)->get();
                foreach ($exercises as $exercise) {
                    $results[] = [
                        'id' => $exercise->id,
                        'text' => '[' . $exercise->category->name . '] ' . $exercise->name,
                    ];
                }
                break;
            case 'exerciseMove':
                $exercise = Movement::with(['moves'])->where('id', $request['id'])->first();
                $exerciseMoves = [];
                if (!empty($exercise)) {
                    $exerciseMoves = $exercise->moves()->orderBy('category_id')->where('type_id', 1)->pluck('movements.id')->toArray();
                }
                $moves = Movement::with(['category'])->where('type_id', 1)->whereNotIn('id', $exerciseMoves)->get();
                foreach ($moves as $move) {
                    $results[] = [
                        'id' => $move->id,
                        'text' => '[' . $move->category->name . '] ' . $move->name,
                    ];
                }
                break;
            case 'spotMove':
                $spot = Spot::with(['movements'])->where('id', $request['id'])->first();
                $spotMovements = [];
                if (!empty($spot)) {
                    $spotMovements = $spot->movements()->orderBy('category_id')->where('type_id', 1)->pluck('movements.id')->toArray();
                }
                $movements = Movement::with(['category'])->where('type_id', 1)->whereNotIn('id', $spotMovements)->get();
                foreach ($movements as $movement) {
                    $results[] = [
                        'id' => $movement->id,
                        'text' => '[' . $movement->category->name . '] ' . $movement->name,
                    ];
                }
                break;
            case 'AllMovements':
                $movements = Movement::with(['category', 'type'])->get();
                foreach ($movements as $movement) {
                    $results[] = [
                        'id' => $movement->id,
                        'text' => $movement->type->name . ': [' . $movement->category->name . '] ' . $movement->name,
                    ];
                }
                break;
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

        $results = [];
        $movementCategories = MovementCategory::with(['type'])->whereIn('type_id', (array)$request['types'])->get();
        foreach ($movementCategories as $category) {
            $results[] = [
                'id' => $category->id,
                'text' => (count($request['types']) > 1 ? '[' . $category->type->name . '] ' : '') . $category->name,
            ];
        }

        return $results;
    }

    public function getMovementFields(Request $request)
    {
        if (!$request->ajax()) {
            return back();
        }

        $results = [];
        $movementFields = MovementField::get();
        foreach ($movementFields as $field) {
            $results[] = [
                'id' => $field->id,
                'text' => $field->label,
            ];
        }

        return $results;
    }

    public function getFieldsFromMovement(Request $request)
    {
        if (!$request->ajax()) {
            return back();
        }

        $results = [];
        $movement = Movement::with('fields')->where('id', $request['id'])->first();
        $movementFields = $movement->fields;
        foreach ($movementFields as $field) {
            $results[] = $field->id;
        }

        return $results;
    }
}
