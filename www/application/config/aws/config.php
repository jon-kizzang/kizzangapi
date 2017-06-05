<?php

$config['emailBounces']                 = "https://sqs.us-east-1.amazonaws.com/176490119945/dev-ses-email-bounces-queue";
$config['kizzangRegistration']  = "https://sqs.us-east-1.amazonaws.com/176490119945/dev-sqs-registration-confirmation-queue";
$config['facebookRegistration'] = "https://sqs.us-east-1.amazonaws.com/176490119945/dev-sqs-fb-registration-queue";
$config['passwordReset']        = "https://sqs.us-east-1.amazonaws.com/176490119945/dev-sqs-password-reset-queue";
$config['bigGame21']            = "https://sqs.us-east-1.amazonaws.com/176490119945/dev-sqs-biggame21-queue";
$config['final3']               = "https://sqs.us-east-1.amazonaws.com/176490119945/dev-sqs-final3-queue";
$config['dailyShowdown']        = "https://sqs.us-east-1.amazonaws.com/176490119945/dev-sqs-daily-showdown-queue";
$config['winner']                       = "https://sqs.us-east-1.amazonaws.com/176490119945/dev-winner-updates";
$config['admin-winner']         = "https://sqs.us-east-1.amazonaws.com/176490119945/dev-create-winner";
$config['email']                       = "https://sqs.us-east-1.amazonaws.com/176490119945/dev-sqs-generic-email";


return array(
        // Bootstrap the configuration file with AWS specific features
        'includes' => array('_aws'),
        'services' => array(
                // All AWS clients extend from 'default_settings'. Here we are
                // overriding 'default_settings' with our default credentials and
                // providing a default region setting.
                'default_settings' => array(
                        'params' => array(
                                'key'    => 'AKIAIFFZF5DLPDSVI65A',
                                'secret' => '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',
                                'region' => 'us-east-1'
                        )
                )
        )
);
