@props(['value'])

<label {{ $attributes->merge(['class' => 'label-caps']) }}>
    {{ $value ?? $slot }}
</label>
