
@include('elements.header',['sidebar'=>$sidebar])

@if(isset($navbar) && ($navbar==true) )
    @section('navbar')
        @if(isset($navbar_type))
            @include('elements.'.$navbar_type,['sidebar'=>$sidebar])
        @else
            @include('elements.navbar',['sidebar'=>$sidebar])
        @endif
    @endsection
@endif