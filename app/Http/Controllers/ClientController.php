<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Models\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ClientController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Client::class);

        return Inertia::render('master-data/clients/index', [
            'clients' => Client::query()
                ->latest()
                ->get()
                ->map(fn (Client $client): array => $this->clientPayload($client))
                ->values(),
            'can' => [
                'create_client' => $request->user()->can('create', Client::class),
            ],
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Client::class);

        return Inertia::render('master-data/clients/create');
    }

    public function store(StoreClientRequest $request): RedirectResponse
    {
        Client::query()->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Client created.')]);

        return to_route('clients.index');
    }

    public function edit(Client $client): Response
    {
        $this->authorize('update', $client);

        return Inertia::render('master-data/clients/edit', [
            'client' => $this->clientPayload($client),
        ]);
    }

    public function update(UpdateClientRequest $request, Client $client): RedirectResponse
    {
        $client->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Client updated.')]);

        return to_route('clients.index');
    }

    public function destroy(Client $client): RedirectResponse
    {
        $this->authorize('delete', $client);

        $client->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Client deleted.')]);

        return to_route('clients.index');
    }

    /**
     * @return array<string, mixed>
     */
    protected function clientPayload(Client $client): array
    {
        return [
            'id' => $client->id,
            'name' => $client->name,
            'billing_address' => $client->billing_address,
            'company_address' => $client->company_address,
            'contact_person' => $client->contact_person,
            'payroll_period' => $client->payroll_period,
            'cutoff_type' => $client->cutoff_type,
            'first_cutoff_day' => $client->first_cutoff_day,
            'second_cutoff_day' => $client->second_cutoff_day,
            'payroll_frequency' => $client->payroll_frequency,
        ];
    }
}
