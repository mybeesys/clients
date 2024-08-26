@if (Session::has('error'))
    <script>
        let p = '{{ app()->getLocale() == 'ar' ? 'bottomLeft' : 'bottomRight' }}';

        iziToast.error({
            timeout: 120000,
            theme: 'light',
            position: 'topCenter',
            transitionIn: 'flipInX',
            transitionOut: 'flipOutX',
            title: '{{ __('lang.error') }}',
            message: '{{ session('error') }}',
        });
    </script>
@endif
