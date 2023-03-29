<?php

return array (
  'initiation' => array (
    'successful' => 'Payment Initiation request processed successfully.',
    'failed' => 'Whoops! Something went wrong during payment initiation process.',
    'invalid_cart' => 'Sorry! Cart Payload must be a valid JSON data in defined format as per documentation.',
    'non_unique_order_id' => 'Sorry! Provided Order ID matched with a previous Order ID. Kindly use a unique Order ID each time you request.',
    'store_not_found' => 'Sorry! Unable to find your store with given Store ID.',
    'store_credentials_doesnt_matched' => 'Sorry! The Store ID and Store Password combination is wrong.',
  ),
  'payment' => array (
    'invalid_token' => 'Sorry! The payment token is not a valid one.',
    'qr_not_found' => 'Sorry! Unable to process your payment request right now.',
    'order_id_not_found' => 'Sorry! No such Order ID belongs in payment gateway against your store.',
    'transaction_not_found' => 'Sorry! No transaction has been found against your Order ID.',
  ),
);