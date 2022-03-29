<?php

namespace App\Http\Controllers\Drivers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Bus;
use App\User;

class BusController extends Controller
{

    /* get all buses based on filter */
    public function filter(Request $request)
    {
        // get the current logged school
        $school = Auth::user();
        // get all buses with names that match the filter term
        $query = Bus::query();
        $query->with('driver')->where('school_id', $school->id);
        if ($request->search) {
            $query->where('license', 'LIKE', '%' . $request->search . '%');
        }
        // order the obtained buses with the requred order column
        $buses = $query->orderBy($request->input('orderBy.column'), $request->input('orderBy.direction'))
            ->paginate($request->input('pagination.per_page'));
        //$buses->load('driver');
        return response()->json(['buses' => $buses]);
        //return the obtained buses
        //return $buses;
    }

    /* get all buses of the current logged school */
    public function all()
    {
        //get the current logged school 
        $school = Auth::user();
        //get all buses of this school
        $query = Bus::query();
        return $query->where('school_id', $school->id)->orderBy('created_at', 'desc')->get();
    }
    /* create a new bus in the current logged school */
    public function store (Request $request)
    {
        //get the current logged school
        $school = Auth::user();
        //make nice names for validation errors
        $niceNames = [
            'license' => 'bus license'
        ]; 
        //validate the request
        $this->validate($request, [
            'license' => 'required|string|unique:buses',
        ], [], $niceNames);
        //create a new bus
        $bus = Bus::create([
            'license' => $request->license,
            'school_id' => $school->id,
        ]);
        //return the created bus
        return $bus;
    }

        /* delete a specific plan */
        public function destroy($bus_id)
        {
            $bus = Bus::with('driver')->find($bus_id);
            if($bus->driver)
            {
                return response()->json(['errors' => ["Can not delete the bus as it is already assigned to a driver"]], 422);
            }
            else
            {
                $bus->delete();
            }
        }
}
