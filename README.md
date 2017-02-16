### Stripe Quick Payments

**Stripe Quick Payments** is a simple web application for giving clients specifically formatted URL's to allow for one time and reoccurring payments through Stripe.

####Functionality:

Allows for formatted URL's to be created and sent to clients such as these:

#####Reoccurring Plans: 
https://yoursite.com/payment/plan/{stripe_plan_id}

#####Update Credit Card:
https://yoursite.com/payment/update/{stripe_customer_id}

#####One Time Fee:
https://yoursite.com/payment/amount/54.95


####Installation:

1. Sign up for a Stripe account
2. Acquire a server with SSL protection
3. Upload contents of the git directory and run `composer install`
4. Duplicate config_example.php and rename to config.php
5. Enter in your Stripe keys into both config.php as well as stripe-payment.js
6. Change other variables in config.php as you want
7. You may need to edit .htaccess RewriteBase depending on where the application lies in your folder structure
8. Test it out and see how it works!