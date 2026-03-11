<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;

        $sort = $request->sort ?? 'id';
        $direction = $request->direction ?? 'desc';

        $clients = Client::query()
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%");
            })
            ->orderBy($sort, $direction)
            ->paginate(10)
            ->appends($request->query());

        return view('clients.index', compact('clients', 'search', 'sort', 'direction'));
    }

    public function create()
    {
        return view('clients.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
        ]);

        Client::create($validated);
        
        return redirect('/clients')->with('success', 'Cliente creato correttamente.');
    }

    public function edit(Client $client)
    {
        return view('clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
        ]);

        $client->update($validated);

        return redirect('/clients')->with('success', 'Cliente aggiornato.');
    }

    public function destroy(Client $client)
    {
        $client->delete();

        return redirect('/clients')->with('success', 'Cliente eliminato.');
    }
}