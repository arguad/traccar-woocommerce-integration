# traccar-woocommerce-integration
# Integrate woocommerce payment system with Traccar gps tracking system
Traccar is the leading open source GPS tracking software. Vehicle, asset and personal tracking. Self hosting and cloud-based solution. Real time view, reports, notifications. 
Mission: Our mission is to provide sustainable free and open source GPS tracking solutions

# Project Traccar WooCommerce
Integrate WooCommerce + WooCommerce Subscriptions with Traccar [GPS Tracking System](https://www.gpyes.com.au "GPS Tracking System") 
The aim is for WooCommerce to programmatically create Traccar User accounts, create Traccar Devices using user input in a custom field, set Traccar User account device limits and then link the Traccar device to the Traccar account. 
The above behaviour will occur when a user purchases a subscription from my WC store.

# Goals
•	Add ‘Device Identification Number’ field (also known as the devices imei) to WooCommerce Checkout page
•	Add ‘Device Identification Number’ field(s) dynamically to WC Checkout page based upon the quantity of subscriptions the customer is purchasing during the checkout process.
•	Add a picture underneath the field ‘Device Identification Number’ on WC Checkout page.
•	Create Traccar User account over API
• Password can be generated securely. Use hashing, currently not using hashing.
•	Create Traccar Device over API
•	Set Traccar User device limit set based on WooCommerce Subscription quantity.
•	Link Traccar Device to Traccar User account

# Status
Work in progress. Needs additional thought put into the design and development.

# Password file
Code pulls in a password file, so you must create the password file in the designated location outlined in the code.

# Resources
Traccar uses the REST api. https://www.traccar.org/traccar-api/

Starting from version 3.0 Traccar server includes an API to access GPS tracking data from your own applications.
Documentation for the API can be found in the API Reference or the OpenAPI JSON spec file. 
There are a lot of tools available to automatically generate client code from OpenAPI format. For more information check the official OpenAPI website.

There are two authorization options:

Using session cookies (see session endpoint)
Standard HTTP authorization header
Official Traccar API documentation:

API Reference

# Who would be interested
This plugin would be good for any user of the traccar gps tracking system, looking to integrate a woocommerce store which is providing subscription based access to the tracking portal. 
