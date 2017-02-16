<?php
	
if($_SERVER["HTTPS"] != "on") {
	die();
}

header('Content-Type: application/json');

require_once dirname( __FILE__ ) . '/config.php';
require_once('./vendor/autoload.php');

\Stripe\Stripe::setApiKey($stripe_secret);

$create_error = false;

$token_id = $_POST['id'];
$email = $_POST['email'];
$type = $_POST['type'];

//Do not process fee's higher than max amount
if ($type == 'amount' && $param > ($max_amount_accepted * 100)) {
	$result = array('result'=>'failure','message'=>'Sorry, we do not take credit card payments for more than $' . $max_amount_accepted . ' because of processing fees, please talk to us about issuing a check or some other method of payment.');
	echo json_encode($result, JSON_PRETTY_PRINT);
} else if ($type == 'amount' && $param <= 0) {
	$result = array('result'=>'failure','message'=>'Sorry, that\'s not how this works.');
	echo json_encode($result, JSON_PRETTY_PRINT);
}  else {
	try {
		if ($type == 'plan') {
			$plan = $_POST['plan'];
			$customer = \Stripe\Customer::create(array(
				"source" => $token_id,
				"description" => $email,
				"plan" => $plan,
				"email" => $email,
			));
		}
		if ($type == 'update') {
			$customerid = $_POST['customerid'];
			$customer = \Stripe\Customer::retrieve($customerid);
			if ($customer->email == $email) {
				$customer->source = $token_id;
				$customer->save();
			}
		}
		if ($type == 'amount') {
			$amount = $_POST['amount'];
			$customer = \Stripe\Customer::create(array(
				"source" => $token_id,
				"description" => $email,
				"email" => $email,
			));
			$charge = \Stripe\Charge::create(array(
				"amount" => $param,
				"currency" => "usd",
				"customer" => $customer->id
			));
		}
		
	} catch(Stripe_CardError $e) {
		// Since it's a decline, Stripe_CardError will be caught
		$body = $e->getJsonBody();
		$err  = $body['error'];
		/*
		print('Status is:' . $e->getHttpStatus() . "\n");
		print('Type is:' . $err['type'] . "\n");
		print('Code is:' . $err['code'] . "\n");
		print('Param is:' . $err['param'] . "\n");
		print('Message is:' . $err['message'] . "\n");
		*/
		$create_error = true;
	} catch (Stripe_InvalidRequestError $e) {
		// Invalid parameters were supplied to Stripe's API
		$create_error = true;
	} catch (Stripe_AuthenticationError $e) {
		// Authentication with Stripe's API failed (maybe you changed API keys recently)
		$create_error = true;
	} catch (Stripe_ApiConnectionError $e) {
		// Network communication with Stripe failed
		$create_error = true;
	} catch (Stripe_Error $e) {
		// Display a very generic error to the user, and maybe send yourself an email
		$create_error = true;
	} catch (Exception $e) {
		// Something else happened, completely unrelated to Stripe
		$create_error = true;
	}
	
	if ($create_error === false) {
		$result = array('result'=>'success','message'=>'Thanks, we have received your payment!');
		echo json_encode($result, JSON_PRETTY_PRINT);
	} else {
		if ($err) {
			$result = array('result'=>'failure','message'=>$err['message']);
		} else {
			$result = array('result'=>'failure','message'=>'Sorry, there was an error. Refresh and try again?');
		}
		
		echo json_encode($result, JSON_PRETTY_PRINT);
	}
}

?>