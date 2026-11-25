{* Options flagged products_options_required render required + data-option-required.
   A page may hide an option that does not apply; the browser still validates a hidden
   required input and blocks the submit, so `required` follows visibility at submit time. *}
<script>//<![CDATA[
{literal}
$(document).on('click', 'form :submit', function() {
	$(this.form).find('[data-option-required]').each(function() {
		this.required = $(this).parent().is(':visible');
	});
});
{/literal}
//]]></script>
