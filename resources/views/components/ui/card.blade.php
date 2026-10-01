@props(['title' => null, 'subtitle' => null])

<div {{ $attributes->merge(['class' => 'ui-card']) }}>
    @if($title)
        <div class="ui-card-title">{{ $title }}</div>
    @endif
    @if($subtitle)
        <p class="ui-card-subtitle">{{ $subtitle }}</p>
    @endif
    {{ $slot }}
</div>
