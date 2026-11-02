<script>
    (function () {
        function arm() {
            if (!window.Livewire || !Livewire.hook || window.__mbSessionGuard) return;
            window.__mbSessionGuard = true;

            Livewire.hook('request', function ({ fail, succeed }) {
                succeed(function () {
                    try { sessionStorage.removeItem('mb-session-refresh'); } catch (e) {}
                });

                fail(function ({ status, preventDefault }) {
                    if (status !== 419) return;
                    preventDefault();

                    var refreshes = 0;
                    try { refreshes = Number(sessionStorage.getItem('mb-session-refresh') || 0); } catch (e) {}
                    if (refreshes >= 1) return;

                    try { sessionStorage.setItem('mb-session-refresh', '1'); } catch (e) {}
                    window.location.reload();
                });
            });
        }

        document.addEventListener('livewire:init', arm);
        arm();
    })();
</script>
