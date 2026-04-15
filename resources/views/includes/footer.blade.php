@if (!in_array(Route::currentRouteName(), $allowedRoutesForDBcss))
    <footer id="footer" class="footer home-footer position-relative w-100">
        <div class="copyright">
        @php $subDomain = $_SERVER['SERVER_NAME']; @endphp
        © Copyright <strong><span> {{ $subDomain }}</span></strong>. All Rights Reserved
        </div>
    </footer>
@endif
