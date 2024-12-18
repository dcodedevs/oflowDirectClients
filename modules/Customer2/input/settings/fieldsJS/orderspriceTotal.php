//(Amount * Price per piece) * (1-(discountPercent/100))
$(function() {
	recalculateTotals();
});

function recalculateTotals()
{
	$("input.ordersamount").keyup(function () {
		$(this).closest("form").find(".orderspriceTotal").val(recalculateTotal($(this).val(),$(this).closest("form").find(".orderspricePerPiece").val(),$(this).closest("form").find(".ordersdiscountPercent").val()));
	});
	$("input.orderspricePerPiece").keyup(function () {
		$(this).closest("form").find(".orderspriceTotal").val(recalculateTotal($(this).closest("form").find(".ordersamount").val(),$(this).val(),$(this).closest("form").find(".ordersdiscountPercent").val()));
	});
	$("input.ordersdiscountPercent").keyup(function () {
		$(this).closest("form").find(".orderspriceTotal").val(recalculateTotal($(this).closest("form").find(".ordersamount").val(),$(this).closest("form").find(".orderspricePerPiece").val(),$(this).val()));
	});
}

function recalculateTotalsCall()
{
	$("input.orderspriceTotal").each(function(){
		$(this).val(recalculateTotal($(this).closest("form").find(".ordersamount").val(),$(this).closest("form").find(".orderspricePerPiece").val(),$(this).closest("form").find(".ordersdiscountPercent").val()));
	});
}

function recalculateTotal(amount,price,discount)
{
	amount = parseFloat(amount.replace(',','.'));
	price = parseFloat(price.replace(',','.'));
	discount = parseFloat(discount.replace(',','.'));
	if(isNaN(amount)) amount = 0;
	if(isNaN(price)) price = 0;
	if(isNaN(discount)) discount = 0;
	var _return = Math.round(amount * price * (1-(discount/100))*100)/100;
	return _return.toString().replace('.',',');
}