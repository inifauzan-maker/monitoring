@switch($tipe ?? 'info')
    @case('success')
        <svg class="icon {{ $kelas ?? '' }}" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
            <path d="M5 12l5 5l10 -10" />
        </svg>
        @break

    @case('warning')
        <svg class="icon {{ $kelas ?? '' }}" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
            <path d="M12 9v4" />
            <path d="M12 16v.01" />
            <path d="M5 19h14a1 1 0 0 0 .8 -1.6l-7 -9.33a1 1 0 0 0 -1.6 0l-7 9.33a1 1 0 0 0 .8 1.6" />
        </svg>
        @break

    @case('danger')
        <svg class="icon {{ $kelas ?? '' }}" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
            <path d="M12 9v4" />
            <path d="M12 16v.01" />
            <path d="M5.07 19h13.86a2 2 0 0 0 1.74 -3l-6.93 -12a2 2 0 0 0 -3.48 0l-6.93 12a2 2 0 0 0 1.74 3" />
        </svg>
        @break

    @default
        <svg class="icon {{ $kelas ?? '' }}" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
            <path d="M12 9h.01" />
            <path d="M11 12h1v4h1" />
            <path d="M12 3a9 9 0 1 0 9 9a9 9 0 0 0 -9 -9" />
        </svg>
@endswitch
