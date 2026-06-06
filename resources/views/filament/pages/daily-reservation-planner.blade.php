<x-filament-panels::page>
    <div class="space-y-6">
        <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            {{ $this->form }}
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

        @forelse ($this->plan as $shift => $slots)
            <section class="space-y-3">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <h2 class="text-lg font-semibold text-gray-950 dark:text-white">{{ $shift }}</h2>
                    <a
                        class="inline-flex items-center justify-center rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-500"
                        href="{{ $this->createReservationUrl() }}"
                    >
                        Nueva reserva
                    </a>
                </div>

                <div class="grid gap-4 xl:grid-cols-2">
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
        @empty
            <div class="rounded-xl bg-white p-6 text-sm text-gray-500 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:text-gray-400 dark:ring-white/10">
                No hay turnos registrados para este dia.
            </div>
        @endforelse
    </div>
</x-filament-panels::page>
