<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Staff;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $ownDept = $user->department();

        $staff = Staff::query()
            ->with('department')
            // A department user sees their own colleagues, not the whole campus.
            ->when(! $user->seesEverything() && $ownDept,
                fn ($q) => $q->where('department_id', $ownDept->id))
            ->when(! $user->seesEverything() && ! $ownDept, fn ($q) => $q->whereRaw('1 = 0'))
            ->when($request->filled('department'),
                fn ($q) => $q->where('department_id', $request->integer('department')))
            ->when($request->filled('category'),
                fn ($q) => $q->where('category', $request->string('category')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $t = '%'.$request->string('q').'%';
                $q->where(fn ($w) => $w->where('name', 'like', $t)
                    ->orWhere('staff_no', 'like', $t)
                    ->orWhere('designation', 'like', $t));
            })
            ->orderByDesc('is_hod')
            ->orderBy('name')
            ->paginate(50);

        $departments = $user->seesEverything()
            ? Department::orderBy('name')->get()
            : Department::where('id', $ownDept?->id)->get();

        return view('staff.index', compact('staff', 'departments', 'ownDept'));
    }
}
