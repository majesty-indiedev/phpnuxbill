{include file="customer/header.tpl"}

<div class="row">
    <div class="col-md-12">
        <div class="box box-primary box-solid">
            <div class="box-header">
                <h3 class="box-title">{Lang::T('Data Usage')}</h3>
            </div>
            <div class="box-body">

                {if !empty($plugin_error)}
                    <div class="alert alert-warning" style="margin-bottom:10px;">
                        <strong>{Lang::T('Plugin Error')}:</strong> {$plugin_error|escape}
                        <div class="text-muted" style="margin-top:6px;">
                            {Lang::T('Tip: remove ?radius_usage_debug=1 from the URL after debugging.')}
                        </div>
                    </div>
                {/if}

                {if empty($items)}
                    <p class="text-muted" style="margin:0;">{Lang::T('No active RADIUS plan found.')}</p>
                {else}
                    {foreach $items as $it}
                        <table class="table table-bordered table-striped" style="margin-bottom:10px;">
                            <tr>
                                <td class="small text-primary text-uppercase text-normal" style="width:40%;">{Lang::T('Plan')}</td>
                                <td>{$it.plan_name|escape}</td>
                            </tr>
                            <tr>
                                <td class="small text-primary text-uppercase text-normal">{Lang::T('Used')}</td>
                                <td>{$it.used_human|escape}</td>
                            </tr>
                            <tr>
                                <td class="small text-primary text-uppercase text-normal">{Lang::T('Remaining')}</td>
                                <td>
                                    {if $it.has_limit}
                                        {if $it.remaining_bytes <= 0}
                                            <span class="label label-danger">{Lang::T('Exhausted')}</span>
                                        {else}
                                            {$it.remaining_human|escape}
                                        {/if}
                                    {else}
                                        <span class="text-muted">{Lang::T('Unlimited')}</span>
                                    {/if}
                                </td>
                            </tr>
                            <tr>
                                <td class="small text-primary text-uppercase text-normal">{Lang::T('Period')}</td>
                                <td><small class="text-muted">{$it.start_dt|escape} → {$it.end_dt|escape}</small></td>
                            </tr>
                        </table>
                    {/foreach}
                    <p class="help-block" style="margin-top:10px;">
                        {Lang::T('Note: usage is calculated from RADIUS accounting records.')}
                    </p>
                {/if}

            </div>
        </div>
    </div>
</div>

{include file="customer/footer.tpl"}
