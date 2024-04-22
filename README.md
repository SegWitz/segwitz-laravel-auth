This package support API authentiction via OTP for users using discord channel and email.
Note: This package supports laravel 11 or greater.

Prerequisites: 
1- Laravel sanctum pckage should be installed (https://github.com/laravel/sanctum). Make sure User.php Model extends HasApiTokens trait.

2- If you want to use discord OTP this package should be installed (https://github.com/yashveersingh/laravel-discord-notifier). After installing this package Make sure DISCORD_WEBHOOK is present in .env file.

Instructions: 

Step 1- 
Package is not published yet to packagist so add this to composer.json in fresh new laravel project

    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/SegWitz/segwitz-laravel-auth"
        }
    ],

Step 2-
Run "composer require segwitz/auth"

For using discord OTP just add DISCORD_OTP=true to your .env file and then upon registering a new user OTP will be sent to discord channel. 
For channel selection please refer to documentation of prerequisites 2 (https://github.com/yashveersingh/laravel-discord-notifier)

For using email OTP just add EMAIL_OTP=true to your .env file and then upon registering a new user OTP will be sent to email.
Please note that SMTP credentials should be set in .env as per laravel's requirements (You can user mailtrap for testing purpose).

For using mobile OTP, just add SMS_OTP=true to your .env. This package already implemented Firemobile sms gateway to send sms so you will need credentials from the provider. Add these variables to your .env file
FIREMOBILE_USERNAME
FIREMOBILE_PASSWORD

APIS covered in this package
1- User Registration
2- User login
4- Forgot Password
5- Resend OTP
6- Verify OTP


Steps to follow:
1- Add otp and account_verified fields to laravel's default users migration
    $table->integer('otp')->nullable();
    $table->boolean('account_verified')->default(false);

2- Add otp to fillable and hidden arrays in User model.
3- Add account_verified to fillable array in User model.

2- If you want to authenticatie users using their mobile number add mobile_number also
    $table->double('mobile_number')->unique();
Dont forget to add mobile_number in fillable array in user model

3- If you want to authenticatie users using their username add username also
    $table->double('username')->unique();
Dont forget to add username in fillable array in user model

Note: Email filed is already present in laravel's default users migration. If you want to use only email then step-2 and step-3 can be omitted. 

API Usage details
A postman collection is already added in the root of this project having details of all the apis.

1- User Registration (baseURL/api/register)
name                    = required
email                   = required
password                = required
password_confirmation   = required
mobile_number           = optional
username                = optional

Upon registration if DISCORD_OTP, EMAIL_OTP and SMS_OTP are set to true, an OTP will be sent to discord channel and email address and mobile_number. 

2- Verify OTP (baseURL/api/verify/otp)
user_id                 = required (You will get user id from registration api)
otp                     = required (OTP sent to email or discord channel)
otp_type                = required (verify_account)

Upon successfull verification you will get user object and api access token.

3- Login (baseURL/api/verify/otp)
email/mobile_number/username = required (You can use any one of them depends upon your project requirement)
password                = required

Upon successfull login you will get user object and api access token.

4- Forgot password (baseURL/api/forgot/password)
email/mobile_number = required (You can use email or mobile_number as per your requiremnt)

Note: if you are using mobile_number then OTP should be configured.
OTP will be sent to email or mobile number. You can use verify OTP api as mentioned in step - 2. Please change otp_type=forgot_password.

5- Reset Password
user_id = required (you will get user_id from Forgot password api)
otp = required
password                = required
password_confirmation   = required


If you want to modify according to your needs you can publish the files using
