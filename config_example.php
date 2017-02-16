<?php
	
	//Stripe Current Mode
	$current_mode = 'test';
	
	//Stripe Test Secret Key
	$stripe_test_secret = 'sk_test_000000000000000000000000';
	
	//Stripe Live Secret Key
	$stripe_live_secret = 'sk_live_000000000000000000000000';
	
	//Stripe Test Public Key
	$stripe_test_public = 'pk_test_000000000000000000000000';
	
	//Stripe Live Public Key
	$stripe_live_public = 'pk_live_000000000000000000000000';
	
	//Stripe Checkout Name
	$checkout_name = 'Switchback Interactive';
	
	//Stripe Checkout Image
	$checkout_image = 'img/switchback-stripe.png';
	
	//The base URL (Used for javascript)
	$base_url = 'https://yoursite.com/payment/';
	
	//The maximum amount you would want someone to give you with this form
	$max_amount_accepted = 1000;
	
	//Postmark API Key
	$postmark_key = null;
	
	//From Email Address
	$postmark_from = 'you@gmail.com';
	
	//From Admin Email Address
	$postmark_admin = 'you@gmail.com';
	
	//You can leave this alone
	if ($current_mode == 'live') {
		$stripe_secret = $stripe_live_secret;
		$stripe_public = $stripe_live_public;
	} else {
		$stripe_secret = $stripe_test_secret;
		$stripe_public = $stripe_test_public;
	}
	
?>