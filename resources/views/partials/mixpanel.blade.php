<script src="{{ asset('genealabs-laravel-mixpanel/js/mixpanel.js') }}"></script>
<script>
    if ("{{ config('services.mixpanel.token') }}" == "") {
        console.error('Mixpanel for Laravel: You must declare the env variable MIXPANEL_TOKEN!');
    }

    mixpanel.init("{{ config('services.mixpanel.token') }}", {
        api_host: "https://{{ config('services.mixpanel.host', 'api-eu.mixpanel.com') }}"
    });
</script>
