<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\Appointment;

class NotificationService
{
    /**
     * Create a notification
     */
    public static function create(
        int $userId,
        string $type,
        string $message,
        ?int $senderId = null,
        ?int $appointmentId = null
    ): Notification {
        return Notification::create([
            'user_id' => $userId,
            'sender_id' => $senderId,
            'appointment_id' => $appointmentId,
            'type' => $type,
            'message' => $message,
        ]);
    }

    /**
     * Notify appointment created (to therapist)
     */
    public static function notifyAppointmentCreated(Appointment $appointment): void
    {
        self::create(
            userId: $appointment->therapist_id,
            type: 'appointment_created',
            message: "New appointment request from {$appointment->student->name} on " . 
                     $appointment->appointment_date->format('M d, Y'),
            senderId: $appointment->student_id,
            appointmentId: $appointment->id
        );
    }

    /**
     * Notify appointment approved (to student)
     */
    public static function notifyAppointmentApproved(Appointment $appointment): void
    {
        self::create(
            userId: $appointment->student_id,
            type: 'appointment_approved',
            message: "Your appointment on {$appointment->appointment_date->format('M d, Y')} has been approved",
            senderId: $appointment->therapist_id,
            appointmentId: $appointment->id
        );
    }

    /**
     * Notify appointment rejected (to student)
     */
    public static function notifyAppointmentRejected(Appointment $appointment, ?string $reason = null): void
    {
        $message = "Your appointment on {$appointment->appointment_date->format('M d, Y')} has been rejected";
        if ($reason) {
            $message .= ". Reason: {$reason}";
        }

        self::create(
            userId: $appointment->student_id,
            type: 'appointment_rejected',
            message: $message,
            senderId: $appointment->therapist_id,
            appointmentId: $appointment->id
        );
    }

    /**
     * Notify appointment completed (to student)
     */
    public static function notifyAppointmentCompleted(Appointment $appointment): void
    {
        self::create(
            userId: $appointment->student_id,
            type: 'appointment_completed',
            message: "Your appointment has been marked as completed",
            senderId: $appointment->therapist_id,
            appointmentId: $appointment->id
        );
    }

    /**
     * Notify appointment cancelled (to therapist)
     */
    public static function notifyAppointmentCancelled(Appointment $appointment): void
    {
        self::create(
            userId: $appointment->therapist_id,
            type: 'appointment_cancelled',
            message: "{$appointment->student->name} cancelled their appointment on " . 
                     $appointment->appointment_date->format('M d, Y'),
            senderId: $appointment->student_id,
            appointmentId: $appointment->id
        );
    }
}