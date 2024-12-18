//(Amount * Price per piece) * (1-(discountPercent/100))
$(function() {
	recalculateTotals();
});

function recalculateTotals()
{
  $("#ordersamount").keyup(function () {
    $("#orderspriceTotal").val(recalculateTotal($(this).val(),$("#orderspricePerPiece").val(),$("#ordersdiscountPercent").val()));
  });
  $("#orderspricePerPiece").keyup(function () {
    $("#orderspriceTotal").val(recalculateTotal($("#ordersamount").val(),$(this).val(),$("#ordersdiscountPercent").val()));
  });
  $("#ordersdiscountPercent").keyup(function () {
    $("#orderspriceTotal").val(recalculateTotal($("#ordersamount").val(),$("#orderspricePerPiece").val(),$(this).val()));
  });
}

function recalculateTotalsCall()
{
  $("#orderspriceTotal").val(recalculateTotal($("#ordersamount").val(),$("#orderspricePerPiece").val(),$("#ordersdiscountPercent").val()));
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
