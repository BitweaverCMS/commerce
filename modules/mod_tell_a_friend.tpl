




















  {bitmodule title=$moduleTitle name="tellafriend"}
  $content = "";
  $content = zen_draw_form('tell_a_friend', zen_href_link(FILENAME_TELL_A_FRIEND, '', 'NONSSL', false), 'get');
  $content = zen_draw_input_field('to_email_address', '', 'size="15"') . '&nbsp;' . zen_image_submit('button_tell_a_friend.gif', BOX_HEADING_TELL_A_FRIEND) . zen_draw_hidden_field('products_id', $_GET['products_id']) . zen_hide_session_id() . '<p>' . BOX_TELL_A_FRIEND_TEXT . '</p>

{/bitmodule}