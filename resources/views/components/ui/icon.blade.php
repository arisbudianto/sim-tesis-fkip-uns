@props(['name'])

<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
     stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" {{ $attributes }}>
    @switch($name)
        @case('home')
            <path d="M4 10.5 12 4l8 6.5" />
            <path d="M6 9.5V19a1 1 0 0 0 1 1h3.5v-5h3V20H17a1 1 0 0 0 1-1V9.5" />
            @break

        @case('users')
            <circle cx="8.5" cy="8" r="3" />
            <circle cx="16" cy="9" r="2.4" />
            <path d="M3.5 19c0-2.8 2.2-5 5-5s5 2.2 5 5" />
            <path d="M13.5 19c0-2-1-3.8-2.6-4.7a4 4 0 0 1 5.1.7c1 1 1.5 2.4 1.5 4" />
            @break

        @case('document-text')
            <rect x="5.5" y="3.5" width="13" height="17" rx="1.6" />
            <path d="M8.5 8.5h7M8.5 12h7M8.5 15.5h4.5" />
            @break

        @case('chart-bar')
            <rect x="4" y="13" width="3.2" height="7" rx="0.6" />
            <rect x="10.4" y="8.5" width="3.2" height="11.5" rx="0.6" />
            <rect x="16.8" y="4.5" width="3.2" height="15.5" rx="0.6" />
            @break

        @case('academic-cap')
            <path d="M12 5 2.5 9.5 12 14l9.5-4.5L12 5Z" />
            <path d="M6.5 11.8V16c0 1.4 2.5 2.5 5.5 2.5s5.5-1.1 5.5-2.5v-4.2" />
            <path d="M21 9.5v5" />
            @break

        @case('check-badge')
            <circle cx="12" cy="12" r="8.5" />
            <path d="M8.3 12.3l2.4 2.4 5-5.4" />
            @break

        @case('clipboard-list')
            <rect x="6" y="4.5" width="12" height="16" rx="1.6" />
            <rect x="9" y="3" width="6" height="3" rx="1" />
            <path d="M9 10h6M9 13h6M9 16h4" />
            @break

        @case('circle-stack')
            <ellipse cx="12" cy="6" rx="6.5" ry="2.3" />
            <path d="M5.5 6v5.5c0 1.3 2.9 2.3 6.5 2.3s6.5-1 6.5-2.3V6" />
            <path d="M5.5 11.5V17c0 1.3 2.9 2.3 6.5 2.3s6.5-1 6.5-2.3v-5.5" />
            @break

        @case('table-cells')
            <rect x="3.5" y="4.5" width="17" height="15" rx="1.6" />
            <path d="M3.5 9.5h17M9.5 9.5V19.5M15 9.5V19.5" />
            @break

        @case('globe-alt')
            <circle cx="12" cy="12" r="8.5" />
            <path d="M3.5 12h17M12 3.5c2.4 2.4 3.7 5.3 3.7 8.5s-1.3 6.1-3.7 8.5c-2.4-2.4-3.7-5.3-3.7-8.5S9.6 5.9 12 3.5Z" />
            @break

        @case('book-open')
            <path d="M12 6.5c-1.8-1.2-4.4-1.6-7-1.2v12c2.6-.4 5.2 0 7 1.2 1.8-1.2 4.4-1.6 7-1.2v-12c-2.6-.4-5.2 0-7 1.2Z" />
            <path d="M12 6.5V18.7" />
            @break

        @case('user-circle')
            <circle cx="12" cy="12" r="8.5" />
            <circle cx="12" cy="9.8" r="2.6" />
            <path d="M6.5 18c1-2.3 3.1-3.6 5.5-3.6s4.5 1.3 5.5 3.6" />
            @break

        @case('logout')
            <path d="M9 4.5H6a1.5 1.5 0 0 0-1.5 1.5v12A1.5 1.5 0 0 0 6 19.5h3" />
            <path d="M13.5 8.5 17 12l-3.5 3.5" />
            <path d="M17 12H9.5" />
            @break

        @case('bars-3')
            <path d="M4 6.5h16M4 12h16M4 17.5h16" />
            @break

        @case('x-mark')
            <path d="M6 6l12 12M18 6 6 18" />
            @break

        @case('pencil-square')
            <path d="M14.5 5.5 18.5 9.5 8 20H4v-4L14.5 5.5Z" />
            <path d="M12.5 7.5l4 4" />
            @break

        @case('lock-closed')
            <rect x="5.5" y="10.5" width="13" height="9" rx="1.6" />
            <path d="M8 10.5V8a4 4 0 0 1 8 0v2.5" />
            <circle cx="12" cy="14.8" r="1.3" />
            @break
    @endswitch
</svg>
