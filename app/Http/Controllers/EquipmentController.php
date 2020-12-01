<?php

namespace App\Http\Controllers;

use App\Equipment;
use App\Http\Requests\CreateEquipment;
use App\Http\Requests\UpdateEquipment;
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
                'difficulty' => 'difficulty',
                'entries' => 'entries_count',
            ];
            $sortParams = explode('_', $request['sort']);
            $sort = [$fieldMapping[$sortParams[0]], $sortParams[1]];
        }

        $equipment = Equipment::dateBetween([
                'from' => $request['date_from'] ?? null,
                'to' => $request['date_to'] ?? null
            ])
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
        $equipment = Equipment::with(['movements'])->where('id', $id)->first();
        if (!empty($request['movements'])) {
            $movements = $equipment->movements()->paginate(20, ['*'], 'movements');
        } else {
            $movements = $equipment->movements()->limit(4)->get();
        }

        return view('equipment.view', [
            'equipment' => $equipment,
            'movements' => $movements,
            'request' => $request,
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
