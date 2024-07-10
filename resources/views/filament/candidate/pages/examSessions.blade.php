    <div id="form" style="margin-top: 5%;">
        <p style="margin-bottom: 2%;">Remember not to leave this tab once the exam has started.</p>
        {{ $this->table }}
    </div>

{{--     <script>
        let confirmation = false;

        function setupModalListener() {
            const modal = document.getElementById('modal');
            if (modal) {
                document.addEventListener("visibilitychange", function() {
                    if (document.hidden && !confirmation) {
                        const alert = confirm(
                            'You must ask the administrator for authorization to enter the exam.');
                        confirmation = true;
                        if (alert) {
                            window.location.href = '/candidate/forze-logout/{{ $this->record->id }}';
                        }
                    }
                });
            }
        }

        document.addEventListener('DOMContentLoaded', setupModalListener);

        setInterval(setupModalListener, 1000);
    </script> --}}
