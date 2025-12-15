<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>{$_title} - {$_c['CompanyName']}</title>
    <link rel="shortcut icon" href="{$app_url}/ui/ui/images/logo.png" type="image/x-icon" />
    <link rel="stylesheet" href="{$app_url}/ui/ui/styles/bootstrap.min.css">
    <link rel="stylesheet" href="{$app_url}/ui/ui/styles/modern-AdminLTE.min.css">
    {* Proper head refresh (iOS CNA is picky about meta refresh placement) *}
    {if isset($job) && ($job.status == 'pending' || $job.status == 'running')}
        <meta http-equiv="refresh" content="2;url={$refresh_url}">
    {/if}
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
                {if $job.status == 'pending' || $job.status == 'running'}
                    <p class="text-muted">
                        {Lang::T('Please wait')}… {Lang::T('This can take up to 30 seconds.')}
                    </p>
                    <p class="text-muted">
                        {Lang::T('If your internet starts working, you may close this page and continue browsing.')}
                    </p>
                    <p>
                        <img src="{$app_url}/ui/ui/images/loading.gif" alt="loading" />
                    </p>
                {elseif $job.status == 'failed'}
                    <div class="alert alert-danger">
                        <b>{Lang::T('Failed to connect to device')}</b><br>
                        {$job.message|escape}
                    </div>
                {/if}
            </div>
            <div class="panel-footer">
                <div class="btn-group btn-group-justified mb15">
                    <div class="btn-group">
                        <a class="btn btn-default" href="{$home_url}">{Lang::T('Back')}</a>
                    </div>
                    <div class="btn-group">
                        <a class="btn btn-primary" href="{$refresh_url}">{Lang::T('Refresh')}</a>
                    </div>
                    <div class="btn-group">
                        <a class="btn btn-success" href="http://captive.apple.com/hotspot-detect.html">Done</a>
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
