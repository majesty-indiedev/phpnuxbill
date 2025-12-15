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
        <div class="panel panel-success">
            <div class="panel-heading">
                {if $job.action == 'logout'}
                    {Lang::T('Logout Request successfully')}
                {else}
                    {Lang::T('Login Request successfully')}
                {/if}
            </div>
            <div class="panel-body">
                <p class="text-muted">
                    {Lang::T('You are connected.')}
                </p>
                <p class="text-muted">
                    {Lang::T('If this window does not close automatically, tap Dashboard or Done.')}
                </p>
            </div>
            <div class="panel-footer">
                <div class="btn-group btn-group-justified mb15">
                    <div class="btn-group">
                        <a class="btn btn-primary" href="{$home_url}">{Lang::T('Dashboard')}</a>
                    </div>
                    <div class="btn-group">
                        <a class="btn btn-default" href="{$apple_cna_url}">Done</a>
                    </div>
                </div>
                <small class="text-muted">
                    Job: {$job.id} ({$job.status})
                </small>
            </div>
        </div>
        <div class="lockscreen-footer text-center">
            {$_c['CompanyName']}
        </div>
    </div>
</body>
</html>
