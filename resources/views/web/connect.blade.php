@extends('layouts.app')
@section('stylesheet')
    {{-- Style for code highlighting --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/9.15.10/styles/a11y-dark.min.css" />
@endsection
@include('elements.dashboard',['sidebar'=>true,'navbar'=>true])

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="dashboard-widget">
                <h3> {{__('pages.webConnect.mainHeading')}} </h3>
                <p> {{__('pages.webConnect.instruction')}} </p>

{{-- This is syntex highlighted code, please don't indent it --}}
{{-- ===================== --}}
<pre>
    <code class="hljs {{ $code->language }}">{!! $code->value !!}</code>
</pre>
{{-- ===================== --}}

            </div>
        </div>
    </div>
</div>
@endsection