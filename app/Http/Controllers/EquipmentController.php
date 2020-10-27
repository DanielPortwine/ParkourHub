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

    public function create(CreateEquipment $request)
    {
        $equipment = new Equipment;
        $equipment->user_id = Auth::id();
        $equipment->name = $request['name'];
        $equipment->description = $request['description'];
        if (!empty($request['image'])) {
            $equipment->image = Storage::url($request->file('image')->store('images/equipment', 'public'));
        }
        $equipment->save();

        $equipment->movements()->attach($request['movement'], ['user_id' => Auth::id()]);

        return back()->with('status', 'Successfully created equipment.');
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

        return back()->with('status', 'Successfully updated movement.');
    }

    public function delete($id)
    {
        $equipment = Equipment::where('id', $id)->first();
        if ($equipment->user_id === Auth::id()) {
            $equipment->delete();
        }

        return redirect()->route('movement_listing');
    }

    public function report($id)
    {
        $report = new Report;
        $report->reportable_id = $id;
        $report->reportable_type = 'App\Equipment';
        $report->user_id = Auth::id();
        $report->save();

        return back()->with('status', 'Successfully reported equipment.');
    }

    public function deleteReported($id)
    {
        Equipment::where('id', $id)->first()->forceDelete();

        return redirect()->route('movement_listing')->with('status', 'Successfully deleted equipment and its related content.');
    }

    public function getEquipment(Request $request)
    {
        if (!$request->ajax()) {
            return back();
        }

        $results = [];
        $equipments = Cache::remember('equipment', 30, function() {
            return Equipment::get();
        });
        foreach ($equipments as $equipment) {
            $results[] = [
                'id' => $equipment->id,
                'text' => $equipment->name,
            ];
        }

        return $results;
    }
}
