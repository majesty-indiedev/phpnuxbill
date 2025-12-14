{include file="customer/header-public.tpl"}

{* Auto-refresh while pending/running. Avoid JS popups for captive portal compatibility. *}
{if isset($job) && ($job.status == 'pending' || $job.status == 'running')}
    <meta http-equiv="refresh" content="2;url={$refresh_url}">
{/if}

<div class="hidden-xs" style="height:60px"></div>

<div class="row">
    <div class="col-md-2"></div>
    <div class="col-md-8">
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
                        {Lang::T('Please wait')}… {Lang::T('Do not close this page.')}
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
                        <a class="btn btn-primary" href="{$refresh_url}">{Lang::T('Try Again')}</a>
                    </div>
                </div>
                <small class="text-muted">
                    Job: {$job.id} ({$job.status})
                </small>
            </div>
        </div>
    </div>
    <div class="col-md-2"></div>
</div>

{include file="customer/footer-public.tpl"}
