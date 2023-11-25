{literal}
<script><!--
var selected;

function selectRowEffect(object, buttonSelect, groupName) {
	if (!selected) {
		if (document.getElementById) {
		selected = document.getElementById('defaultSelected');
		} else {
		selected = document.all['defaultSelected'];
		}
	}

	if (document.getElementById) {
		group = document.getElementById(groupName);
	} else {
		group = document.all[groupName];
	}
	if (selected) selected.className = 'moduleRow';
	object.className = 'moduleRowSelected';
	selected = object;

	// one button is not an array
	if (document.checkout_address.address[0]) {
		document.checkout_address.address[buttonSelect].checked=true;
	} else {
		document.checkout_address.address.checked=true;
	}
}

function rowOverEffect(object) {
  if (object.className == 'moduleRow') object.className = 'moduleRowOver';
}

function rowOutEffect(object) {
  if (object.className == 'moduleRowOver') object.className = 'moduleRow';
}

function check_form_optional(form_name) {
  var form = form_name;
  if (!form.elements['firstname']) {
    return true;
  } else {
    var firstname = form.elements['firstname'].value;
    var lastname = form.elements['lastname'].value;
    var street_address = form.elements['street_address'].value;

    if (firstname == '' && lastname == '' && street_address == '') {
      return true;
    } else {
      return check_form(form_name);
    }
  }
}
var form = "";
var submitted = false;
var error = false;
var error_message = "";

function check_input(field_name, field_size, message) {
  if (form.elements[field_name] && (form.elements[field_name].type != "hidden")) {
    var field_value = form.elements[field_name].value;

    if (field_value == '' || field_value.length < field_size) {
      error_message = error_message + "* " + message + "\n";
      error = true;
    }
  }
}

function check_radio(field_name, message) {
  var isChecked = false;

  if (form.elements[field_name] && (form.elements[field_name].type != "hidden")) {
    var radio = form.elements[field_name];

    for (var i=0; i<radio.length; i++) {
      if (radio[i].checked == true) {
        isChecked = true;
        break;
      }
    }

    if (isChecked == false) {
      error_message = error_message + "* " + message + "\n";
      error = true;
    }
  }
}

function check_select(field_name, field_default, message) {
  if (form.elements[field_name] && (form.elements[field_name].type != "hidden")) {
    var field_value = form.elements[field_name].value;

    if (field_value == field_default) {
      error_message = error_message + "* " + message + "\n";
      error = true;
    }
  }
}

function check_password(field_name_1, field_name_2, field_size, message_1, message_2) {
  if (form.elements[field_name_1] && (form.elements[field_name_1].type != "hidden")) {
    var password = form.elements[field_name_1].value;
    var confirmation = form.elements[field_name_2].value;

    if (password == '' || password.length < field_size) {
      error_message = error_message + "* " + message_1 + "\n";
      error = true;
    } else if (password != confirmation) {
      error_message = error_message + "* " + message_2 + "\n";
      error = true;
    }
  }
}

function check_password_new(field_name_1, field_name_2, field_name_3, field_size, message_1, message_2, message_3) {
  if (form.elements[field_name_1] && (form.elements[field_name_1].type != "hidden")) {
    var password_current = form.elements[field_name_1].value;
    var password_new = form.elements[field_name_2].value;
    var password_confirmation = form.elements[field_name_3].value;

    if (password_current == '' || password_current.length < field_size) {
      error_message = error_message + "* " + message_1 + "\n";
      error = true;
    } else if (password_new == '' || password_new.length < field_size) {
      error_message = error_message + "* " + message_2 + "\n";
      error = true;
    } else if (password_new != password_confirmation) {
      error_message = error_message + "* " + message_3 + "\n";
      error = true;
    }
  }
}

function check_form(form_name) {
  if (submitted == true) {
    alert("{/literal}{$smarty.const.JS_ERROR_SUBMITTED}{literal}");
    return false;
  }

  error = false;
  form = form_name;
  error_message = "{/literal}{$smarty.const.JS_ERROR}{literal}";

{/literal}
	{if $smarty.const.ACCOUNT_GENDER == 'true'}
		check_radio("gender", "{$smarty.const.ENTRY_GENDER_ERROR}");
	{/if}
{literal}

  check_input("firstname", {/literal}{$smarty.const.ENTRY_FIRST_NAME_MIN_LENGTH}{literal}, "{/literal}{$smarty.const.ENTRY_FIRST_NAME_ERROR}{literal}");
  check_input("lastname", {/literal}{$smarty.const.ENTRY_LAST_NAME_MIN_LENGTH}{literal}, "{/literal}{$smarty.const.ENTRY_LAST_NAME_ERROR}{literal}");

{/literal}
	{if $smarty.const.ACCOUNT_DOB == 'true'}
		check_input("dob", {$smarty.const.ENTRY_DOB_MIN_LENGTH}, "{$smarty.const.ENTRY_DATE_OF_BIRTH_ERROR}");
	{/if}
{literal}

  check_input("email_address", {/literal}{$smarty.const.ENTRY_EMAIL_ADDRESS_MIN_LENGTH}{literal}, "{/literal}{$smarty.const.ENTRY_EMAIL_ADDRESS_ERROR}{literal}");
  check_input("street_address", {/literal}{$smarty.const.ENTRY_STREET_ADDRESS_MIN_LENGTH}{literal}, "{/literal}{$smarty.const.ENTRY_STREET_ADDRESS_ERROR}{literal}");
  check_input("postcode", {/literal}{$smarty.const.ENTRY_POSTCODE_MIN_LENGTH}{literal}, "{/literal}{$smarty.const.ENTRY_POST_CODE_ERROR}{literal}");
  check_input("city", {/literal}{$smarty.const.ENTRY_CITY_MIN_LENGTH}{literal}, "{/literal}{$smarty.const.ENTRY_CITY_ERROR}{literal}");

{/literal}
	{if $smarty.const.ACCOUNT_STATE == 'true'}
		check_input("state", "{$smarty.const.ENTRY_STATE_MIN_LENGTH}", "{$smarty.const.ENTRY_STATE_ERROR}");
	{/if}
{literal}

  check_select("country", "", "{/literal}{$smarty.const.ENTRY_COUNTRY_ERROR}{literal}");

  check_input("telephone", {/literal}{$smarty.const.ENTRY_TELEPHONE_MIN_LENGTH}{literal}, "{/literal}{$smarty.const.ENTRY_TELEPHONE_NUMBER_ERROR}{literal}");

  check_password("password", "confirmation", {/literal}{$smarty.const.ENTRY_PASSWORD_MIN_LENGTH}{literal}, "{/literal}{$smarty.const.ENTRY_PASSWORD_ERROR}{literal}", "{/literal}{$smarty.const.ENTRY_PASSWORD_ERROR_NOT_MATCHING}{literal}");
  check_password_new("password_current", "password_new", "password_confirmation", {/literal}{$smarty.const.ENTRY_PASSWORD_MIN_LENGTH}{literal}, "{/literal}{$smarty.const.ENTRY_PASSWORD_ERROR}{literal}", "{/literal}{$smarty.const.ENTRY_PASSWORD_NEW_ERROR}{literal}", "{/literal}{$smarty.const.ENTRY_PASSWORD_NEW_ERROR_NOT_MATCHING}{literal}");

  if (error == true) {
    alert(error_message);
    return false;
  } else {
    submitted = true;
    return true;
  }
}
//--></script>
{/literal}
