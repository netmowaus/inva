<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\ClientController; // Will be used later

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome'); // Assuming a default welcome view exists
});

// Authentication routes (e.g., Laravel Breeze or Jetstream would typically handle this)
// Route::get('/login', ...)->name('login');
// Route::post('/logout', ...)->name('logout');
// Route::get('/register', ...)->name('register');


// Admin Routes
// The 'auth' middleware ensures the user is logged in.
// The 'admin' middleware (conceptual, defined earlier) would ensure the user has an admin role.
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Company Information Routes
    Route::get('company', [CompanyController::class, 'show'])->name('company.show');
    Route::get('company/edit', [CompanyController::class, 'edit'])->name('company.edit');
    Route::put('company', [CompanyController::class, 'update'])->name('company.update'); // Using PUT for update

    // Client Management Routes
    Route::resource('clients', ClientController::class)->except(['show']); // 'show' can be added if needed
    // Example: Route::get('clients/{client}', [ClientController::class, 'show'])->name('clients.show');

    // Quote Management (Admin)
    Route::get('quotes/{quote}/pdf', [\App\Http\Controllers\Admin\QuoteController::class, 'downloadPDF'])->name('quotes.pdf');
    Route::post('quotes/{quote}/send-email', [\App\Http\Controllers\Admin\QuoteController::class, 'sendEmail'])->name('quotes.sendEmail');
    Route::resource('quotes', \App\Http\Controllers\Admin\QuoteController::class);

    // Invoice Management (Admin)
    Route::get('invoices/{invoice}/pdf', [\App\Http\Controllers\Admin\InvoiceController::class, 'downloadPDF'])->name('invoices.pdf');
    Route::post('invoices/{invoice}/send-email', [\App\Http\Controllers\Admin\InvoiceController::class, 'sendEmail'])->name('invoices.sendEmail');
    Route::get('invoices/create-from-quote/{quote}', [\App\Http\Controllers\Admin\InvoiceController::class, 'createFromQuote'])->name('invoices.createFromQuote');
    Route::resource('invoices', \App\Http\Controllers\Admin\InvoiceController::class);

    // Payment Management (Admin - nested under invoices)
    Route::prefix('invoices/{invoice}/payments')->name('invoices.payments.')->group(function() {
        Route::get('create', [\App\Http\Controllers\Admin\PaymentController::class, 'create'])->name('create');
        Route::post('', [\App\Http\Controllers\Admin\PaymentController::class, 'store'])->name('store');
    });
    // Separate routes for payment edit, update, destroy as they operate on Payment model directly
    Route::prefix('payments/{payment}')->name('payments.')->group(function() {
        Route::get('edit', [\App\Http\Controllers\Admin\PaymentController::class, 'edit'])->name('edit');
        Route::put('', [\App\Http\Controllers\Admin\PaymentController::class, 'update'])->name('update');
        Route::delete('', [\App\Http\Controllers\Admin\PaymentController::class, 'destroy'])->name('destroy');
    });
});

// Client Routes
Route::middleware(['auth'])->prefix('client')->name('client.')->group(function() {
    // Client Quote Viewing & Actions
    Route::get('quotes', [\App\Http\Controllers\Client\QuoteController::class, 'index'])->name('quotes.index');
    Route::get('quotes/{quote}', [\App\Http\Controllers\Client\QuoteController::class, 'show'])->name('quotes.show');
    Route::post('quotes/{quote}/accept', [\App\Http\Controllers\Client\QuoteController::class, 'accept'])->name('quotes.accept');
    Route::post('quotes/{quote}/reject', [\App\Http\Controllers\Client\QuoteController::class, 'reject'])->name('quotes.reject');
    Route::get('quotes/{quote}/pdf', [\App\Http\Controllers\Client\QuoteController::class, 'downloadPDF'])->name('quotes.pdf');

    // Client Invoice Viewing
    Route::get('invoices', [\App\Http\Controllers\Client\InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('invoices/{invoice}', [\App\Http\Controllers\Client\InvoiceController::class, 'show'])->name('invoices.show');
    Route::get('invoices/{invoice}/pdf', [\App\Http\Controllers\Client\InvoiceController::class, 'downloadPDF'])->name('invoices.pdf');

    // Other client-specific routes can go here (e.g., profile)
});

// Example of a fallback for users trying to access admin without privileges,
// if not handled by middleware redirect or abort.
// Route::get('/unauthorized', function() { return "You are not authorized."; })->name('unauthorized');

// If using Laravel Fortify/Jetstream or Breeze, they will set up /login, /register, /dashboard etc.
// For now, let's assume a basic auth setup or placeholder.
// A dashboard route for authenticated users (non-admin clients might see this)
Route::middleware(['auth'])->get('/dashboard', function () {
    if(auth()->user()->isAdmin()){
        // Optional: redirect admin users to a specific admin dashboard if they land here.
        // return redirect()->route('admin.company.show'); // or an admin dashboard
    }
    return "User Dashboard (Visible to logged-in users)"; // Placeholder
})->name('dashboard');

// Add a fallback for the login route if not otherwise defined by an auth package
if (!Route::has('login')) {
    Route::get('/login', function() {
        // This is a placeholder. In a real app, you'd have a login view.
        // For now, it redirects to the root if you try to access it directly without being an admin.
        // If an auth system is in place, it would show a login page.
        return 'This would be the login page. <a href="/">Go Home</a>';
    })->name('login');
}
