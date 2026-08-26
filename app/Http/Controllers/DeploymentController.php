<?php

namespace App\Http\Controllers;

use App\Actions\Deployments\AssignGuardAction;
use App\Actions\Deployments\EndDeploymentAction;
use App\Http\Requests\StoreDeploymentRequest;
use App\Http\Requests\UpdateDeploymentRequest;
use App\Models\Client;
use App\Models\Deployment;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DeploymentController extends Controller
{
    public function __construct(
        protected AssignGuardAction $assignGuardAction,
        protected EndDeploymentAction $endDeploymentAction,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Deployment::class);

        return Inertia::render('master-data/deployments/index', [
            'deployments' => Deployment::query()
                ->with(['client', 'employee'])
                ->latest()
                ->get()
                ->map(fn (Deployment $deployment): array => [
                    ...$this->deploymentPayload($deployment),
                    'can_update' => $request->user()->can('update', $deployment),
                    'can_delete' => $request->user()->can('delete', $deployment),
                ])
                ->values(),
            'can' => [
                'create_deployment' => $request->user()->can('create', Deployment::class),
            ],
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Deployment::class);

        return Inertia::render('master-data/deployments/create', [
            'clients' => Client::query()->orderBy('name')->get(['id', 'name']),
            'guards' => Employee::query()
                ->where('employee_type', 'guard')
                ->where('employment_status', 'active')
                ->where('availability_status', 'available')
                ->orderBy('first_name')
                ->get()
                ->map(fn (Employee $guard): array => [
                    'id' => $guard->id,
                    'full_name' => "{$guard->first_name} {$guard->last_name}",
                ])
                ->values(),
        ]);
    }

    public function store(StoreDeploymentRequest $request): RedirectResponse
    {
        $this->assignGuardAction->execute($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Guard deployed.')]);

        return to_route('deployments.index');
    }

    public function edit(Deployment $deployment): Response
    {
        $this->authorize('update', $deployment);

        return Inertia::render('master-data/deployments/edit', [
            'deployment' => $this->deploymentPayload($deployment->load(['client', 'employee'])),
        ]);
    }

    public function update(UpdateDeploymentRequest $request, Deployment $deployment): RedirectResponse
    {
        if ($deployment->status === 'active' && $request->validated('status') === 'ended') {
            $this->endDeploymentAction->execute($deployment, $request->validated('end_date'));
        } else {
            $deployment->update($request->safe()->only(['post_name', 'end_date', 'billing_rate', 'status']));
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Deployment updated.')]);

        return to_route('deployments.index');
    }

    public function destroy(Deployment $deployment): RedirectResponse
    {
        $this->authorize('delete', $deployment);

        $deployment->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Deployment deleted.')]);

        return to_route('deployments.index');
    }

    /**
     * @return array<string, mixed>
     */
    protected function deploymentPayload(Deployment $deployment): array
    {
        return [
            'id' => $deployment->id,
            'client_id' => $deployment->client_id,
            'client_name' => $deployment->client?->name,
            'employee_id' => $deployment->employee_id,
            'guard_name' => trim(($deployment->employee?->first_name ?? '').' '.($deployment->employee?->last_name ?? '')),
            'post_name' => $deployment->post_name,
            'start_date' => $deployment->start_date?->toDateString(),
            'end_date' => $deployment->end_date?->toDateString(),
            'billing_rate' => $deployment->billing_rate,
            'status' => $deployment->status,
        ];
    }
}
