{literal}
    <script type="text/javascript">
        CRM.$(function($) {
            $('.crm-membership-type-form-block-limit_renewal').insertAfter('.crm-membership-type-form-block-period_type');
            $('.crm-membership-type-form-block-renewal_period').insertAfter('.crm-membership-type-form-block-period_type');
            $('.crm-membership-type-form-block-restrict_to_groups').insertAfter('.crm-membership-type-form-block-period_type');
            showHidePeriodSettingsCustom();
            $('#period_type').change(function(){
                showHidePeriodSettingsCustom();
            });

            function showHidePeriodSettingsCustom() {
                if ((cj("#period_type :selected").val() == "fixed" )) {
                    cj('.crm-membership-type-form-block-limit_renewal').show();
                    cj('.crm-membership-type-form-block-renewal_period').hide();
                    cj("#renewal_period_number, #renewal_period_unit").val("");
                }
                else {
                    cj('.crm-membership-type-form-block-limit_renewal').hide();
                    cj('.crm-membership-type-form-block-renewal_period').show();
                    cj("#limit_renewal").prop("checked", false);
                }
            }
        });
    </script>
{/literal}

{crmScope extensionKey='org.example.myextension'}
<table class="form-layout-compressed" style="display: none">
    <tr class="crm-membership-type-form-block-limit_renewal">
        <td class="label">{$form.limit_renewal.label}</td>
        <td>{$form.limit_renewal.html}<br/>
            <span class="description">
                {ts}On public contribution pages, disallow renewal before the Fixed Period Rollover Day.{/ts}
            </span>
            <br/><br/>
        </td>
    </tr>
    <tr class="crm-membership-type-form-block-renewal_period">
        <td class="label">{$form.renewal_period_number.label}</td>
        <td>{$form.renewal_period_number.html} {$form.renewal_period_unit.html} {ts}before the membership End Date{/ts}<br/>
            <span class="description">
                {ts}On public contribution pages, disallow renewal until this period.{/ts}
            </span>
            <br/><br/>
        </td>
    </tr>
    <tr class="crm-membership-type-form-block-restrict_to_groups">
        <td class="label">{$form.restrict_to_groups.label}</td>
        <td>{$form.restrict_to_groups.html}<br/>
            <span class="description">
                {ts}On public contribution pages, disallow signup/renewal by contacts not in these groups.{/ts}
            </span>
            <br/><br/>
        </td>
    </tr>
</table>
{/crmScope}