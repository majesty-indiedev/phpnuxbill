</section>
</div>
{if isset($_c['CompanyFooter'])}
    <footer class="main-footer">
        {$_c['CompanyFooter']}
        <div class="pull-right">
            <a href="javascript:showPrivacy()">Privacy</a>
            &bull;
            <a href="javascript:showTaC()">T &amp; C</a>
        </div>
    </footer>
{else}
    <footer class="main-footer">
        PHPNuxBill by <a href="https://github.com/hotspotbilling/phpnuxbill" rel="nofollow noreferrer noopener"
            target="_blank">iBNuX</a>, Theme by <a href="https://adminlte.io/" rel="nofollow noreferrer noopener"
            target="_blank">AdminLTE</a>
        <div class="pull-right">
            <a href="javascript:showPrivacy()">Privacy</a>
            &bull;
            <a href="javascript:showTaC()">T &amp; C</a>
        </div>
    </footer>
{/if}
</div>


<!-- Modal -->
<div class="modal fade" id="HTMLModal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body" id="HTMLModal_konten"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">&times;</button>
            </div>
        </div>
    </div>
</div>



<script src="{$app_url}/ui/ui/scripts/jquery.min.js"></script>
<script src="{$app_url}/ui/ui/scripts/bootstrap.min.js"></script>
<script src="{$app_url}/ui/ui/scripts/adminlte.min.js"></script>

<script src="{$app_url}/ui/ui/scripts/plugins/select2.min.js"></script>
<script src="{$app_url}/ui/ui/scripts/custom.js?2025.2.5"></script>

{if isset($xfooter)}
    {$xfooter}
{/if}

{if $_c['tawkto'] != ''}
    <!--Start of Tawk.to Script-->
    <script type="text/javascript">
        var isLoggedIn = false;
        var Tawk_API = {
            onLoad: function() {
                Tawk_API.setAttributes({
                    'username'    : '{$_user['username']}',
                    'service'    : '{$_user['service_type']}',
                    'balance'    : '{$_user['balance']}',
                    'account'    : '{$_user['account_type']}',
                    'phone'    : '{$_user['phonenumber']}'
                }, function(error) {
                    console.log(error)
                });

                }
            };
            var Tawk_LoadStart = new Date();
            Tawk_API.visitor = {
                name: '{$_user['fullname']}',
                email: '{$_user['email']}',
                phone: '{$_user['phonenumber']}'
            };
            (function() {
                var s1 = document.createElement("script"),
                    s0 = document.getElementsByTagName("script")[0];
                s1.async = true;
                s1.src = 'https://embed.tawk.to/{$_c['tawkto']}';
                s1.charset = 'UTF-8';
                s1.setAttribute('crossorigin', '*');
                s0.parentNode.insertBefore(s1, s0);
            })();
        </script>
        <!--End of Tawk.to Script-->
    {/if}

    <script>
        const toggleIcon = document.getElementById('toggleIcon');
        const body = document.body;
        const savedMode = localStorage.getItem('mode');
        if (savedMode === 'dark') {
            body.classList.add('dark-mode');
            toggleIcon.textContent = '🌞';
        }

        function setMode(mode) {
            if (mode === 'dark') {
                body.classList.add('dark-mode');
                toggleIcon.textContent = '🌞';
            } else {
                body.classList.remove('dark-mode');
                toggleIcon.textContent = '🌜';
            }
        }

        toggleIcon.addEventListener('click', () => {
            if (body.classList.contains('dark-mode')) {
                setMode('light');
                localStorage.setItem('mode', 'light');
            } else {
                setMode('dark');
                localStorage.setItem('mode', 'dark');
            }
        });
    </script>


{literal}
    <script>
        var listAtts = document.querySelectorAll(`[api-get-text]`);
        listAtts.forEach(function(el) {
            $.get(el.getAttribute('api-get-text'), function(data) {
                el.innerHTML = data;
            });
        });
        $(document).ready(function() {
            var listAtts = document.querySelectorAll(`button[type="submit"]`);
            listAtts.forEach(function(el) {
                if (el.addEventListener) { // all browsers except IE before version 9
                    el.addEventListener("click", function() {
                        $(this).html(
                            `<span class="loading"></span>`
                        );
                        setTimeout(() => {
                            $(this).prop("disabled", true);
                        }, 100);
                    }, false);
                } else {
                    if (el.attachEvent) { // IE before version 9
                        el.attachEvent("click", function() {
                            $(this).html(
                                `<span class="loading"></span>`
                            );
                            setTimeout(() => {
                                $(this).prop("disabled", true);
                            }, 100);
                        });
                    }
                }
                $(function() {
                    $('[data-toggle="tooltip"]').tooltip()
                })
            });
        });

        function ask(field, text){
            // NOTE: iOS captive portal webviews (CNA) often block native confirm()/alert().
            // In that environment, bypass confirmation so critical actions (login/logout) still work.
            try {
                var ua = (navigator && navigator.userAgent) ? navigator.userAgent : '';
                if (/CaptiveNetworkSupport/i.test(ua)) {
                    return true;
                }
            } catch (e) {
                // ignore
            }

            var txt = field.innerHTML;
            if (confirm(text)) {
                setTimeout(() => {
                    field.innerHTML = field.innerHTML.replace(`<span class="loading"></span>`, txt);
                    field.removeAttribute("disabled");
                }, 5000);
                return true;
            } else {
                setTimeout(() => {
                    field.innerHTML = field.innerHTML.replace(`<span class="loading"></span>`, txt);
                    field.removeAttribute("disabled");
                }, 500);
                return false;
            }
        }

        function setCookie(name, value, days) {
            var expires = "";
            if (days) {
                var date = new Date();
                date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                expires = "; expires=" + date.toUTCString();
            }
            document.cookie = name + "=" + (value || "") + expires + "; path=/";
        }

        function getCookie(name) {
            var nameEQ = name + "=";
            var ca = document.cookie.split(';');
            for (var i = 0; i < ca.length; i++) {
                var c = ca[i];
                while (c.charAt(0) == ' ') c = c.substring(1, c.length);
                if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
            }
            return null;
        }

        // Dashboard: keep user on page while hotspot login/logout runs in the background worker.
        // - Click button -> enqueue_json -> spinner on button -> poll status_json -> refresh button state.
        $(document).on('click', 'a.js-hotspot-action', function(e) {
            // If JS is unavailable, the link will navigate to the captive-friendly page.
            e.preventDefault();

            var $a = $(this);
            if ($a.data('busy')) return;
            $a.data('busy', true);

            var originalHtml = $a.html();
            var enqueueUrl = $a.attr('data-enqueue-json');
            var refreshUrl = $a.attr('data-refresh-url');
            var op = ($a.attr('data-op') || '').toLowerCase();

            // Spinner + disable
            var busyText = (op === 'logout') ? ' Disconnecting…' : ' Granting access…';
            $a.html('<span class="loading"></span>' + busyText);
            // Make it truly non-interactive (anchors don't support disabled natively)
            $a.addClass('disabled')
              .attr('aria-disabled', 'true')
              .attr('tabindex', '-1')
              .css('pointer-events', 'none');

            function restore() {
                $a.html(originalHtml);
                $a.removeClass('disabled')
                  .removeAttr('aria-disabled')
                  .removeAttr('tabindex')
                  .css('pointer-events', '');
                $a.data('busy', false);
            }

            function refreshButton() {
                if (!refreshUrl) {
                    restore();
                    return;
                }
                $.get(refreshUrl, function(html) {
                    // Replace the whole container if present, otherwise just replace the link
                    var rid = $a.attr('data-recharge-id');
                    var $container = rid ? $('#login_status_' + rid) : null;
                    if ($container && $container.length) {
                        $container.html(html);
                    } else {
                        $a.replaceWith(html);
                    }
                }).always(function() {
                    // In case the refresh fails, at least restore the button.
                    restore();
                });
            }

            function flipButtonState(toState) {
                // toState: 'online' or 'offline'
                var rid = $a.attr('data-recharge-id');
                var $container = rid ? $('#login_status_' + rid) : null;
                if (!$container || !$container.length) {
                    restore();
                    return;
                }

                var hrefOnline = $a.attr('data-href-online') || '';
                var hrefOffline = $a.attr('data-href-offline') || '';
                var enqueueOnline = $a.attr('data-enqueue-online') || '';
                var enqueueOffline = $a.attr('data-enqueue-offline') || '';
                var textOnline = $a.attr('data-text-online') || 'Online';
                var textOffline = $a.attr('data-text-offline') || 'Offline';

                if (toState === 'online') {
                    $container.html(
                        '<a href="' + hrefOnline + '" ' +
                        'data-enqueue-json="' + enqueueOnline + '" ' +
                        'data-refresh-url="' + refreshUrl + '" ' +
                        'data-recharge-id="' + rid + '" ' +
                        'data-op="logout" ' +
                        'data-href-online="' + hrefOnline + '" ' +
                        'data-href-offline="' + hrefOffline + '" ' +
                        'data-enqueue-online="' + enqueueOnline + '" ' +
                        'data-enqueue-offline="' + enqueueOffline + '" ' +
                        'data-text-online="' + $('<div/>').text(textOnline).html() + '" ' +
                        'data-text-offline="' + $('<div/>').text(textOffline).html() + '" ' +
                        'class="btn btn-success btn-xs btn-block js-hotspot-action">' +
                        $('<div/>').text(textOnline).html() +
                        '</a>'
                    );
                } else {
                    $container.html(
                        '<a href="' + hrefOffline + '" ' +
                        'data-enqueue-json="' + enqueueOffline + '" ' +
                        'data-refresh-url="' + refreshUrl + '" ' +
                        'data-recharge-id="' + rid + '" ' +
                        'data-op="login" ' +
                        'data-href-online="' + hrefOnline + '" ' +
                        'data-href-offline="' + hrefOffline + '" ' +
                        'data-enqueue-online="' + enqueueOnline + '" ' +
                        'data-enqueue-offline="' + enqueueOffline + '" ' +
                        'data-text-online="' + $('<div/>').text(textOnline).html() + '" ' +
                        'data-text-offline="' + $('<div/>').text(textOffline).html() + '" ' +
                        'class="btn btn-danger btn-xs btn-block js-hotspot-action">' +
                        $('<div/>').text(textOffline).html() +
                        '</a>'
                    );
                }
            }

            function pollJobStatus(statusUrl, startedAt) {
                if (Date.now() - startedAt > 45000) {
                    // Final fallback: refresh from server if it took too long
                    refreshButton();
                    return;
                }
                $.ajax({
                    url: statusUrl + (statusUrl.indexOf('?') >= 0 ? '&' : '?') + 't=' + Date.now(),
                    cache: false,
                    dataType: 'json',
                    timeout: 3000,
                    success: function(data) {
                        if (!data || !data.ok) {
                            setTimeout(function() { pollJobStatus(statusUrl, startedAt); }, 800);
                            return;
                        }
                        if (data.status === 'success') {
                            // Flip immediately (no RouterOS API check needed)
                            if (op === 'login') {
                                flipButtonState('online');
                                // Reload once after CONNECT so the OS/browser re-evaluates connectivity immediately.
                                try {
                                    var now = Date.now();
                                    var lastKick = 0;
                                    try {
                                        lastKick = parseInt(sessionStorage.getItem('nux_connect_kick_ts') || '0', 10) || 0;
                                    } catch (e) {
                                        lastKick = window.__nux_connect_kick_ts || 0;
                                    }
                                    if (!lastKick || (now - lastKick) > 60000) {
                                        try { sessionStorage.setItem('nux_connect_kick_ts', String(now)); } catch (e) { window.__nux_connect_kick_ts = now; }
                                        setTimeout(function() { try { window.location.reload(); } catch (e) {} }, 800);
                                    }
                                } catch (e) {}
                            } else {
                                // Logout: just flip (no reload, per your request)
                                flipButtonState('offline');
                            }
                            return;
                        }
                        if (data.status === 'failed') {
                            restore();
                            return;
                        }
                        setTimeout(function() { pollJobStatus(statusUrl, startedAt); }, 800);
                    },
                    error: function() {
                        setTimeout(function() { pollJobStatus(statusUrl, startedAt); }, 800);
                    }
                });
            }

            // Enqueue the job, then poll its status.
            $.ajax({
                url: enqueueUrl + (enqueueUrl.indexOf('?') >= 0 ? '&' : '?') + 't=' + Date.now(),
                cache: false,
                dataType: 'json',
                timeout: 8000,
                success: function(data) {
                    if (!data || !data.ok || !data.status_url) {
                        restore();
                        return;
                    }
                    // Poll the job file status (fast) and flip UI immediately on success.
                    pollJobStatus(data.status_url, Date.now());
                },
                error: function() {
                    // If enqueue_json fails, fall back to normal navigation (best effort).
                    $a.data('busy', false);
                    window.location.href = $a.attr('href');
                }
            });
        });
    </script>
{/literal}
<script>
setCookie('user_language', '{$user_language}', 365);
</script>
</body>

</html>
