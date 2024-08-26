@if (Session::has('success'))
    <script>
        let p = '{{ app()->getLocale() == 'ar' ? 'bottomLeft' : 'bottomRight' }}';

        iziToast.success({
            timeout: 3000,
            theme: 'light',
            position: 'topCenter',
            transitionIn: 'flipInX',
            transitionOut: 'flipOutX',
            title: '{{ __('lang.done') }}',
            message: '{{ session('success') }}',
        });
    </script>
@endif
