    </div> <!-- End page-content-wrapper --->

</div> <!-- End main App --->
{{--Show all notification from react--}}
<div id="notification"></div>

{{-- Very Very Important for store the current user ID--}}
@if (!Auth::guest())
    <input type="hidden" name="cuid" id="current_user_id"  value="{{ Auth::user()->id }}">
@endif

{{--<script src="//{{ Request::getHost() }}:6001/socket.io/socket.io.js"></script>--}}
<script src="{{ asset('js/socket.io.min.js') }}"></script>
<script>
    const defaultLocale = '{{ App::getLocale() }}';
    const fallbackLocale = '{{ config('app.fallback_locale') }}';
</script>
<script src="{{ asset('js/app.js') }}"></script>
<script src="{{ asset('js/bots.js') }}"></script>

@yield('script')

</body>
</html>