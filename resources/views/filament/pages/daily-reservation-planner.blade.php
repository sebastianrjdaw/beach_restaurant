<x-filament-panels::page>
    <div class="space-y-6" wire:poll.visible.5s="refreshPlanner">
        <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div class="w-full lg:max-w-xl">
                    {{ $this->form }}
                </div>
                <div class="flex flex-wrap gap-2">
                    <button
                        type="button"
                        wire:click="refreshPlanner"
                        class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50 dark:border-white/10 dark:text-gray-200 dark:hover:bg-white/5"
                    >
                        Actualizar
                    </button>
                    <a
                        class="inline-flex items-center justify-center rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-500"
                        href="{{ $this->createReservationUrl() }}"
                    >
                        Nueva reserva
                    </a>
                </div>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <p class="text-sm text-gray-500 dark:text-gray-400">Reservas activas</p>
                <p class="mt-1 text-2xl font-semibold text-gray-950 dark:text-white">{{ $this->totals['reservations'] }}</p>
            </div>
            <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <p class="text-sm text-gray-500 dark:text-gray-400">Comensales previstos</p>
                <p class="mt-1 text-2xl font-semibold text-gray-950 dark:text-white">{{ $this->totals['guests'] }}</p>
            </div>
            <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <p class="text-sm text-gray-500 dark:text-gray-400">Mesas asignadas</p>
                <p class="mt-1 text-2xl font-semibold text-gray-950 dark:text-white">{{ $this->totals['tables'] }}</p>
            </div>
        </div>

        @if ($this->plan !== [])
            <div class="grid gap-5 lg:grid-cols-2">
                @foreach ($this->plan as $shift => $slots)
                    <section class="space-y-3">
                        <div class="rounded-xl bg-gray-900 px-4 py-3 text-white shadow-sm dark:bg-white dark:text-gray-950">
                            <h2 class="text-lg font-semibold">{{ $shift }}</h2>
                            <p class="text-sm opacity-75">{{ count($slots) }} turnos registrados</p>
                        </div>

                        <div class="grid gap-4">
                    @foreach ($slots as $slot)
                        <article class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <p class="text-xl font-semibold text-gray-950 dark:text-white">{{ $slot['time'] }}</p>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Hasta {{ $slot['ends_at'] }}</p>
                                </div>
                                <div class="grid grid-cols-3 gap-2 text-center text-sm sm:min-w-72">
                                    <div class="rounded-lg bg-gray-50 p-2 dark:bg-white/5">
                                        <p class="font-semibold text-gray-950 dark:text-white">{{ $slot['reservations_count'] }}@if($slot['max_reservations']) / {{ $slot['max_reservations'] }}@endif</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Reservas</p>
                                    </div>
                                    <div class="rounded-lg bg-gray-50 p-2 dark:bg-white/5">
                                        <p class="font-semibold text-gray-950 dark:text-white">{{ $slot['guests_count'] }}@if($slot['max_guests']) / {{ $slot['max_guests'] }}@endif</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Comensales</p>
                                    </div>
                                    <div class="rounded-lg bg-gray-50 p-2 dark:bg-white/5">
                                        <p class="font-semibold text-gray-950 dark:text-white">{{ $slot['tables_count'] }} / {{ $slot['tables_capacity'] }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Mesas/cap.</p>
                                    </div>
                                </div>
                            </div>

                            @if ($slot['is_over_reservations_limit'] || $slot['is_over_guests_limit'])
                                <div class="mt-4 rounded-lg bg-warning-50 px-3 py-2 text-sm text-warning-800 ring-1 ring-warning-200 dark:bg-warning-400/10 dark:text-warning-200 dark:ring-warning-400/30">
                                    Este turno supera los limites operativos configurados.
                                </div>
                            @endif

                            <div class="mt-4 space-y-3">
                                @forelse ($slot['reservations'] as $reservation)
                                    <div class="rounded-lg border border-gray-200 p-3 dark:border-white/10">
                                        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                            <div>
                                                <p class="font-semibold text-gray-950 dark:text-white">
                                                    {{ $reservation->customer_name }}
                                                    <span class="text-sm font-normal text-gray-500 dark:text-gray-400">· {{ $reservation->party_size }} pax</span>
                                                </p>
                                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                                    {{ $reservation->customer_phone ?: 'Sin telefono' }}
                                                    @if ($reservation->customer_email)
                                                        · {{ $reservation->customer_email }}
                                                    @endif
                                                </p>
                                            </div>
                                            <span class="inline-flex w-fit rounded-full bg-gray-100 px-2 py-1 text-xs font-semibold text-gray-700 dark:bg-white/10 dark:text-gray-200">
                                                {{ str_replace('_', ' ', $reservation->status->value) }}
                                            </span>
                                        </div>

                                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                                            Mesas:
                                            {{ $reservation->tables->isNotEmpty() ? $reservation->tables->pluck('name')->join(', ') : 'sin asignar' }}
                                        </p>

                                        @if ($reservation->comments)
                                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ $reservation->comments }}</p>
                                        @endif

                                        <div class="mt-3 flex flex-wrap gap-2">
                                            @if ($reservation->customer_phone)
                                                <a
                                                    href="tel:{{ preg_replace('/\s+/', '', $reservation->customer_phone) }}"
                                                    class="rounded-lg bg-primary-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-primary-500"
                                                >
                                                    Llamar
                                                </a>
                                            @endif
                                            <button
                                                type="button"
                                                wire:click="openReservationModal({{ $reservation->id }})"
                                                class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-white/10 dark:text-gray-200 dark:hover:bg-white/5"
                                            >
                                                Editar
                                            </button>
                                        </div>
                                    </div>
                                @empty
                                    <p class="rounded-lg bg-gray-50 p-3 text-sm text-gray-500 dark:bg-white/5 dark:text-gray-400">
                                        Sin reservas para este turno.
                                    </p>
                                @endforelse
                            </div>
                        </article>
                    @endforeach
                        </div>
                    </section>
                @endforeach
            </div>
        @else
            <div class="rounded-xl bg-white p-6 text-sm text-gray-500 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:text-gray-400 dark:ring-white/10">
                No hay turnos registrados para este dia.
            </div>
        @endif

        @if ($this->editingReservation)
            @php($editingReservation = $this->editingReservation)
            <div
                class="fixed inset-0 z-50 bg-gray-950/80"
                role="dialog"
                aria-modal="true"
                style="display: flex; align-items: center; justify-content: center; padding: 24px;"
            >
                <div
                    class="bg-white shadow-2xl ring-1 ring-gray-950/10 dark:bg-gray-900 dark:ring-white/10"
                    style="width: min(760px, calc(100vw - 32px)); max-height: calc(100vh - 48px); overflow: hidden; border-radius: 18px;"
                >
                    <div
                        class="border-b border-gray-200 dark:border-white/10"
                        style="display: flex; align-items: flex-start; justify-content: space-between; gap: 20px; padding: 22px 24px; background: linear-gradient(135deg, #0E3A47 0%, #355E53 100%);"
                    >
                        <div style="min-width: 0;">
                            <p class="text-xs font-semibold uppercase tracking-wide" style="color: #D8C3A5;">Editar reserva</p>
                            <h3 class="mt-1 text-2xl font-semibold" style="color: #ffffff;">{{ $editingReservation->customer_name }}</h3>
                            <div class="mt-3 flex flex-wrap gap-2 text-sm">
                                <span class="rounded-full px-3 py-1 font-semibold" style="background: rgba(255, 255, 255, 0.14); color: #ffffff;">
                                    {{ $editingReservation->reservation_date->format('d/m/Y') }}
                                </span>
                                <span class="rounded-full px-3 py-1 font-semibold" style="background: rgba(255, 255, 255, 0.14); color: #ffffff;">
                                    {{ substr((string) $editingReservation->start_time, 0, 5) }}
                                </span>
                                <span class="rounded-full px-3 py-1 font-semibold" style="background: rgba(255, 255, 255, 0.14); color: #ffffff;">
                                    {{ $editingReservation->party_size }} pax
                                </span>
                                <span class="rounded-full px-3 py-1 font-semibold" style="background: #F7F3EC; color: #0E3A47;">
                                    {{ str_replace('_', ' ', $editingReservation->status->value) }}
                                </span>
                            </div>
                        </div>
                        <button
                            type="button"
                            wire:click="closeReservationModal"
                            class="rounded-lg px-3 py-1.5 text-sm font-semibold transition hover:bg-white/20"
                            style="border: 1px solid rgba(255, 255, 255, 0.35); color: #ffffff;"
                        >
                            Cerrar
                        </button>
                    </div>

                    <div style="max-height: calc(100vh - 190px); overflow-y: auto;">
                        <div class="space-y-5" style="padding: 24px;">
                            <div
                                class="text-sm"
                                style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 12px;"
                            >
                                <div class="rounded-xl bg-gray-50 p-4 dark:bg-white/5">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Telefono</p>
                                    <p class="mt-1 font-semibold text-gray-950 dark:text-white">{{ $editingReservation->customer_phone ?: 'Sin telefono' }}</p>
                                </div>
                                <div class="rounded-xl bg-gray-50 p-4 dark:bg-white/5">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Email</p>
                                    <p class="mt-1 break-all font-semibold text-gray-950 dark:text-white">{{ $editingReservation->customer_email ?: 'Sin email' }}</p>
                                </div>
                                <div class="rounded-xl bg-gray-50 p-4 dark:bg-white/5">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Mesas</p>
                                    <p class="mt-1 font-semibold text-gray-950 dark:text-white">{{ $editingReservation->tables->isNotEmpty() ? $editingReservation->tables->pluck('name')->join(', ') : 'sin asignar' }}</p>
                                </div>
                            </div>

                            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900">
                                <div class="flex flex-col gap-1">
                                    <p class="text-base font-semibold text-gray-950 dark:text-white">Mover reserva</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Cambia fecha u hora y decide si quieres avisar al cliente.</p>
                                </div>

                                <div class="mt-4" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px;">
                                    <label class="grid gap-1 text-sm font-medium text-gray-700 dark:text-gray-200">
                                        Nueva fecha
                                        <input
                                            type="date"
                                            wire:model="moves.{{ $editingReservation->id }}.date"
                                            class="rounded-lg border-gray-300 text-sm dark:border-white/10 dark:bg-gray-900"
                                            style="width: 100%;"
                                        />
                                    </label>
                                    <label class="grid gap-1 text-sm font-medium text-gray-700 dark:text-gray-200">
                                        Nueva hora
                                        <input
                                            type="time"
                                            wire:model="moves.{{ $editingReservation->id }}.time"
                                            class="rounded-lg border-gray-300 text-sm dark:border-white/10 dark:bg-gray-900"
                                            style="width: 100%;"
                                        />
                                    </label>
                                </div>

                                <div class="mt-4 flex flex-wrap gap-3 text-sm text-gray-700 dark:text-gray-200">
                                    <label class="inline-flex items-center gap-2 rounded-lg bg-gray-50 px-3 py-2 dark:bg-white/5">
                                        <input type="checkbox" wire:model="moves.{{ $editingReservation->id }}.notify_email" class="rounded border-gray-300" />
                                        Enviar cambio por email
                                    </label>
                                    <label class="inline-flex items-center gap-2 rounded-lg bg-gray-50 px-3 py-2 opacity-70 dark:bg-white/5">
                                        <input type="checkbox" wire:model="moves.{{ $editingReservation->id }}.notify_whatsapp" class="rounded border-gray-300" />
                                        WhatsApp futuro
                                    </label>
                                </div>

                                <button
                                    type="button"
                                    wire:click="moveReservation({{ $editingReservation->id }})"
                                    class="mt-4 rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-500"
                                >
                                    Guardar cambio
                                </button>
                            </div>
                        </div>

                        <div class="border-t border-gray-200 bg-gray-50 px-6 py-5 dark:border-white/10 dark:bg-white/5">
                            <div class="mb-3 flex items-center justify-between gap-3">
                                <p class="text-sm font-semibold text-gray-950 dark:text-white">Operaciones</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Acciones rápidas sobre esta reserva</p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                @if (in_array($editingReservation->status, [\App\Enums\ReservationStatus::Pending, \App\Enums\ReservationStatus::PendingEmailVerification], true))
                                    <button type="button" wire:click="confirmReservation({{ $editingReservation->id }})" class="rounded-lg bg-success-600 px-4 py-2 text-sm font-semibold text-white hover:bg-success-500">
                                        Confirmar
                                    </button>
                                @endif
                                @if ($editingReservation->customer_phone)
                                    <a href="tel:{{ preg_replace('/\s+/', '', $editingReservation->customer_phone) }}" class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-500">
                                        Llamar
                                    </a>
                                @endif
                                @if (in_array($editingReservation->status, [\App\Enums\ReservationStatus::Pending, \App\Enums\ReservationStatus::PendingEmailVerification, \App\Enums\ReservationStatus::Confirmed], true))
                                    <button type="button" wire:click="completeReservation({{ $editingReservation->id }})" class="rounded-lg bg-gray-600 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-500">
                                        Completada
                                    </button>
                                    <button type="button" wire:click="markNoShow({{ $editingReservation->id }})" class="rounded-lg bg-danger-600 px-4 py-2 text-sm font-semibold text-white hover:bg-danger-500">
                                        No-show
                                    </button>
                                    <button type="button" wire:click="cancelReservation({{ $editingReservation->id }})" wire:confirm="Cancelar esta reserva?" class="rounded-lg border border-danger-300 px-4 py-2 text-sm font-semibold text-danger-700 hover:bg-danger-50 dark:border-danger-500/40 dark:text-danger-300 dark:hover:bg-danger-500/10">
                                        Cancelar
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
