@props(['name' => 'wallet'])
<svg {{ $attributes->merge(['class' => 'icon']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    @switch($name)
        @case('bank')<path d="m3 9 9-6 9 6H3Z"/><path d="M5 10v8M10 10v8M14 10v8M19 10v8M3 21h18M4 18h16"/>@break
        @case('deposit')<path d="M12 3v12m-5-5 5 5 5-5"/><path d="M4 15v4a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-4"/>@break
        @case('withdrawal')<path d="M12 15V3m-5 5 5-5 5 5"/><path d="M4 15v4a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-4"/>@break
        @case('plus')<path d="M12 5v14M5 12h14"/>@break
        @case('edit')<path d="m15 5 4 4M4 20l4-1L20 7a2.8 2.8 0 0 0-4-4L4 15v5Z"/>@break
        @case('history')<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>@break
        @case('receipt')<path d="M5 3h14v18l-3-2-4 2-4-2-3 2V3Z"/><path d="M9 7h6M9 11h6M9 15h3"/>@break
        @case('filter')<path d="M4 5h16l-6 7v7l-4 2v-9L4 5Z"/>@break
        @default<rect x="3" y="5" width="18" height="15" rx="3"/><path d="M3 9h18M21 13h-5a2 2 0 0 0 0 4h5M17 15h.01"/>
    @endswitch
</svg>
