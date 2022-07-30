{fieldset legend="Order Deadline"}
	<div class="input-group date" id="order-deadline">
		<input type="text" class="form-control" name="deadline_date" value="{$deadline|date_format:'Y-m-d'}"><span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
	</div>
	{formhelp note="If you have an urgent deadline to receive your order, enter it here. <br><strong>This is not a guarantee</strong>, but will let us know if it can be delivered in time."}
{/fieldset}
<script>
$('.input-group.date').datepicker({
    format: "yyyy-mm-dd",
    clearBtn: true,
    todayHighlight: true
});
</script>
