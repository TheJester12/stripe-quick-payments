$( document ).ready(function() {
	
	var stripeKey = $("#payment-form").data("public-key");
	var baseURL = $("#payment-form").data("base-url");
	var stripeCheckOutName = $("#payment-form").data("checkout-name");
	var stripeCheckOutImage = $("#payment-form").data("checkout-image");
	
	var handler = StripeCheckout.configure({
		key: stripeKey,
		image: baseURL + stripeCheckOutImage,
		locale: 'auto',
		token: function(token) {
			$("#payment-form").hide();
			$("#payment-spinner").show();
			var type = $( "#payment-button" ).data('type');
			if (type == 'plan') {
				token.type = 'plan';
				token.plan = $( "#payment-button" ).data('plan');
				token.amount = $( "#payment-button" ).data('amount') * 100;
			}
			if (type == 'update') {
				token.type = 'update';
				token.customerid = $( "#payment-button" ).data('customerid');
			}
			if (type == 'amount') {
				token.type = 'amount';
				token.amount = $( "#payment-button" ).data('amount') * 100;
			}
			$.ajax({
				type: "POST",
				url: baseURL + '/process.php',
				data: token,
				dataType: 'json',
				success: function(json, err) {
					if (json.result == 'success') {
						$("#payment-spinner").hide();
						$("#payment-message").text(json.message).show();
					} else {
						$("#payment-spinner").hide();
						$("#payment-message").text(json.message).show();
					}
				},
				error: function(xhr, status, err) {
					$("#payment-spinner").hide();
					$("#payment-message").text(json.message).show();
				}
			});
		}
	});
	
	$( "#payment-button" ).click(function( e ) {
		// Open Checkout with further options:
		var type = $( "#payment-button" ).data('type');
		if (type == 'plan') {
			var plan = $( "#payment-button" ).data('plan');
			var amount = $( "#payment-button" ).data('amount');
			var description = $( "#payment-button" ).data('description');
		}
		if (type == 'update') {
			var amount = $( "#payment-button" ).data('plan');
			var description = $( "#payment-button" ).data('description');
		}
		if (type == 'amount') {
			var amount = $( "#payment-button" ).data('amount');
			var description = $( "#payment-button" ).data('description');
		}
		handler.open({
			name: stripeCheckOutName,
			description: description,
			amount: amount * 100,
			allowRememberMe: false,
		});
		e.preventDefault();
	});
	
	// Close Checkout on page navigation:
	window.addEventListener('popstate', function() {
		handler.close();
	});
	
});