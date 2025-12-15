<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>{$_title} - {$_c['CompanyName']}</title>
    <link rel="shortcut icon" href="{$app_url}/ui/ui/images/logo.png" type="image/x-icon" />
    <link rel="stylesheet" href="{$app_url}/ui/ui/styles/bootstrap.min.css">
    <link rel="stylesheet" href="{$app_url}/ui/ui/styles/modern-AdminLTE.min.css">
</head>
<body class="hold-transition lockscreen">
    <div class="lockscreen-wrapper" style="max-width: 520px;">
        <div class="panel panel-primary">
            <div class="panel-heading">
                {if $job.action == 'logout'}
                    {Lang::T('Disconnecting')}…
                {else}
                    {Lang::T('Connecting')}…
                {/if}
            </div>
            <div class="panel-body">
                {if $job.action == 'logout'}
                    <p class="text-muted">
                        {Lang::T('Disconnect request received.')}
                    </p>
                    <p class="text-muted">
                        {Lang::T('You will lose internet access once disconnected.')}
                    </p>
                    <p class="text-muted">
                        {Lang::T('You can turn it back on later from your account.')}
                    </p>
                {else}
                    <h4 style="margin-top:0">🛜 {Lang::T('Granting internet access…')}</h4>
                    <p class="text-muted" style="margin-bottom:6px">
                        ⏳ {Lang::T('You can close this page now.')}
                    </p>
                    <p class="text-muted" style="margin-bottom:6px">
                        ✅ {Lang::T('As soon as the connection is established, your Wi‑Fi icon will show internet access.')}
                    </p>
                    <p class="text-muted">
                        📱 {Lang::T('This can take up to 30 seconds. Then you can use your favorite apps like TikTok, Instagram, Facebook, and more.')}
                    </p>
                {/if}
                <hr>
                <p class="text-muted">
                    {Lang::T('On iPhone, tap Done (top right) to close this window.')}
                </p>
            </div>
            <div class="panel-footer">
                <div class="btn-group btn-group-justified">
                    <div class="btn-group">
                        <a class="btn btn-default" href="{$home_url}">{Lang::T('Back')}</a>
                    </div>
                    <div class="btn-group">
                        <a class="btn btn-success" href="#" onclick="try{window.close();}catch(e){} return false;">{Lang::T('Close')}</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="lockscreen-footer text-center">
            {$_c['CompanyName']}
        </div>
    </div>
</body>
</html>
