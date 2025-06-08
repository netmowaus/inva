<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // For potential use if company is tied to a user

class CompanyController extends Controller
{
    public function __construct()
    {
        // Assuming 'admin' middleware is registered and used in routes for this controller
        // $this->middleware('admin'); // Or apply directly in routes
    }

    /**
     * Display the company's information.
     * Assumes a single company record for the application.
     */
    public function show()
    {
        // Attempt to find the first company record.
        // Admins would typically set this up once.
        $company = Company::first();

        // If no company record exists, perhaps redirect to a setup page or show a default.
        // For this example, we'll pass null if not found, and the view can handle it.
        return view('admin.company.show', compact('company'));
    }

    /**
     * Show the form for editing the company's information.
     * If no company exists, this can serve as a create form.
     */
    public function edit()
    {
        $company = Company::first();
        // If $company is null, the form will be for creating a new entry.
        return view('admin.company.edit', compact('company'));
    }


    /**
     * Update the specified company information in storage or create it if it doesn't exist.
     */
    public function update(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'logo_path' => 'nullable|string|max:255', // In a real app, this would be a file upload
        ]);

        // Find the first company record, or create a new one if it doesn't exist.
        $company = Company::first();
        if ($company) {
            $company->update($validatedData);
        } else {
            $company = Company::create($validatedData);
        }

        return redirect()->route('admin.company.show')->with('success', 'Company information updated successfully.');
    }
}
