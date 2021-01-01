<?php

namespace App\Http\Controllers;

use App\Equipment;
use App\Follower;
use App\Http\Requests\CreateEquipment;
use App\Http\Requests\UpdateEquipment;
use App\Movement;
use App\MovementCategory;
use App\MovementField;
use App\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class EquipmentController extends Controller
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

        $equipment = Equipment::search($request['search'] ?? '')
            ->dateBetween([
                'from' => $request['date_from'] ?? null,
                'to' => $request['date_to'] ?? null
            ])
            ->where(function($q) {
                $q->where('visibility', 'public')
                    ->orWhere(function($q1) {
                        $q1->where('visibility', 'follower')
                            ->whereIn('user_id', Follower::where('follower_id', Auth::id())->pluck('user_id')->toArray());
                    })
                    ->orWhere('user_id', Auth::id());
            })
            ->orderBy($sort[0], $sort[1])
            ->paginate(20);

        return view('content_listings', [
            'title' => 'Equipment',
            'content' => $equipment,
            'component' => 'equipment',
            'create' => true,
        ]);
    }
    public function view(Request $request, $id)
    {
        $equipment = Equipment::with(['movements'])
            ->where(function($q) {
                if (Auth::id() !== 1) {
                    $q->where('visibility', 'public')
                        ->orWhere(function($q1) {
                            $q1->where('visibility', 'follower')
                                ->whereIn('user_id', Follower::where('follower_id', Auth::id())->pluck('user_id')->toArray());
                        })
                        ->orWhere('user_id', Auth::id());
                }
            })
            ->where('id', $id)
            ->first();
        if (!empty($request['movements'])) {
            $movements = $equipment->movements()
                ->where(function($q) {
                    if (Auth::id() !== 1) {
                        $q->where('visibility', 'public')
                            ->orWhere(function($q1) {
                                $q1->where('visibility', 'follower')
                                    ->whereIn('user_id', Follower::where('follower_id', Auth::id())->pluck('user_id')->toArray());
                            })
                            ->orWhere('user_id', Auth::id());
                    }
                })
                ->paginate(20, ['*'], 'movements');
        } else {
            $movements = $equipment->movements()
                ->where(function($q) {
                    if (Auth::id() !== 1) {
                        $q->where('visibility', 'public')
                            ->orWhere(function($q1) {
                                $q1->where('visibility', 'follower')
                                    ->whereIn('movements.user_id', Follower::where('follower_id', Auth::id())->pluck('user_id')->toArray());
                            })
                            ->orWhere('movements.user_id', Auth::id());
                    }
                })
                ->limit(4)
                ->get();
        }

        $linkableMovements = Movement::where('id', '!=', $id)
            ->whereNotIn('id', $equipment->movements()->pluck('movements.id')->toArray())
            ->where('type_id', 2)
            ->orderBy('category_id')
            ->get();
        $movementCategories = MovementCategory::where('type_id', 2)->get();

        return view('equipment.view', [
            'equipment' => $equipment,
            'movements' => $movements,
            'request' => $request,
            'linkableMovements' => $linkableMovements,
            'movementCategories' => $movementCategories,
        ]);
    }

    public function create()
    {
        return view('equipment.create');
    }

    public function store(CreateEquipment $request)
    {
        $equipment = new Equipment;
        $equipment->user_id = Auth::id();
        $equipment->name = $request['name'];
        $equipment->description = $request['description'];
        $equipment->visibility = $request['visibility'] ?: 'private';
        if (!empty($request['image'])) {
            $equipment->image = Storage::url($request->file('image')->store('images/equipment', 'public'));
        }
        $equipment->save();

        if (!empty($request['movement'])) {
            $equipment->movements()->attach($request['movement'], ['user_id' => Auth::id()]);
        }

        return redirect()->route('equipment_view', $equipment->id)->with('status', 'Successfully created equipment');
    }

    public function edit($id)
    {
        $equipment = Equipment::where('id', $id)->first();
        if ($equipment->user_id != Auth::id()) {
            return redirect()->route('equipment_view', $id);
        }

        return view('equipment.edit', ['equipment' => $equipment]);
    }

    public function update(UpdateEquipment $request, $id)
    {
        $equipment = Equipment::where('id', $id)->first();
        if ($equipment->user_id != Auth::id()) {
            return redirect()->route('movement_view', $id);
        }
        $equipment->name = $request['name'];
        $equipment->description = $request['description'];
        $equipment->visibility = $request['visibility'] ?: 'private';
        if (!empty($request['image'])) {
            $equipment->image = Storage::url($request->file('image')->store('images/equipment', 'public'));
        }
        $equipment->save();

        return back()->with('status', 'Successfully updated movement');
    }

    public function delete($id, $redirect = null)
    {
        $equipment = Equipment::where('id', $id)->first();
        if ($equipment->user_id === Auth::id()) {
            $equipment->delete();
        }

        if (empty($redirect)) {
            $redirect = redirect()->route('equipment_listing');
        }

        return $redirect->with('status', 'Successfully deleted equipment');
    }

    public function report(Equipment $equipment)
    {
        $equipment->report();

        return back()->with('status', 'Successfully reported equipment');
    }

    public function discardReports(Equipment $equipment)
    {
        $equipment->discardReports();

        return back()->with('status', 'Successfully discarded reports against this content');
    }

    public function getEquipment(Request $request)
    {
        if (!$request->ajax()) {
            return back();
        }

        $results = [];
        $equipments = Equipment::get();

        foreach ($equipments as $equipment) {
            $results[] = [
                'id' => $equipment->id,
                'text' => $equipment->name,
            ];
        }

        return $results;
    }
}
