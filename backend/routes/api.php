<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TherapistDashboardController;
use App\Http\Controllers\TherapistProfileController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\MedicalRecordController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ForumController;

// Guest routes (no authentication required)
Route::middleware('guest')->group(function () {
    // Registration with OTP
    Route::post('/register', [RegisteredUserController::class, 'store'])->name('register');
    Route::post('/verify-otp', [RegisteredUserController::class, 'verifyOtp'])->name('verify.otp');
    Route::post('/resend-otp', [RegisteredUserController::class, 'resendOtp'])->name('resend.otp');
    
    // Login
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login');
    
    // Password reset
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::post('/verify-password-otp', [PasswordResetLinkController::class, 'verifyOtp'])->name('password.verify.otp');
    Route::post('/resend-password-otp', [PasswordResetLinkController::class, 'resendOtp'])->name('password.resend.otp');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

// Chatbot route
Route::post('/chat', [ChatController::class, 'send']);

// Authenticated routes (require auth:sanctum)
Route::middleware(['auth:sanctum'])->group(function () {
    // Current user
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Logout
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    // Profile routes (Student)
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'show']);
        Route::put('/', [ProfileController::class, 'update']);
        Route::post('/image', [ProfileController::class, 'updateImage']);
    });

    // Therapist Profile routes
    Route::prefix('therapist/profile')->group(function () {
        Route::get('/', [TherapistProfileController::class, 'show']);
        Route::put('/', [TherapistProfileController::class, 'update']);
    });

    // Notification routes
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/clear', [NotificationController::class, 'clearAll']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);

    // Dashboard routes - Student
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/journal/today', [DashboardController::class, 'getJournal']);
    Route::post('/journal/save', [DashboardController::class, 'saveJournal']);
    
    // Dashboard routes - Therapist
    Route::get('/therapist/dashboard', [TherapistDashboardController::class, 'index']);
    Route::get('/therapist/appointments/{id}', [AppointmentController::class, 'getAppointmentDetails']);

    // Appointment routes
    Route::prefix('appointments')->group(function () {
        Route::get('/therapists', [AppointmentController::class, 'getTherapists']);
        Route::get('/specializations', [AppointmentController::class, 'getSpecializations']);
        Route::get('/my-appointments', [AppointmentController::class, 'myAppointments']);
        Route::get('/available-slots', [AppointmentController::class, 'getAvailableTimeSlots']);
        Route::get('/patient-reflections/{studentId}', [AppointmentController::class, 'getPatientReflections']);
        Route::post('/', [AppointmentController::class, 'store']);
        Route::get('/{id}/edit', [AppointmentController::class, 'show']);
        Route::put('/{id}', [AppointmentController::class, 'update']);
        Route::delete('/{id}', [AppointmentController::class, 'destroy']);

        // Therapist actions
        Route::post('/{id}/approve', [AppointmentController::class, 'approve']);
        Route::post('/{id}/reject', [AppointmentController::class, 'reject']);
        Route::post('/{id}/complete', [AppointmentController::class, 'complete']);
    });

    // Medical Records routes
    Route::prefix('medical-records')->group(function () {
        Route::get('/', [MedicalRecordController::class, 'index']);
        Route::post('/', [MedicalRecordController::class, 'store']);
        Route::get('/{id}/download', [MedicalRecordController::class, 'download']);
        Route::delete('/{id}', [MedicalRecordController::class, 'destroy']);
    });

    // Notes routes
    Route::prefix('notes')->group(function () {
        Route::get('/', [NoteController::class, 'index']);
        Route::post('/', [NoteController::class, 'store']);
        Route::get('/{id}/download', [NoteController::class, 'download']);
        Route::delete('/{id}', [NoteController::class, 'destroy']);
    });

    // Evaluation routes
    Route::prefix('evaluations')->group(function () {
        Route::post('/', [EvaluationController::class, 'store']);
        Route::get('/check/{appointmentId}', [EvaluationController::class, 'check']);
    });

    // Forum routes
    Route::get('/forum/questions', [ForumController::class, 'index']);
    Route::post('/forum/questions', [ForumController::class, 'storeQuestion']);
    Route::post('/forum/questions/{questionId}/answers', [ForumController::class, 'storeAnswer']);

    // Email verification
    Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
});

// Email verification with signed route
Route::get('/verify-email/{id}/{hash}', VerifyEmailController::class)
    ->middleware(['auth:sanctum', 'signed', 'throttle:6,1'])
    ->name('verification.verify');