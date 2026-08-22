<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGuardRequest;
use App\Http\Requests\UpdateGuardRequest;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class GuardController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Employee::class);

        return Inertia::render('master-data/guards/index', [
            'guards' => Employee::query()
                ->with('compensationDetail')
                ->where('employee_type', 'guard')
                ->latest()
                ->get()
                ->map(fn (Employee $guard): array => $this->guardPayload($guard))
                ->values(),
            'can' => [
                'create_guard' => $request->user()->can('create', Employee::class),
            ],
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Employee::class);

        return Inertia::render('master-data/guards/create');
    }

    public function store(StoreGuardRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request): void {
            $guard = Employee::query()->create([
                ...$request->safe()->only([
                    'employee_number',
                    'first_name',
                    'last_name',
                    'contact_number',
                    'address',
                    'employment_status',
                    'availability_status',
                ]),
                'employee_type' => 'guard',
            ]);

            $guard->compensationDetail()->create($request->safe()->only([
                'daily_rate',
                'night_differential_rate',
                'sss_number',
                'philhealth_number',
                'pagibig_number',
            ]));
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Guard profile created.')]);

        return to_route('guards.index');
    }

    public function edit(Employee $guard): Response
    {
        $this->authorize('update', $guard);

        return Inertia::render('master-data/guards/edit', [
            'guard' => $this->guardPayload($guard->load('compensationDetail')),
        ]);
    }

    public function update(UpdateGuardRequest $request, Employee $guard): RedirectResponse
    {
        DB::transaction(function () use ($request, $guard): void {
            $guard->update($request->safe()->only([
                'employee_number',
                'first_name',
                'last_name',
                'contact_number',
                'address',
                'employment_status',
                'availability_status',
            ]));

            $guard->compensationDetail()->updateOrCreate(
                ['employee_id' => $guard->id],
                $request->safe()->only([
                    'daily_rate',
                    'night_differential_rate',
                    'sss_number',
                    'philhealth_number',
                    'pagibig_number',
                ]),
            );
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Guard profile updated.')]);

        return to_route('guards.index');
    }

    public function destroy(Employee $guard): RedirectResponse
    {
        $this->authorize('delete', $guard);

        $guard->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Guard profile deleted.')]);

        return to_route('guards.index');
    }

    /**
     * @return array<string, mixed>
     */
    protected function guardPayload(Employee $guard): array
    {
        return [
            'id' => $guard->id,
            'employee_number' => $guard->employee_number,
            'first_name' => $guard->first_name,
            'last_name' => $guard->last_name,
            'full_name' => "{$guard->first_name} {$guard->last_name}",
            'contact_number' => $guard->contact_number,
            'address' => $guard->address,
            'employment_status' => $guard->employment_status,
            'availability_status' => $guard->availability_status,
            'daily_rate' => $guard->compensationDetail?->daily_rate,
            'night_differential_rate' => $guard->compensationDetail?->night_differential_rate,
            'sss_number' => $guard->compensationDetail?->sss_number,
            'philhealth_number' => $guard->compensationDetail?->philhealth_number,
            'pagibig_number' => $guard->compensationDetail?->pagibig_number,
        ];
    }
}
