<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index(Request $request)
    {
        $visible = auth()->user()->visibleCourseIds();

        $subjects = Subject::query()
            ->when($visible !== null, fn ($q) => $q->whereHas('offerings.batch',
                fn ($b) => $b->whereIn('course_id', $visible)))
            ->when($request->filled('q'), function ($q) use ($request) {
                $t = '%'.$request->string('q').'%';
                $q->where(fn ($w) => $w->where('name', 'like', $t)->orWhere('code', 'like', $t));
            })
            ->withCount('offerings')
            ->orderBy('code')
            ->paginate(40);

        return view('subjects.index', compact('subjects'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code'    => ['required', 'string', 'max:30', 'unique:subjects,code'],
            'name'    => ['required', 'string', 'max:255'],
            'credits' => ['nullable', 'integer', 'min:0', 'max:20'],
            'type'    => ['required', 'in:theory,practical,project'],
        ]);

        Subject::create($data + ['is_active' => true]);

        return back()->with('status', "Subject {$data['code']} added.");
    }
}
