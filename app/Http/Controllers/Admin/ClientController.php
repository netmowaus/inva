<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    // Middleware for admin access would be applied in routes/web.php for this group

    /**
     * Display a listing of the clients.
     */
    public function index()
    {
        $clients = Client::latest()->paginate(10); // Paginate for better display
        return view('admin.clients.index', compact('clients'));
    }

    /**
     * Show the form for creating a new client.
     */
    public function create()
    {
        return view('admin.clients.create');
    }

    /**
     * Store a newly created client in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'company_name' => 'required|string|max:255',
            'contact_name' => 'required|string|max:255',
            'contact_email' => 'required|email|max:255|unique:clients,contact_email',
            'address' => 'required|string',
            'phone' => 'nullable|string|max:50',
        ]);

        Client::create($validatedData);

        return redirect()->route('admin.clients.index')->with('success', 'Client created successfully.');
    }

    /**
     * Display the specified client. (Optional - often edit is sufficient for admin)
     */
    public function show(Client $client)
    {
        return view('admin.clients.show', compact('client')); // You might not need a dedicated show for admin
    }

    /**
     * Show the form for editing the specified client.
     */
    public function edit(Client $client)
    {
        return view('admin.clients.edit', compact('client'));
    }

    /**
     * Update the specified client in storage.
     */
    public function update(Request $request, Client $client)
    {
        $validatedData = $request->validate([
            'company_name' => 'required|string|max:255',
            'contact_name' => 'required|string|max:255',
            'contact_email' => 'required|email|max:255|unique:clients,contact_email,' . $client->id,
            'address' => 'required|string',
            'phone' => 'nullable|string|max:50',
        ]);

        $client->update($validatedData);

        return redirect()->route('admin.clients.index')->with('success', 'Client updated successfully.');
    }

    /**
     * Remove the specified client from storage.
     */
    public function destroy(Client $client)
    {
        // Consider related records (quotes, invoices) and how they should be handled.
        // Soft deletes are on the model, so this will soft delete.
        // If there are foreign key constraints that prevent deletion (e.g., no ON DELETE CASCADE),
        // you might need to handle that (e.g., check if client has invoices/quotes first).
        // For now, we assume soft delete is sufficient.
        $client->delete();

        return redirect()->route('admin.clients.index')->with('success', 'Client deleted successfully.');
    }
}
