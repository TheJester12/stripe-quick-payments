<?php 
	if($_SERVER["HTTPS"] != "on") {
	$newurl = "https://" . $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
	header("Location: $newurl");
	exit();
} ?><!DOCTYPE html>
<html lang="en-US" prefix="og: http://ogp.me/ns#">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Switchback Interactive - Payment</title>
	
	<link rel='stylesheet' href='/payment/css/normalize.min.css' type='text/css' media='all' />
	<link rel='stylesheet' href='/payment/css/stripe-quick-payments.css' type='text/css' media='all' />
	
	<script src="//use.typekit.net/ehu2vxy.js"></script>
	<script>try{Typekit.load();}catch(e){}</script>

	<script src='/payment/js/jquery-2.1.3.min.js'></script>
	<script src="https://checkout.stripe.com/checkout.js"></script>
	<script src='/payment/js/stripe-quick-payments.js'></script>

</head>

<body>
	<script>
		(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
		(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
		m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
		})(window,document,'script','//www.google-analytics.com/analytics.js','ga');
		ga('create', 'UA-61467862-1', 'auto');
		ga('send', 'pageview');
	</script>
	
	<?php
		require_once dirname( __FILE__ ) . '/config.php';
		require_once('./vendor/autoload.php');
		\Stripe\Stripe::setApiKey($stripe_secret);
		$type = $_GET['type'];
		$param = $_GET['param'];
	?>
	
	<div class="content">
		<div class="center">
			<img src="/payment/img/switchback-interactive-logo.png" width="681" height="126">
			
			<div class="payment-box">
				<div id="payment-form" data-public-key="<?php echo $stripe_public; ?>" data-base-url="<?php echo $base_url; ?>" data-checkout-name="<?php echo $checkout_name; ?>" data-checkout-image="<?php echo $checkout_image; ?>">
					<?php if ($type == 'plan') { ?>
						<?php if ($param == '') { ?>
							<?php 
								$plan_details = array();
								foreach($stripe_public_plans as $plan) {
									try {
										$plan_with_details = \Stripe\Plan::retrieve($plan);
									} catch (Exception $e) {
										
									}
									if ($plan_with_details) {
										$plan_details[] = $plan_with_details;
									}
								}
							?>
							<p>Thanks for doing business with Switchback Interactive. You can sign up for an ongoing maintenance plan using this secure payment portal.</p>
							<p class="small-caps">What plan would you prefer?</p>
							<p>
								<div class="styled-select">
									<select id="payment-select">
										<?php foreach($plan_details as $plan) { ?>
											<option value="<?php echo $plan->id; ?>" data-amount="<?php echo $plan->amount/100; ?>" data-description="<?php echo $plan->name; ?>"><?php echo $plan->name; ?> ($<?php echo $plan->amount/100; ?>/<?php echo $plan->interval; ?>)</option>
										<?php } ?>
									</select>
								</div><!-- .styled-select -->
							</p>
							<p><button id="payment-button" class="btn" data-type="plan" data-plan="<?php echo $plan_details[0]->id; ?>" data-amount="<?php echo $plan_details[0]->amount/100; ?>" data-description="<?php echo $plan_details[0]->name; ?>">Pay Now</button></p>
						<?php } else { ?>
							<?php 
								try {
									$plan = \Stripe\Plan::retrieve($param);
								} catch (Exception $e) {
									
								}
							?>
								<?php if ($plan) { ?>
									<p>Thanks for doing business with Switchback Interactive. You can sign up for an ongoing maintenance plan using this secure payment portal.</p>
									<p class="small-caps">You will be charged $<?php echo $plan->amount/100; ?> every <?php if ($plan->interval_count > 1 ) { echo $plan->interval_count; } ?> <?php echo $plan->interval; ?><?php if ($plan->interval_count > 1 ) { echo "s"; } ?></p>
									<p><button id="payment-button" class="btn" data-type="plan" data-plan="<?php echo $plan->id; ?>" data-amount="<?php echo $plan->amount/100; ?>" data-description="<?php echo $plan->name; ?>">Pay Now</button></p>
								<?php } else { ?>
									<p>Sorry, that isn't one of our maintenance plans.</p>
								<?php } ?>
						<?php } ?>
					<?php } ?>
					<?php if ($type == 'update') { ?>
						<?php if ($param) { ?>
							<p>Thanks for doing business with Switchback Interactive. You can update the credit card used for your maintenance plan using this secure payment portal.</p>
							<p class="small-caps">Please use the same email address that you used when signing up</p>
							<p><button id="payment-button" class="btn" data-type="update" data-customerid="<?php echo $param; ?>" data-description="Update Credit Card">Update Card</button></p>
						<?php } else { ?>
							<p>Sorry, a customer ID is required.</p>
						<?php } ?>
					<?php } ?>
					<?php if ($type == 'amount') { ?>
						<?php if (!is_numeric($param)) { ?>
							<p>Sorry, we didn't understand that number.</p>
						<?php } else { ?>
							<?php if ($param > $max_amount_accepted) { ?>
								<p>Sorry, we do not take credit card payments for more than $<?php echo $max_amount_accepted; ?> because of processing fees, please talk to us about issuing a check or some other method of payment.</p>
							<?php } else if ($param <= 0) { ?>
								<p>Sorry, that's not how this works.</p>
							<?php } else { ?> 
								<p>Thanks for doing business with Switchback Interactive. You can pay for services using this secure payment portal.</p>
								<p class="small-caps">You will be charged $<?php echo $param; ?></p>
								<p><button id="payment-button" class="btn" data-type="amount" data-amount="<?php echo $param; ?>" data-description="One time payment">Pay Now</button></p>
							<?php } ?>								
						<?php } ?>
					<?php } ?>
				</div><!-- #payment-form -->
				<p id="payment-spinner" style="display:none;text-align: center;"><img src="/payment/img/spinner.gif" width="60" height="60"></p>
				<p id="payment-message" style="display:none;"></p>
			</div><!-- .payment-box -->
			
		</div><!-- .center -->
	</div><!-- .content -->
	
</body>

</html>