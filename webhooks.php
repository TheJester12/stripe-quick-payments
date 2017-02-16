<?php
	
	//Still working on this!!
	
	require_once dirname( __FILE__ ) . '/config.php';
	require_once('./vendor/autoload.php');
	use Postmark\PostmarkClient;
	$postmark_client = new PostmarkClient($postmark_key);
	\Stripe\Stripe::setApiKey($stripe_secret);
	
	// retrieve the request's body and parse it as JSON
	$body = @file_get_contents('php://input');
	
	// grab the event information
	$event_json = json_decode($body);
	
	// this will be used to retrieve the event from Stripe
	$event_id = $event_json->id;
	
	if(isset($event_json->id)) {
		
		try {
															
			// successful payment
			if($event_json->type == 'charge.succeeded') {
				// send a payment receipt email here
				
				// retrieve the payer's information
				$customer = \Stripe\Customer::retrieve($event_json->data->object->customer);
				
				$email = $customer->email;
				
				$amount = $event_json->data->object->amount / 100; // amount comes in as amount in cents, so we need to convert to dollars
				$donor_name = $customer->description;
				$card_type = $event_json->data->object->source->brand;
				$card_Last_four = $event_json->data->object->source->last4;
				
				ob_start();

				include("receipt-email-header.php");
				
				?>
				
				<span style="font-size:18px; color:#888888">Receipt - <?php echo date("F j, Y"); ?></span><br><br>
				<span style="font-size:30px; line-height:30px; text-align:center"><?php echo $donor_name; ?></span><br>
				<span style="font-size:30px; line-height:30px; text-align:center"><?php echo $card_type; ?> - <?php echo $card_Last_four; ?></span><br><br>
				<span style="font-size:18px; line-height:30px; text-align:center; color:#888888">Donation Total:</span><br>
				<span style="font-size:30px; line-height:30px; text-align:center"><?php echo '$' . number_format($amount, 2); ?></span>
				
				<?php
				
				include("receipt-email-footer.php");
				
				$message = ob_get_contents();
				
				$subject = "Way to Grow Donation Receipt";
				$headers[] = 'From: "' . html_entity_decode(get_bloginfo('name')) . '" <' . get_bloginfo('admin_email') . '>';
				$headers[] = "MIME-Version: 1.0\r\n";
				$headers[] = 'Content-Type: text/html; charset=UTF-8';
				
				ob_end_clean();
				
				//$mail_sent = wp_mail($email, $subject, $message, $headers);
				
				$sendResult = $postmark_client->sendEmail(
					"sender@example.com",
					"receiver@example.com",
					"Test",
					"Hello from Postmark!"
				);
				
			}
			
			// failed payment

			if($event->type == 'charge.failed') {
				// send a failed payment notice email here
				
				// retrieve the payer's information
				$customer = Stripe_Customer::retrieve($invoice->customer);
				$email = $customer->email;
									
				$subject = __('Failed Payment', 'pippin_stripe');
				$headers = 'From: "' . html_entity_decode(get_bloginfo('name')) . '" <' . get_bloginfo('admin_email') . '>';
				$message = "Hello " . $customer_name . "\n\n";
				$message .= "We have failed to process your payment of " . $amount . "\n\n";
				$message .= "Please get in touch with support.\n\n";
				$message .= "Thank you.";

				//wp_mail($email, $subject, $message, $headers);
			}

			
		} catch (Exception $e) {
			// something failed, perhaps log a notice or email the site admin
		}
		
	}
	
?>