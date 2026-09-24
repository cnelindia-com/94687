@php
    $gtmEvents = \App\Services\Analytics\GoogleTagManager::pull();
    $gtmEnabled = \App\Services\Analytics\GoogleTagManager::enabled();
@endphp

<script>
    window.dataLayer = window.dataLayer || [];

    window.pushGtmEvents = function(events) {
        if (!Array.isArray(events) || !events.length) {
            return;
        }

        events.forEach(function(eventPayload) {
            window.dataLayer.push(eventPayload);
        });
    };

    window.flushGtmEvents = async function() {
        @if (auth()->check())
        try {
            const response = await fetch(@json(route('dashboard.user.gtm.events')), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            });

            if (!response.ok) {
                return;
            }

            const data = await response.json();
            window.pushGtmEvents(data.events || []);
        } catch (error) {
            // Ignore polling errors.
        }
        @endif
    };

    @if (! empty($gtmEvents))
        window.pushGtmEvents(@json($gtmEvents));
    @endif

    @if ($gtmEnabled && auth()->check())
        // Image/video complete via AJAX — poll pending events without full page reload.
        setInterval(function() {
            window.flushGtmEvents();
        }, 4000);

        document.addEventListener('visibilitychange', function() {
            if (!document.hidden) {
                window.flushGtmEvents();
            }
        });
    @endif
</script>
