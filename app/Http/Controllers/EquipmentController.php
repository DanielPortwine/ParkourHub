<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\Follower;
use App\Http\Requests\CreateEquipment;
use App\Http\Requests\UpdateEquipment;
use App\Models\Movement;
use App\Models\MovementCategory;
use App\Models\MovementField;
use App\Models\Report;
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

        $equipment = Equipment::withCount(['movements'])
            ->with(['movements', 'reports', 'user'])
            ->dateBetween([
                'from' => $request['date_from'] ?? null,
                'to' => $request['date_to'] ?? null
            ])
            ->search($request['search'] ?? false)
            ->orderBy($sort[0], $sort[1])
            ->paginate(20)
            ->appends(request()->query());

        return view('content_listings', [
            'title' => 'Equipment',
            'content' => $equipment,
            'component' => 'equipment',
            'create' => true,
        ]);
    }
    public function view(Request $request, $id)
    {
        $equipment = Equipment::withTrashed()
            ->with([
                'movements',
                'reports',
            ])
            ->where('id', $id)
            ->first();

        if (empty($equipment) || ($equipment->deleted_at !== null && Auth::id() !== $equipment->user_id)) {
            return view('errors.404');
        }

        if (!empty($request['movements'])) {
            $movements = $equipment->movements()->paginate(20, ['*'], 'movements');
        } else {
            $movements = $equipment->movements()->limit(4)->get();
        }

        $linkableMovements = Movement::with(['type'])
            ->whereNotIn('id', $equipment->movements()->pluck('movements.id')->toArray())
            ->whereHas('type', function($q) {
                return $q->where('name', 'Exercise');
            })
            ->orderBy('category_id')
            ->get();
        $movementCategories = MovementCategory::with(['type'])
            ->whereHas('type', function ($q) {
                return $q->where('name', 'Exercise');
            })
            ->get();

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
        if ($equipment->user_id !== Auth::id()) {
            return redirect()->route('equipment_view', $id);
        }

        return view('equipment.edit', ['equipment' => $equipment]);
    }

    public function update(UpdateEquipment $request, $id)
    {
        if (!empty($request['delete'])) {
            return $this->delete($id, $request['redirect']);
        }

        $equipment = Equipment::where('id', $id)->first();
        if ($equipment->user_id !== Auth::id()) {
            return redirect()->route('equipment_view', $id);
        }
        $equipment->name = $request['name'];
        $equipment->description = $request['description'];
        $equipment->visibility = $request['visibility'] ?: 'private';
        if (!empty($request['image'])) {
            $equipment->image = Storage::url($request->file('image')->store('images/equipment', 'public'));
        }
        $equipment->save();

        return back()->with([
            'status' => 'Successfully updated equipment',
            'redirect' => $request['redirect'],
        ]);
    }

    public function delete($id, $redirect = null)
    {
        $equipment = Equipment::where('id', $id)->first();
        if ($equipment->user_id === Auth::id()) {
            $equipment->delete();
        }

        if (!empty($redirect)) {
            return redirect($redirect)->with('status', 'Successfully deleted equipment');
        }

        return back()->with('status', 'Successfully deleted equipment');
    }

    public function recover(Request $request, $id)
    {
        $equipment = Equipment::onlyTrashed()->where('id', $id)->first();

        if (empty($equipment) || $equipment->user_id !== Auth::id()) {
            return back();
        }

        $equipment->restore();

        return back()->with('status', 'Successfully recovered equipment.');
    }

    public function remove(Request $request, $id)
    {
        $equipment = Equipment::withTrashed()->where('id', $id)->first();

        if ($equipment->user_id !== Auth::id() && !Auth::user()->hasPermissionTo('remove content')) {
            return back();
        }

        if (!empty($equipment->image)) {
            Storage::disk('public')->delete(str_replace('storage/', '', $equipment->image));
        }

        $equipment->forceDelete();

        return back()->with('status', 'Successfully removed equipment forever.');
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
}
