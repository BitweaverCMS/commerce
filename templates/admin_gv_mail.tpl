
{include_php file="`$smarty.const.BITCOMMERCE_PKG_PATH`admin/includes/header_navigation.php"}

<div class="admin bitcommerce">
	<div class="page-header">
		<h1>{tr}Send Gift Certificate To Customers{/tr}</h1>
	</div>
	<div class="body">

{if $smarty.get.action == 'preview' && ($smarty.post.customers_email_address || $smarty.post.email_to)}

{form name="mail" action="`$smarty.const.BITCOMMERCE_PKG_ADMIN_URI`gv_mail.php?action=send_email_to_user" method="post"}
<div class="control-group">
	{formlabel label="Customer"}
	{forminput}
		{$mailSentTo}
	{/forminput}
</div>
<div class="control-group">
	{formlabel label="From"}
	{forminput}
		{$smarty.request.from|escape}
	{/forminput}
</div>
<div class="control-group">
	{formlabel label="Subject"}
	{forminput}
		{$smarty.request.subject|escape}
	{/forminput}
</div>
<div class="control-group">
	{formlabel label="Amount"}
	{forminput}
		{$smarty.request.amount|escape} {if $smarty.request.amount <= 0}<span class="alert">{$smarty.const.ERROR_GV_AMOUNT}</span>{/if}
	{/forminput}
</div>

{if $smarty.request.message_html}
<div class="control-group">
	{formlabel label="Rich Text Message"}
	{forminput}
		{$smarty.request.message_html}
	{/forminput}
</div>
{/if}

<div class="control-group">
	{formlabel label="Email Message"}
	{forminput}
		{$smarty.request.message|escape|nl2br}
	{/forminput}
</div>

<div class="control-group">
	{formlabel label="Admin Note"}
	{forminput}
		{$smarty.request.admin_note|escape|nl2br}
	{/forminput}
</div>

{if $smarty.request.oID}
<div class="control-group">
	{formlabel label="Related Order"}
	{forminput}
		{$smarty.request.oID}
	{/forminput}
</div>
{/if}

{* Re-Post all POST'ed variables *}
{foreach from=$smarty.post key=key item=value}
	<input type="hidden" name="{$key}" value="{$value|escape}" />
{/foreach}

<div class="control-group submit">
	{forminput}
		<input class="btn btn-small" name="Back" value="Back" type="submit" />
		<input class="btn btn-small" name="send_gv" value="Send Email" type="submit" />
	{/forminput}
</div>

{/form}


{else}


<script language="javascript" type="text/javascript"><!--
{literal}
var form = "";
var submitted = false;
var error = false;
var error_message = "";

function check_recipient(field_cust, field_input, message) {
//  if (form.elements[field_cust] && form.elements[field_cust].type != "hidden" && form.elements[field_input] && form.elements[field_input].type != "hidden") {
    var field_value_cust = form.elements[field_cust].value;
    var field_value_input = form.elements[field_input].value;

    if ((field_value_input == '' || field_value_input.length < 1)  &&  field_value_cust == '') {
      error_message = error_message + "* " + message + "\n";
      error = true;
    }
  }
//}
function check_amount(field_name, field_size, message) {
  if (form.elements[field_name] && (form.elements[field_name].type != "hidden")) {
    var field_value = form.elements[field_name].value;

    if (field_value == '' || field_value == 0 || field_value < 0 || field_value.length < field_size ) {
      error_message = error_message + "* " + message + "\n";
      error = true;
    }
  }
}
function check_message(msg) {
  if (form.elements['message'] && form.elements['message_html']) {
    var field_value1 = form.elements['message'].value;
    var field_value2 = form.elements['message_html'].value;

    if ((field_value1 == '' || field_value1.length < 3) && (field_value2 == '' || field_value2.length < 3)) {
      error_message = error_message + "* " + msg + "\n";
      error = true;
    }
  }
}
function check_input(field_name, field_size, message) {
  if (form.elements[field_name] && (form.elements[field_name].type != "hidden")) {
    var field_value = form.elements[field_name].value;

    if (field_value == '' || field_value.length < field_size) {
      error_message = error_message + "* " + message + "\n";
      error = true;
    }
  }
}

function check_form(form_name) {
  if (submitted == true) {
    alert("<?php echo JS_ERROR_SUBMITTED; ?>");
    return false;
  }
  error = false;
  form = form_name;
  error_message = "<?php echo JS_ERROR; ?>";

  check_recipient('customers_email_address', 'email_to', "<?php echo ERROR_NO_CUSTOMER_SELECTED; ?>");
  check_message("<?php echo ENTRY_NOTHING_TO_SEND; ?>");
  check_amount('amount',1,"<?php echo ERROR_NO_AMOUNT_SELECTED; ?>");
  check_input('subject','',"<?php echo ERROR_NO_SUBJECT; ?>");

  if (error == true) {
    alert(error_message);
    return false;
  } else {
    submitted = true;
    return true;
  }
}
{/literal}
//--></script>

{if $smarty.session.html_editor_preference_status=="FCKEDITOR"}{include_php file="`$smarty.const.DIR_WS_INCLUDES`fckeditor.php"}{/if}
{if $smarty.session.html_editor_preference_status=="HTMLAREA"}{include_php file="`$smarty.const.DIR_WS_INCLUDES`htmlarea.php"}{/if}


{form name="mail" action="`$smarty.const.BITCOMMERCE_PKG_ADMIN_URI`gv_mail.php?action=preview" method="post" onsubmit="return check_form(mail);"}
<div class="control-group">
	{formlabel label="Email To"}
	{forminput}
		<input type="text" name="email_to" value="{$smarty.request.email_to}" />
	{/forminput}
</div>

<div class="control-group">
	{formlabel label="From"}
	{forminput}
		<input type="text" name="from" value="{$smarty.request.from|default:$smarty.const.EMAIL_FROM}" />
	{/forminput}
</div>

<div class="control-group">
	{formlabel label="Subject"}
	{forminput}
		<input type="text" name="subject" value="{$smarty.request.subject|default:"`$gBitSystem->mConfig.site_title` Gift Certificate"|tra}" />
	{/forminput}
</div>

<div class="control-group">
	{formlabel label="Amount"}
	{forminput}
		<input type="text" name="amount" value="{$smarty.request.amount}" />
	{/forminput}
</div>

{*
<?php
// toggle switch for editor
        $editor_array = array(array('id' => '0', 'text' => TEXT_NONE),
                              array('id' => '1', 'text' => TEXT_HTML_AREA));
        echo TEXT_EDITOR_INFO . zen_draw_form_admin('set_editor_form', FILENAME_GV_MAIL, '', 'get') . '&nbsp;&nbsp;' . zen_draw_pull_down_menu('reset_editor', $editor_array, ($_SESSION['html_editor_preference_status'] == 'HTMLAREA' ? '1' : '0'), 'onChange="this.form.submit();"') .
        zen_draw_hidden_field('action', 'set_editor') .
        '</form>';
?>


              <tr>
                <td valign="top" class="main"><?php echo TEXT_RICH_TEXT_MESSAGE; ?></td>
                <td>
				<?php if (is_null($_SESSION['html_editor_preference_status'])) echo TEXT_HTML_EDITOR_NOT_DEFINED; ?>
				<?php if (EMAIL_USE_HTML != 'true') echo TEXT_WARNING_HTML_DISABLED; ?>
				<?php if ($_SESSION['html_editor_preference_status']=="FCKEDITOR") {
					$oFCKeditor = new FCKeditor ;
					$oFCKeditor->Value = ($_POST['message_html']=='') ? TEXT_GV_ANNOUNCE : stripslashes($_POST['message_html']) ;
					$oFCKeditor->CreateFCKeditor( 'message_html', '97%', '250' ) ;  //instanceName, width, height (px or %)
					} else { // using HTMLAREA or just raw "source"
  if (EMAIL_USE_HTML == 'true') {
					echo zen_draw_textarea_field('message_html', 'soft', '100%', '20', ($_POST['message_html']=='') ? TEXT_GV_ANNOUNCE : stripslashes($_POST['message_html']), 'id="message_html"');
}
					} ?>
				</td>
			  </tr>
*}

<div class="control-group">
	{formlabel label="Message"}
	{forminput}
		<textarea name="message" wrap="soft" cols="60" rows="15">{$smarty.request.message|default:$smarty.const.TEXT_GV_ANNOUNCE|strip_tags|stripslashes|escape|trim}</textarea>
	{/forminput}
</div>

<div class="control-group">
	{formlabel label="Admin Note"}
	{forminput}
		<input type="text" name="admin_note" value="{$smarty.request.message|strip_tags|stripslashes|escape|trim}"/>
		{formhelp note="Enter the reason for this gift certificate. It will NOT be visible to the customer."}
	{/forminput}
</div>

<div class="control-group">
	{formlabel label="Related to Order"}
	{forminput}
		<input type="text" name="oID" value="{$smarty.request.oID}" />
	{/forminput}
</div>

<div class="control-group submit">
	{forminput}
		<input class="btn btn-small" name="Send Email" value="Send Email" type="submit">
	{/forminput}
</div>

{/form}

{/if}

	</div><!-- end .body -->
</div><!-- end .bitcommerce -->
