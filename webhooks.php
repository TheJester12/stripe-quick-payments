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
			if (isset($event_json) && $event_json->type == "invoice.payment_succeeded") {
				
				
				// retrieve the payer's information
				try {
					$customer = \Stripe\Customer::retrieve($event_json->data->object->customer);
					$customer_name = $customer->description;
					$customer_email = $customer->email;
				} catch (Exception $e) {
					$customer = null;
					$customer_name = null;
					$customer_email = null;
                }
                
                $receipt_email_to = $admin_email . "," . $customer_email;
				
				$period_end = date('n/j/Y',$event_json->data->object->period_end);
				$period_start = date('n/j/Y',$event_json->data->object->period_start);
								
				$total = $event_json->data->object->total / 100; // amount comes in as amount in cents, so we need to convert to dollars
				$subtotal = $event_json->data->object->subtotal / 100;
				$has_discount = false;
				if (isset($event_json->data->object->discount)) {
					$has_discount = true;
					$percent_off = $event_json->data->object->discount->coupon->percent_off / 100;
					$discount = ($subtotal * $percent_off) * -1;
				}
								
				ob_start();

				include(dirname( __FILE__ ) . "/receipt-email-header.php");
				
				?>
				
				<!-- start copy -->
		          <tr>
		            <td bgcolor="#ffffff" align="left" style="padding: 40px; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;text-align: left;color:#58585a;">
			            <h1 style="text-align: center;margin-top:0px;font-size:28px;"><?php echo $receipt_headline; ?></h1>         	
					  	<?php echo $receipt_intro; ?>
					  	<p><?php echo $customer_name; ?></p>
						<?php if (isset($event_json->data->object->description) && $event_json->data->object->description !== '') { ?>
							<p><?php echo $event_json->data->object->description; ?></p>
						<?php } ?>
					  	<p><strong>Period: <?php echo $period_start; ?> - <?php echo $period_end; ?></strong></p>
						<table cellpadding="0" cellspacing="0" border="0" style="margin: 30px auto;width: 100%;border: 1px solid #EDEFF2;">
							<tr>
								<th style="width:50%;margin: 0;border: 1px solid #EDEFF2;padding:5px 10px;font-size: 12px;line-height: 14px;text-transform: uppercase;color:#aaaaaa;text-align:left;">
									Description
								</th>
								<th style="width:10%;margin: 0;border: 1px solid #EDEFF2;padding:5px 10px;font-size: 12px;line-height: 14px;text-transform: uppercase;color:#aaaaaa;text-align:right;">
									Qty
								</th>
								<th style="width:20%;margin: 0;border: 1px solid #EDEFF2;padding:5px 10px;font-size: 12px;line-height: 14px;text-transform: uppercase;color:#aaaaaa;text-align:right;">
									Unit price
								</th>
								<th style="width:20%;margin: 0;border: 1px solid #EDEFF2;padding:5px 10px;font-size: 12px;line-height: 14px;text-transform: uppercase;color:#aaaaaa;text-align:right;">
									Amount
								</th>
							</tr>
							<?php foreach($event_json->data->object->lines->data as $line_item) : ?>
								<?php
									$product = null;
									$product_name = null;
									//TODO: THis would be better to grab them all at once rather than one at a time... but most customers only have one or two products...
									try {
										$product = \Stripe\Product::retrieve($line_item->plan->product);
										$product_name = $product->name;
									} catch (Exception $e) {
										$product = null;
										$product_name = null;
									}
								?>
								<tr>
									<td style="color: #58585a;font-size: 15px;line-height: 18px;margin: 0;border: 1px solid #EDEFF2;padding:10px;text-align:left;">
										<?php echo $product_name; ?>
									</td>
									<td style="color: #58585a;font-size: 15px;line-height: 18px;margin: 0;border: 1px solid #EDEFF2;padding:10px;text-align:right;">
										<?php echo $line_item->quantity; ?>
									</td>
									<td style="color: #58585a;font-size: 15px;line-height: 18px;margin: 0;border: 1px solid #EDEFF2;padding:10px;text-align:right;">
										<?php echo '$' . number_format(($line_item->plan->amount / 100),2); ?>
									</td>
									<td style="color: #58585a;font-size: 15px;line-height: 18px;margin: 0;border: 1px solid #EDEFF2;padding:10px;text-align:right;">
										<?php echo '$' . number_format(($line_item->amount / 100),2); ?>
									</td>
								</tr>
							<?php endforeach; ?>
							<?php if ($has_discount) : ?>
								<tr>
									<td colspan="3" style="color: #58585a;font-size: 15px;line-height: 18px;margin: 0;border: 1px solid #EDEFF2;padding:10px;text-align:right;">
										<strong>Subtotal</strong>
									</td>
									<td style="color: #58585a;font-size: 15px;line-height: 18px;margin: 0;border: 1px solid #EDEFF2;padding:10px;text-align:right;">
										<strong><?php echo '$' . number_format($subtotal, 2); ?></strong>
									</td>
								</tr>
								<tr>
									<td colspan="3" style="color: #58585a;font-size: 15px;line-height: 18px;margin: 0;border: 1px solid #EDEFF2;padding:10px;text-align:right;">
										<strong>Discount</strong>
									</td>
									<td style="color: #58585a;font-size: 15px;line-height: 18px;margin: 0;border: 1px solid #EDEFF2;padding:10px;text-align:right;">
										<strong><?php echo '$' . number_format($discount, 2); ?></strong>
									</td>
								</tr>
							<?php endif; ?>
							<tr>
								<td colspan="3" style="color: #58585a;font-size: 15px;line-height: 18px;margin: 0;border: 1px solid #EDEFF2;padding:10px;text-align:right;">
									<strong>Total</strong>
								</td>
								<td style="color: #58585a;font-size: 15px;line-height: 18px;margin: 0;border: 1px solid #EDEFF2;padding:10px;text-align:right;">
									<strong><?php echo '$' . number_format($total, 2); ?></strong>
								</td>
							</tr>
						</table>
						<div style="text-align: center;margin-top:15px;"><a href="<?php echo $base_url; ?>update/<?php echo $event_json->data->object->customer; ?>/" target="_blank">Update Credit Card</a> | <a href="<?php echo $event_json->data->object->invoice_pdf; ?>" target="_blank">Download Invoice</a></div>
		            </td>
		          </tr>
		          <!-- end copy -->
				
				<?php
				
				include(dirname( __FILE__ ) . "/receipt-email-footer.php");
				
				$message = ob_get_contents();				
				
				ob_end_clean();
				
				// send a payment receipt email
				$sendResult = $postmark_client->sendEmail(
					$postmark_from,
					$receipt_email_to,
					$receipt_subject,
					$message
				);
				
				http_response_code(200);
				
			}
			
			// failed payment

			if (isset($event_json) && $event_json->type == "invoice.payment_failed") {
								
				// retrieve the payer's information
				try {
					$customer = \Stripe\Customer::retrieve($event_json->data->object->customer);
					$customer_name = $customer->description;
					$customer_email = $customer->email;
				} catch (Exception $e) {
					$customer = null;
					$customer_name = null;
					$customer_email = null;
                }
                
                $payment_failed_email_to = $admin_email . "," . $customer_email;
								
				$amount = $event_json->data->object->amount_due / 100; // amount comes in as amount in cents, so we need to convert to dollars
				$card_type = $event_json->data->object->default_source->brand;
				$card_Last_four = $event_json->data->object->source->last4;
				
				$period_end = date('n/j/Y',$event_json->data->object->period_end);
				$period_start = date('n/j/Y',$event_json->data->object->period_start);
				
				ob_start();

				include(dirname( __FILE__ ) . "/receipt-email-header.php");
				
				?>
				
				<!-- start copy -->
		          <tr>
		            <td bgcolor="#ffffff" align="left" style="padding: 40px 40px 0px 40px; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;text-align: center;color:#58585a;">
			            <h1 style="text-align: center;margin-top:0px;font-size:28px;"><?php echo $payment_failed_headline; ?></h1>
			            <?php echo $payment_failed_intro; ?>
			            <p><?php echo $customer_name; ?></p>
		              	<p><strong>Period: <?php echo $period_start; ?> - <?php echo $period_end; ?></strong></p>
			          	<p><?php echo $card_type; ?> - <?php echo $card_Last_four; ?></p>
						<p style="margin-bottom:0px;"><strong>Failed Charge: <?php echo '$' . number_format($amount, 2); ?></strong></p>
		            </td>
		          </tr>
		          <!-- end copy -->
		
		          <!-- start button -->
		          <tr>
		            <td align="left" bgcolor="#ffffff">
		              <table border="0" cellpadding="0" cellspacing="0" width="100%">
		                <tr>
		                  <td align="center" bgcolor="#ffffff" style="padding: 40px;">
		                    <table border="0" cellpadding="0" cellspacing="0">
		                      <tr>
		                        <td align="center" bgcolor="#31c0ce">
		                          <a href="<?php echo $base_url; ?>update/<?php echo $event_json->data->object->customer; ?>/" target="_blank" rel="noopener noreferrer" style="display: inline-block; padding: 16px 36px; font-family: Helvetica, Arial, sans-serif; font-size: 14px; color: #ffffff; text-decoration: none; text-transform:uppercase; font-weight:bold;">Update Credit Card</a>
		                        </td>
		                      </tr>
		                    </table>
		                  </td>
		                </tr>
		              </table>
		            </td>
		          </tr>
		          <!-- end button -->
				
				<?php
				
				include(dirname( __FILE__ ) . "/receipt-email-footer.php");
				
				$message = ob_get_contents();
				
				ob_end_clean();
				
				// send a failed payment notice email
				$sendResult = $postmark_client->sendEmail(
					$postmark_from,
					$payment_failed_email_to,
					$payment_failed_subject,
					$message
				);
				
				http_response_code(200);
				
			}
			
			// customer payment method updated

			if (isset($event_json) && $event_json->type == "customer.updated" && isset($event_json->data->previous_attributes->default_source)) {
				
				// retrieve the payer's information
				$customer_name = $event_json->data->object->description;
                $customer_email = $event_json->data->object->email;
                				
				ob_start();

				include(dirname( __FILE__ ) . "/receipt-email-header.php");
				
				?>
				
				<!-- start copy -->
		          <tr>
		            <td bgcolor="#ffffff" align="left" style="padding: 40px 40px 0px 40px; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;text-align: center;color:#58585a;">
			            <h1 style="text-align: center;margin-top:0px;font-size:28px;"><?php echo $payment_updated_headline; ?></h1>
			            <?php echo $payment_updated_intro; ?>
						<p><strong><?php echo $customer_email; ?></strong></p>
		            </td>
		          </tr>
		          <!-- end copy -->			          
				
				<?php
				
				include(dirname( __FILE__ ) . "/receipt-email-footer.php");
				
				$message = ob_get_contents();
				
				ob_end_clean();
				
				// send a payment method update email
				$sendResult = $postmark_client->sendEmail(
					$postmark_from,
					$admin_email,
					$payment_updated_subject,
					$message
				);
				
				http_response_code(200);
								
			}

			
		} catch (Exception $e) {
			// something failed, perhaps log a notice or email the site admin
			
			http_response_code(500);
		}
		
	}
	
?>