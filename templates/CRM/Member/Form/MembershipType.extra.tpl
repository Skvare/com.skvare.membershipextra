{literal}
    <script type="text/javascript">
        CRM.$(function($) {
            $('.crm-membership-type-form-block-limit_renewal').insertAfter('.crm-membership-type-form-block-period_type');
            $('.crm-membership-type-form-block-allow_renewal_before_unit').insertAfter('.crm-membership-type-form-block-period_type');
            $('.crm-membership-type-form-block-restrict_to_groups').insertAfter('.crm-membership-type-form-block-period_type');
            showHidePeriodSettingsCustom();
            $('#period_type').change(function(){
                showHidePeriodSettingsCustom();
            });

            function showHidePeriodSettingsCustom() {
                if ((cj("#period_type :selected").val() == "fixed" )) {
                    cj('.crm-membership-type-form-block-limit_renewal').show();
                    cj('.crm-membership-type-form-block-allow_renewal_before_unit').hide();
                    cj("#allow_renewal_before, #allow_renewal_before_unit").val("");
                }
                else {
                    cj('.crm-membership-type-form-block-limit_renewal').hide();
                    cj('.crm-membership-type-form-block-allow_renewal_before_unit').show();
                    cj("#limit_renewal"). prop("checked", false);
                }
            }
        });
    </script>
{/literal}

<table class="form-layout-compressed" style="display: none">
    <tr class="crm-membership-type-form-block-limit_renewal">
        <td class="label">{$form.limit_renewal.label}</td>
        <td>{$form.limit_renewal.html}<br/>For Fixed Membership Type, existing Members will not allow to renew their membership until Fixed Period Rollover Day<br/><br/>
        </td>
    </tr>
    <tr class="crm-membership-type-form-block-allow_renewal_before_unit">
        <td class="label">{$form.allow_renewal_before.label}</td>
        <td>{$form.allow_renewal_before.html} {$form.allow_renewal_before_unit.html}<br/>
            For Rolling type, existing members wil allow to renew their membership after above above period.
            <br/><br/>
        </td>
    </tr>
    <tr class="crm-membership-type-form-block-restrict_to_groups">
        <td class="label">{$form.restrict_to_groups.label}</td>
        <td>{$form.restrict_to_groups.html}<br/>
            Contact Subscribed to selected group only signup/renew their membership through online page.<br/><br/>
        </td>
    </tr>
</table>