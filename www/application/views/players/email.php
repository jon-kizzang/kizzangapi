<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html style="width:100%;height:100%; background-color:#000;">
    <head>
        <style>
            body {
                height: 432px;
                width: 600px;
                background-color: #fff;
                margin: 20px auto 0;
            }
        </style>
    </head>
    <body>
        <div id="email_body_wrap">
            <div style="height: 66px;width: 600px;float:left;">
                <div style="background-color:#e2f4ff; width: 600px; height: 262px;">
                    <h3 style="text-align: center; margin: 0px; white-space:nowrap; padding-top: 40px; font-size: 23px; font-family: Trebuchet MS; text-shadow: 0px 2px 2px silver;">
                        Thank you for registering with Kizzang!
                    </h3>
                    <h4 style="text-align: center;">
                        In order to complete your registration, <a href="http://KizzangWebLoadBalancer-692873250.us-east-1.elb.amazonaws.com/accounts/emailConfirm/<?php echo $emailCode; ?>">CLICK THIS LINK</a> to confirm your email address.
                    </h4>
                    <h5 style="text-align: center; margin: 0px auto; color: red; font-weight: 200; width: 70%;">
                        Your registration is not complete and you will not be able to access your account until you complete this step.
                    </h5>
                    <div style="width:70%;margin: 0 auto;">
                        <a style="padding-top:10px;text-align: center; margin: 0px auto; color: #000; font-weight: 200; width: 70%;" href="<?php echo $baseUrl; ?>/account/reset">Please click here to request for new password.</a>
                    </div>
                    <h5 style="text-align: center;"> Please ignore this email if you believe it to be a mistake.</h5>
                    <h5 style="text-align: center;">
                        <a href="http://KizzangWebLoadBalancer-692873250.us-east-1.elb.amazonaws.com/accounts/optout/<?php echo $emailCode; ?>">Kizzang Email Opt-Out</a>
                    </h5>
                </div>
                <p style="text-align:center;font-size:10px;">NO PURCHASE NECESSARY.<br />
                    Kizzang is a promotional provider of Sweepstakes Entertainment.<br />
                    Copyright 2016 Kizzang. All Rights Reserved.
                </p>
            </div>
        </div>
    </body>
</html>