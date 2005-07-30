{if $sideboxRecordCompanies}
{bitmodule title=$moduleTitle name="recordcompanies"}

  $content.= zen_draw_form('record_company', zen_href_link(FILENAME_DEFAULT, '', 'NONSSL', false), 'get');
  $content .= zen_draw_pull_down_menu('record_company_id', $record_company_array, (isset($_GET['record_company_id']) ? $_GET['record_company_id'] : ''), 'onchange="this.form.submit();" size="' . MAX_RECORD_COMPANY_LIST . '" style="width: 100%"') . zen_hide_session_id() .zen_draw_hidden_field('typefilter', 'record_company');
  $content .= zen_draw_hidden_field('main_page', FILENAME_DEFAULT) . '</form>';
{/bitmodule}
{/if}