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
			if (isset($event_json) && $event->type == "invoice.payment_succeeded") {
				// send a payment receipt email here
				
				// retrieve the payer's information
				$customer = \Stripe\Customer::retrieve($event_json->data->object->customer);
				
				$email = 'jesse@switchbackinteractive.com'; //$customer->email;
				
				$amount = $event_json->data->object->amount / 100; // amount comes in as amount in cents, so we need to convert to dollars
				$donor_name = $customer->description;
				$card_type = $event_json->data->object->source->brand;
				$card_Last_four = $event_json->data->object->source->last4;
				
				ob_start();

				include(dirname( __FILE__ ) . "receipt-email-header.php");
				
				?>
				
				<span style="font-size:18px; color:#888888">Receipt - <?php echo date("F j, Y"); ?></span><br><br>
				<span style="font-size:30px; line-height:30px; text-align:center"><?php echo $donor_name; ?></span><br>
				<span style="font-size:30px; line-height:30px; text-align:center"><?php echo $card_type; ?> - <?php echo $card_Last_four; ?></span><br><br>
				<span style="font-size:18px; line-height:30px; text-align:center; color:#888888">Donation Total:</span><br>
				<span style="font-size:30px; line-height:30px; text-align:center"><?php echo '$' . number_format($amount, 2); ?></span>
				
				<?php
				
				include(dirname( __FILE__ ) . "receipt-email-footer.php");
				
				$message = ob_get_contents();
				
				$subject = "Way to Grow Donation Receipt";
				
				ob_end_clean();
				
				//$mail_sent = wp_mail($email, $subject, $message, $headers);
				
				$sendResult = $postmark_client->sendEmail(
					$postmark_from,
					$email,
					$subject,
					$message
				);
				
			}
			
			// failed payment

			if (isset($event_json) && $event->type == "invoice.payment_failed") {
				// send a failed payment notice email here
				
				echo "Got here<br>";
				
				// retrieve the payer's information
				$customer = \Stripe\Customer::retrieve($event_json->data->object->customer);
				
				echo $customer->email . "<br>";
				
				$email = 'jesse@switchbackinteractive.com'; //$customer->email;
				
				$amount = $event_json->data->object->amount / 100; // amount comes in as amount in cents, so we need to convert to dollars
				$donor_name = $customer->description;
				$card_type = $event_json->data->object->source->brand;
				$card_Last_four = $event_json->data->object->source->last4;
									
				$subject = 'Failed Payment';
				
				ob_start();

				include(dirname( __FILE__ ) . "/receipt-email-header.php");
				
				?>
				
				<span style="font-size:18px; color:#888888">Receipt - <?php echo date("F j, Y"); ?></span><br><br>
				<span style="font-size:30px; line-height:30px; text-align:center"><?php echo $donor_name; ?></span><br>
				<span style="font-size:30px; line-height:30px; text-align:center"><?php echo $card_type; ?> - <?php echo $card_Last_four; ?></span><br><br>
				<span style="font-size:18px; line-height:30px; text-align:center; color:#888888">Donation Total:</span><br>
				<span style="font-size:30px; line-height:30px; text-align:center"><?php echo '$' . number_format($amount, 2); ?></span>
				
				<?php
				
				include(dirname( __FILE__ ) . "/receipt-email-footer.php");
				
				$message = ob_get_contents();
				
				echo $message . "<br>";
				
				$sendResult = $postmark_client->sendEmail(
					$postmark_from,
					$email,
					$subject,
					$message
				);

				//wp_mail($email, $subject, $message, $headers);
			}

			
		} catch (Exception $e) {
			// something failed, perhaps log a notice or email the site admin
		}
		
	}
	
?>