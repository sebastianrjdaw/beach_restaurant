<?php

namespace App\Notifications;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReservationCustomerNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Reservation $reservation,
        private readonly string $type,
        private readonly ?string $actionUrl = null,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return match ($this->type) {
            'confirmed' => $this->confirmedMail(),
            'cancelled' => $this->cancelledMail(),
            'changed' => $this->changedMail(),
            'verification' => $this->verificationMail(),
            default => $this->pendingMail(),
        };
    }

    private function pendingMail(): MailMessage
    {
        return $this->baseMail('Hemos recibido tu solicitud de reserva')
            ->line('El restaurante revisara tu solicitud y te confirmara la reserva.')
            ->line('Estado actual: pendiente.');
    }

    private function confirmedMail(): MailMessage
    {
        $mail = $this->baseMail('Tu reserva esta confirmada')
            ->line('Te esperamos en Restaurante A Saina.');

        return $this->actionUrl ? $mail->action('Cancelar reserva', $this->actionUrl) : $mail;
    }

    private function verificationMail(): MailMessage
    {
        $mail = $this->baseMail('Confirma tu reserva')
            ->line('Para completar la reserva, confirma tu email desde este enlace.');

        return $this->actionUrl ? $mail->action('Confirmar reserva', $this->actionUrl) : $mail;
    }

    private function cancelledMail(): MailMessage
    {
        return $this->baseMail('Tu reserva ha sido cancelada')
            ->line('Hemos registrado la cancelacion de tu reserva.');
    }

    private function changedMail(): MailMessage
    {
        return $this->baseMail('Tu reserva ha sido modificada')
            ->line('El restaurante ha actualizado los datos de tu reserva.');
    }

    private function baseMail(string $subject): MailMessage
    {
        return (new MailMessage())
            ->subject($subject)
            ->greeting("Hola {$this->reservation->customer_name}")
            ->line("Fecha: {$this->reservation->reservation_date->format('d/m/Y')}")
            ->line("Hora: ".substr((string) $this->reservation->start_time, 0, 5))
            ->line("Personas: {$this->reservation->party_size}")
            ->line('Codigo: '.$this->reservation->confirmation_code);
    }
}
