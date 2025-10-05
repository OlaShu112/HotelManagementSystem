<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\BarController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\HotelController;
use App\Http\Controllers\MenueController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OutletController;
use App\Http\Controllers\ReservationController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ExpenseController;
use App\Models\Reservation;
use App\Http\Controllers\ExpenseTypeController;
use App\Http\Controllers\PaymentTypeController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ChecklistController;
use App\Http\Controllers\StaffCheckinCheckoutController;
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\RoomTypeController;
use App\Http\Controllers\StripeWebhookController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

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
Route::get('/refresh-app', function () {
    try {
        // Clear caches
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');
        
        // Create storage link if it doesn't exist
        if (!file_exists(public_path('storage'))) {
            Artisan::call('storage:link');
        }
        
        // Test database connection
        try {
            \DB::connection()->getPdo();
            $dbStatus = 'Database connected: ' . \DB::connection()->getDatabaseName();
        } catch (\Exception $e) {
            $dbStatus = 'Database connection failed: ' . $e->getMessage();
        }
        
        return response()->json([
            'status' => 'success',
            'operations' => [
                'config_cleared' => true,
                'cache_cleared' => true,
                'route_cache_cleared' => true,
                'view_cache_cleared' => true,
                'storage_link_created' => file_exists(public_path('storage')),
                'database_status' => $dbStatus
            ]
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
});
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handleWebhook']);
// Redirect root based on authentication status
Route::get('/', [FrontendController::class, 'welcome']);

Route::get('/subscribe', [FrontendController::class, 'create'])->name('subscribe');
Route::post('/book', [FrontendController::class, 'store'])->name('booking.store');
Route::get('/room-types', [FrontendController::class, 'getRoomTypes'])->name('booking.room-types');
Route::get('/rooms/{type}', [FrontendController::class, 'getRoomsByType'])->name('booking.rooms');
// Login route
// Login route
Route::get('admin/login', function () {
    if (Auth::check()) {
        return redirect()->route('admin.dashboard');
    }
    return view('auth.login');
})->name('login');

Auth::routes();

// Redirecting /home to dashboard
Route::get('/home', function () {
    return redirect('/admin/dashboard');
});

// Redirecting /admin to dashboard if authenticated
Route::get('/admin', function () {
    if (Auth::check()) {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('login');
});

// Redirecting /home to dashboard
Route::get('/home', function () {
    return redirect('/admin/dashboard');
});
// Grouping admin routes with auth middleware


Route::middleware(['auth', 'check.subscription'])->prefix('admin')->group(function () {
    Route::get('dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('setting', [AdminController::class, 'Setting'])->name('admin.setting');
    Route::post('setting/update', [AdminController::class, 'settingStore'])->name('settings.update');
    Route::get('profile_edit', [AdminController::class, 'editProfile'])->name('admin.profile.edit');
    Route::post('profile/update', [AdminController::class, 'updateProfile'])->name('admin.profile.update');
    Route::get('change_password', [AdminController::class, 'changePassword'])->name('admin.password.change');
    Route::post('password/store', [AdminController::class, 'passwordStore'])->name('admin.password.store');
    Route::resource('roles', RoleController::class);
    Route::resource('users', UserController::class);
    Route::resource('rooms', RoomController::class);
    Route::resource('subscriptions', SubscriptionController::class);
    Route::get('/reservations/confirmed', [ReservationController::class, 'confirmed'])->name('reservations.confirmed');
    Route::get('/reservations/cancelled', [ReservationController::class, 'cancelled'])->name('reservations.cancelled');
    Route::resource('reservations', ReservationController::class);
    Route::post('/reservations/check-availability', [ReservationController::class, 'checkAvailability'])->name('reservations.checkAvailability');
    Route::post('/reservations/{id}/cancel', [ReservationController::class, 'cancel'])->name('reservations.cancel');
    Route::post('/reservations/{id}/confirm', [ReservationController::class, 'confirm'])->name('reservations.confirm');
    Route::get('today/confirmed', [ReservationController::class, 'todayConfirmedReservations'])->name('today.confirmed.reservations');
    Route::post('/reservations/{reservation}/checkin', [ReservationController::class, 'checkin'])->name('reservations.checkin');
    Route::post('/reservations/{reservation}/checkout', [ReservationController::class, 'checkout'])->name('reservations.checkout');
    Route::get('today/reservations/checkin', [ReservationController::class, 'checkinReservations'])->name('checkin.reservations');
    Route::get('today/reservations/checkout', [ReservationController::class, 'checkoutReservations'])->name('checkout.reservations');
    Route::get('reservation/calendar', [ReservationController::class, 'calendarReservations'])->name('calendar.reservations');
    Route::get('get/reservation/calendar', [ReservationController::class, 'getCelandarReservations'])->name('get.calendar.reservations');
    //OutLet Controllerp
    Route::resource('/outlets', OutletController::class);
    Route::resource('/hotels', HotelController::class);
    // Resturant Complete Flow
    Route::get('resturant/menue', [MenueController::class, 'index'])->name('resturant.menue.index');
    Route::get('resturant/menue/create', [MenueController::class, 'create'])->name('resturant.menue.create');
    Route::post('resturant/menue/store', [MenueController::class, 'store'])->name('resturant.menue.store');
    Route::get('resturant/menue/{id}/edit', [MenueController::class, 'edit'])->name('resturant.menue.edit');
    Route::put('resturant/menue/{id}', [MenueController::class, 'update'])->name('resturant.menue.update');
    Route::delete('resturant/menue/{id}', [MenueController::class, 'destroy'])->name('resturant.menue.destroy');
    // Order Controller
    Route::post('/orders/{order}/start', [OrderController::class, 'start'])->name('orders.start');
    Route::post('/orders/{order}/complete', [OrderController::class, 'complete'])->name('orders.complete');
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    Route::get('/orders/{order}/receipt', [OrderController::class, 'generateReceipt'])->name('orders.receipt');
    Route::get('/menus/export/{type}', [MenueController::class, 'export'])->name('menus.export');
    Route::post('/menus/import/{type}', [MenueController::class, 'import'])->name('menus.import');

    Route::resource('orders', OrderController::class);
    //Bar mangement
    Route::get('bar/menue', [BarController::class, 'index'])->name('bar.menue.index');
    Route::get('bar/menue/create', [BarController::class, 'create'])->name('bar.menue.create');
    Route::get('bar/menue/{id}/edit', [BarController::class, 'edit'])->name('bar.menue.edit');
    Route::get('bar/orders', [BarController::class, 'orderIndex'])->name('bar.orders.index');
    Route::get('bar/orders/create', [BarController::class, 'orderCreate'])->name('bar.orders.create');
    Route::get('bar/orders/{id}/edit', [BarController::class, 'orderCreate'])->name('bar.orders.edit');
    // Expense Module
    Route::get('expenses/pdf',[ExpenseController::class,'generatePDF'])->name('expense.generatePdf');
    Route::resource('expense_types', ExpenseTypeController::class);
    Route::resource('expenses', ExpenseController::class);

    Route::resource('payment_types', PaymentTypeController::class);

    // Report Management
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/pdf', [ReportController::class, 'generatePDF'])->name('reports.pdf');
    // / Checklist management
    Route::get('checklists', [ChecklistController::class, 'index'])->name('checklists.index');
    Route::get('checklists/create', [ChecklistController::class, 'create'])->name('checklists.create');
    Route::post('checklists', [ChecklistController::class, 'store'])->name('checklists.store');
    Route::get('checklists/{id}/edit', [ChecklistController::class, 'edit'])->name('checklists.edit');
    Route::put('checklists/{id}', [ChecklistController::class, 'update'])->name('checklists.update');
    Route::delete('checklists/{id}', [ChecklistController::class, 'destroy'])->name('checklists.destroy');

    Route::post('checklist-items', [ChecklistController::class, 'createItem'])->name('checklist-items.create');
    Route::put('checklist-items/{id}', [ChecklistController::class, 'updateItem'])->name('checklist-items.update');
    Route::delete('checklist-items/{id}', [ChecklistController::class, 'destroyItem'])->name('checklist-items.destroy');

    // / Audit management
    Route::get('audits', [AuditController::class, 'index'])->name('audits.index');
    Route::get('audits/create/{checklist_id}', [AuditController::class, 'create'])->name('audits.create');
    Route::post('audits/{checklist_id}', [AuditController::class, 'store'])->name('audits.store');
    Route::get('audits/{id}', [AuditController::class, 'show'])->name('audits.show');
    Route::delete('audits/{id}', [AuditController::class, 'destroy'])->name('audits.destroy');
    Route::get('audits/pdf/{id}', [AuditController::class, 'generatePdf'])->name('audits.pdf');

    Route::post('admin/checkin_checkout/toggle', [StaffCheckinCheckoutController::class, 'toggle'])->name('checkin_checkout.toggle');
    Route::get('admin/checkin_checkout/records', [StaffCheckinCheckoutController::class, 'records'])->name('checkin_checkout.records');
    Route::get('admin/checkin_checkout/user/{id}', [StaffCheckinCheckoutController::class, 'userDetail'])->name('checkin_checkout.user_detail');
    Route::get('admin/checkin_checkout/user/{id}/pdf', [StaffCheckinCheckoutController::class, 'userDetailPdf'])->name('checkin_checkout.user_detail_pdf');
    //Check Room Type
    Route::resource('room_types', RoomTypeController::class);
    Route::post('/discounts/validate', [\App\Http\Controllers\DiscountController::class, 'validateDiscount'])->name('discounts.validate');
 Route::resource('discounts', \App\Http\Controllers\DiscountController::class);

});
// Other routes that can be accessed by authenticated users
Route::get('profile_edit', [AdminController::class, 'editProfile'])->name('admin.profile.edit');
Route::post('profile/update', [AdminController::class, 'updateProfile'])->name('admin.profile.update');
Route::get('change_password', [AdminController::class, 'changePassword'])->name('admin.password.change');
Route::post('password/store', [AdminController::class, 'passwordStore'])->name('admin.password.store');
Route::post('/reservations/getRoomsByType', [ReservationController::class, 'getRoomsByType'])->name('reservations.getRoomsByType');
Route::post('client/reservation', [ReservationController::class, 'store'])->name('client.reservation.store');
