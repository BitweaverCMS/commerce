




















    {bitmodule title=$moduleTitle name="currencies"}
    $content = "";
    $content .= zen_draw_form('currencies', zen_href_link(basename(ereg_replace('.','', $PHP_SELF)), '', $request_type, false), 'get');
    $content .= zen_draw_pull_down_menu('currency', $currencies_array, $_SESSION['currency'], 'onchange="this.form.submit();" style="width: 100%"') . $hidden_get_variables . zen_hide_session_id();
/form>
{/bitmodule}