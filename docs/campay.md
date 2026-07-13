CamPay API
Introduction
Authentication
POST
Get access token
POST
Request Payment
POST
Payment Links
GET
Transaction Status
POST
Withdraw funds
GET
Webhook or Callback
GET
Get Application Balance
POST
Transaction History
POST
Utilities: Airtime Tansfer
GET
Utilities: Transaction Status
GET
Phone Number Holder Info
CamPay API
Online Payments Made Easy

Signup on our demo site: https://demo.campay.net

Login and register an application

Error codes reference

View More
Error code	Description
ER101
Invalid phone number. Ensure the number starts with the country code. e.g 237xxxxxxxxx
ER102
Unsupported Carrier phone number. Currently, only MTN and Orange phone numbers are accepted for mobile money
ER201
Invalid amount. Decimal numbers are NOT allowed. The Amount can be sent as integer or string
ER301
Insufficient balance. Trying to withdraw an amount which is above your current balance for the specific carrier
Authentication
There are two methods of authenticating requests.

Using a permanent access token that does not expire.

Using a temporary access token that expires and you can renew it.

Method 1
Permanent access token:
You can find your permanent access token under the APP KEYS section of your app.
Copy it and use it in the Authorisation header of your request. e.g

json
{
    Authorization: Token 69e2df238ea8d66f24018574c5de51e2b77d9abb,
    Content-Type: application/json
}
Method 2
Temporary access token:
POST
Get access token
https://demo.campay.net/api/token/
/token/
After registering your application from your account, we generate API access username and password for that application which will be used to get access tokens before you can perform operations on our API.

Response parameters
token : To be used in subsequent requests in the Authorization header.
expires_in : Shows when the token will expire. In seconds
Successful response status code:
200
HEADERS
Content-Type
application/json

Body
raw (json)
View More
json
{
    "username": "b-8OfojjDf35sqwli92G2t_EU4s7Oxxbp0wEWvX_ITTUUsIkSLiQWo7c_jG0ok8E59EM8A5OXVQ-Y1FeTtTRnw",
    "password": "hFOs7Ibg0yyN0fsDq8CFvI2QRZHmGgUMObyetKli0N07uZ8eq9d3Gr2GfjsQjvP9m5YtmQk7asBefJdNgwBzTg"
}
Example Request
Get access token
View More
php
<?php

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://demo.campay.net/api/token/',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>'{
    "username": "b-8OfojjDf35sqwli92G2t_EU4s7Oxxbp0wEWvX_ITTUUsIkSLiQWo7c_jG0ok8E59EM8A5OXVQ-Y1FeTtTRnw",
    "password": "hFOs7Ibg0yyN0fsDq8CFvI2QRZHmGgUMObyetKli0N07uZ8eq9d3Gr2GfjsQjvP9m5YtmQk7asBefJdNgwBzTg"
}',
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/json'
  ),
));

$response = curl_exec($curl);

curl_close($curl);
echo $response;
200 OK
Example Response
Body
Headers (12)
View More
json
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiIsInVpZCI6Mn0.eyJpYXQiOjE2MDM4MjQyODMsIm5iZiI6MTYwMzgyNDI4MywiZXhwIjoxNjAzODI3ODgzfQ.ufW8sCrf_W2RFpVvH6zri0l7pJLnkPXCZi1zc10ZvOg",
  "expires_in": 3600
}
GET
Phone Number Holder Info
https://demo.campay.net/api/holder_info/?phone_number=237XXXXXXXX
Check the name associated to a phone number

HEADERS
Content-Type
application/json

Authorization
Token eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiIsInVpZCI6Mn0.eyJpYXQiOjE2MDc0OTk4MDMsIm5iZiI6MTYwNzQ5OTgwMywiZXhwIjoxNjA3NTAzNDAzfQ.wDVpvyC00u5EX8KpddbNc8zbg43XFBrDYD7gJbeAc3w

PARAMS
phone_number
237XXXXXXXX

Example Request
Success
View More
php
<?php

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://demo.campay.net/api/holder_info/?phone_number=237XXXXXXXX',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'GET',
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/json',
    'Authorization: Token eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiIsInVpZCI6Mn0.eyJpYXQiOjE2MDc0OTk4MDMsIm5iZiI6MTYwNzQ5OTgwMywiZXhwIjoxNjA3NTAzNDAzfQ.wDVpvyC00u5EX8KpddbNc8zbg43XFBrDYD7gJbeAc3w'
  ),
));

$response = curl_exec($curl);

curl_close($curl);
echo $response;
200 OK
Example Response
Body
Headers (14)
{
  "full_name": "JOHN DOE"
}