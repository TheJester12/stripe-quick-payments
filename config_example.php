<?php
    
    //Company Name
	$company_name = 'Switchback Interactive';
	
	//Admin Email
    $admin_email = 'you@gmail.com';
    
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
    
    //Stripe Publically Available Plans (That user can choose from)
	$stripe_public_plans = array(
		'plan_00000000000000',
		'plan_00000000000000'
	);
	
	//Stripe Checkout Name
	$checkout_name = 'Switchback Interactive';
	
	//Stripe Checkout Image
	$checkout_image = 'img/switchback-stripe.png';
	
	//The base URL (Used for javascript and emails)
	$base_url = 'https://yoursite.com/payment/';
	
	//The maximum amount you would want someone to give you with this form
	$max_amount_accepted = 1000;
	
	//Postmark API Key
	$postmark_key = null;
	
	//From Email Address
	$postmark_from = 'you@gmail.com';
	
	//From Admin Email Address
    $postmark_admin = 'you@gmail.com';
    
    //Receipt Email Customizations
    $receipt_subject = "Switchback Payment Receipt";
    $receipt_headline = 'Payment Receipt';
    $receipt_intro = '<p style="margin-top:0px;">Thanks for your business with Switchback Interactive. If you have questions about this invoice, please contact [Your name] at <a href="mailto:you@gmail.com">you@gmail.com</a>.</p>';

    //Payment Failed Email Customizations
    $payment_failed_subject = 'Switchback Payment Failed';
    $payment_failed_headline = 'Payment Failed';
    $payment_failed_intro = '<p>Your credit card payment failed when trying to charge it this month. Please use the link below to update your card within the next 3 days. If you have questions about this invoice, please contact [Your name] at <a href="mailto:you@gmail.com">you@gmail.com</a>.</p>';

    //Payment Updated Email Customizations
    $payment_updated_subject = 'Customer Payment Method Updated';
    $payment_updated_headline = 'Payment Method Updated';
    $payment_updated_intro = '<p>A customer payment method has been updated.</p>';
	
	//You can leave this alone
	if ($current_mode == 'live') {
		$stripe_secret = $stripe_live_secret;
		$stripe_public = $stripe_live_public;
	} else {
		$stripe_secret = $stripe_test_secret;
		$stripe_public = $stripe_test_public;
	}
	
?>