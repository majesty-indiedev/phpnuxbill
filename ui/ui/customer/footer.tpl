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

            function pollUiState(startedAt) {
                // Stop after ~45s; don't spin forever.
                if (Date.now() - startedAt > 45000) {
                    refreshButton();
                    return;
                }
                if (!refreshUrl) {
                    restore();
                    return;
                }
                $.ajax({
                    url: refreshUrl + (refreshUrl.indexOf('?') >= 0 ? '&' : '?') + 't=' + Date.now(),
                    cache: false,
                    timeout: 8000,
                    success: function(html) {
                        var rid = $a.attr('data-recharge-id');
                        var $container = rid ? $('#login_status_' + rid) : null;
                        if ($container && $container.length) {
                            $container.html(html);
                        } else {
                            $a.replaceWith(html);
                        }

                        // Detect whether we reached the expected final state.
                        // - login final: shows Logout (btn-success)
                        // - logout final: shows Login now (btn-danger)
                        var s = (html || '').toString();
                        if (op === 'login') {
                            if (s.indexOf('btn-success') !== -1 || s.indexOf('Logout') !== -1) {
                                // iOS captive portal sometimes needs a connectivity re-check to "unlock" internet,
                                // even after the router granted access. Kick the OS re-check once.
                                try {
                                    var ua = (navigator && navigator.userAgent) ? navigator.userAgent : '';
                                    if (/CaptiveNetworkSupport/i.test(ua)) {
                                        try {
                                            if (!sessionStorage.getItem('nux_cna_kick')) {
                                                sessionStorage.setItem('nux_cna_kick', '1');
                                                // Background probe (does not navigate away)
                                                var img = new Image();
                                                img.src = 'http://captive.apple.com/hotspot-detect.html?t=' + Date.now();
                                                // One-time reload inside CNA so iOS reevaluates the captive state
                                                setTimeout(function() {
                                                    try { window.location.reload(); } catch (e) {}
                                                }, 1200);
                                            }
                                        } catch (e) {
                                            // If sessionStorage is blocked, still try the probe + reload once.
                                            var img2 = new Image();
                                            img2.src = 'http://captive.apple.com/hotspot-detect.html?t=' + Date.now();
                                            setTimeout(function() {
                                                try { window.location.reload(); } catch (e) {}
                                            }, 1200);
                                        }
                                    }
                                } catch (e) {}
                                return;
                            }
                        } else if (op === 'logout') {
                            if (s.indexOf('btn-danger') !== -1 || s.indexOf('Login now') !== -1) return;
                        }
                        setTimeout(function() { pollUiState(startedAt); }, 2000);
                    },
                    error: function() {
                        // If router/API check fails intermittently, keep trying a few times.
                        setTimeout(function() { pollUiState(startedAt); }, 2000);
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
                    if (!data || !data.ok) {
                        restore();
                        return;
                    }
                    // Poll the UI state (source of truth: router online check) until the button flips.
                    pollUiState(Date.now());
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
