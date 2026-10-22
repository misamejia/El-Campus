@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'border-gray-300 focus:border-verde-600 focus:ring-verde-600 rounded-md shadow-sm']) !!}>
