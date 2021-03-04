<?php
// WooCommerce Traccar API Integration
// This is a work in progress.
// 20210207

// When a customer purchases a device or subscription from the WC store, an account will be created
// if statements need to be inserted, so that the account creation/email sending functions only run if the account does not exist
// Plan to set traccar device limits based upon current subscriptions
// Also to add device automatically to system, then link to user account, based on user input of the IMEI/tracker ID.

add_action('woocommerce_thankyou', 'wdm_send_order_to_ext');
function wdm_send_order_to_ext( $order_id ){

// Load PassWord file for smtp authentication.
require_once ('/var/www/pw.php');

    // get order object and order details
    $order = new WC_Order( $order_id );
    $userEmail = $order->billing_email;
    $phone = $order->billing_phone;
		//$name = $order->get_billing_first_name();
    $shipping_type = $order->get_shipping_method();
    $shipping_cost = $order->get_total_shipping();

    // set the address fields
    $user_id = $order->user_id;
    $address_fields = array(
				'country',
        'title',
        'first_name',
        'last_name',
        'company',
        'address_1',
        'address_2',
        'address_3',
        'address_4',
        'city',
        'state',
        'postcode');

    $address = array();
    if(is_array($address_fields)){
        foreach($address_fields as $field){
            $address['billing_'.$field] = get_user_meta( $user_id, 'billing_'.$field, true );
            $address['shipping_'.$field] = get_user_meta( $user_id, 'shipping_'.$field, true );
        }
    }

    // get coupon information (if applicable)
    $cps = array();
    $cps = $order->get_items( 'coupon' );

    $coupon = array();
    foreach($cps as $cp){
            // get coupon titles (and additional details if accepted by the API)
            $coupon[] = $cp['name'];
    }

    // get product details
    $items = $order->get_items();

    $item_name = array();
    $item_qty = array();
    $item_price = array();
    $item_sku = array();

    foreach( $items as $key => $item){
        $item_name[] = $item['name'];
        $item_qty[] = $item['qty'];
        $item_price[] = $item['line_total'];

        $item_id = $item['product_id'];
        $product = new WC_Product($item_id);
        $item_sku[] = $product->get_sku();
    }

    /* for online payments, send across the transaction ID/key. If the payment is handled offline, you could send across the order key instead */
    $transaction_key = get_post_meta( $order_id, '_transaction_id', true );
    $transaction_key = empty($transaction_key) ? $_GET['key'] : $transaction_key;

    // to test out the API, set $api_mode as ‘sandbox’
	//$api_mode = 'sandbox';
	if($api_mode == 'sandbox'){
			// sandbox URLs
			$sessionEndpoint = "api-testing-endpoint.com";
			$usersEndpoint = "api-testing-endpoint.com";
	}
	else{
			// production URLs
			$sessionEndpoint = "http://localhost:8082/api/session";
			$usersEndpoint = "http://localhost:8082/api/users";
	}

			// Password generator code
			// needs to be changed to hashing method for security.
			$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
			$userPassword = substr(str_shuffle($chars), 0, 10);

 			// setup the data which has to be sent
			$data = json_encode(array(

					'attributes' => array('speedUnit' => 'kmh'),
					'name' => $address['billing_first_name'],
					'email' => $userEmail,
					'phone' => $phone,
					'readonly' => false,
					'administrator' => false,
					'map' => 'osm',
					'latitude' => 0,
					'longitude' => 0,
					'zoom' => 0,
					'twelveHourFormat' => false,
					'coordinateFormat' => 'dd',
					'disabled' => false,
					'expirationTime' => null,
					'deviceLimit' => -1,
					'userLimit' => 0,
					'deviceReadonly' => false,
					'limitCommands' => false,
					'poiLayer' => '',
					'password' => $userPassword

			));

     // Send the API commands to Traccar using CURL.

		// Create session for Traccar
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $sessionEndpoint);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
		curl_setopt($ch, CURLOPT_COOKIEJAR, "/tmp/cookieFileName");
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, 'email='.$adminEmail.'&password='.$adminPassword);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

		$response = curl_exec ($ch);

  	curl_close ($ch);


		// Log into Traccar and create a new user, using the session created above.

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_COOKIEFILE, "/tmp/cookieFileName");
		curl_setopt($ch, CURLOPT_URL, $usersEndpoint);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

		$response = curl_exec ($ch);

		curl_close ($ch);

        // the handle response
        if (strpos($response,'ERROR') !== false) {
                print_r($response);
        } else {
                // success
        }

				// Send username/password over email.
        // This email should only be sent if the email/account does not appear within traccar.
        // Work in progress.
				require_once "Mail.php";
  
        $traccarAdminEmail = "hello@hellohello.com"

			  $from = $traccarAdminEmail;
			  $to = $userEmail;
			  $subject = "Thankyou for signing up to the Traccar Tracking Portal";
			  $body = "Hello ".$userEmail.",\n\nThank you for signing up to the Traccar Portal\n\nYour log in details are below\n\nTraccar Portal: https://your.portal.com \n\nLogin: ".$userEmail."\n\nPassword: ".$userPassword;

			  $headers = array
					('From' => $from,
			    'To' => $to,
			    'Subject' => $subject);
			  $smtp = Mail::factory('smtp',
			    array ('host' => $host,
			      'port' => $port,
			      'auth' => true,
			      'username' => $username,
			      'password' => $password));

			  $mail = $smtp->send($to, $headers, $body);

			  if (PEAR::isError($mail)) {
			    echo("<p>" . $mail->getMessage() . "</p>");
			   } else {
			    echo("<p>Message successfully sent!</p>");
			   }
 }

?>
