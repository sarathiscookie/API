@component('mail::message')
    # Introduction

    The body of supplier message.

    @isset($supplier)
        | {{ $supplier['name'] }} | {{ $supplier['email'] }} | {{ $supplier['phone'] }} |
    @endisset

    Thanks,<br>
    {{ config('app.name') }}
@endcomponent
