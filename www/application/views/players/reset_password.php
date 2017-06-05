<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html style="width:100%;height:100%; background-color:#000;">
    <head>
        <style>
            body {
                height: 200px;
                width: 600px;
                background-color: #fff;
                margin: 20px auto 0;
            }
        </style>
    </head>
    <body>
        <div id="email_body_wrap">
            <div style="height: 66px;width: 600px;float:left;">

                <div style="background-color:#e2f4ff; width: 600px; height: 100px;">
                    <h5 style="text-align: center;">The reset token: <?php echo $resetToken; ?></h5>
                    
                    <h5 style="text-align: center;"> Please click the link below to reset password.</h5>
                    <h5 style="text-align: center;">
                        <a href="http://KizzangWebLoadBalancer-692873250.us-east-1.elb.amazonaws.com/accounts/reset_password/<?php echo $resetToken;?>">Reset password</a>
                    </h5>
                </div>
                <p style="text-align:center;font-size:10px;">NO PURCHASE NECESSARY.<br />
                    Kizzang is a promotional provider of Sweepstakes Entertainment.<br />
                    Copyright 2014 Kizzang. All Rights Reserved.
                </p>
            </div>
        </div>
    </body>
</html>