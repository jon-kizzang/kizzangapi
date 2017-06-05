<title>Kizzang Prize Claim Documents Needed</title>       

    <br>

    <!-- ** START HEADER ** -->
    <table style="text-align: center;">
        <body>
            <tr>
                <td >
                    <a href="https://<?php echo getenv("ENV") == "dev" ? "dev." : ""; ?>kizzang.com/ref?id=pcd1&s=9&m=email&t=pcd1&c=1&d=1" target="_blank"> 
                        <img src="https://d1w836my735uqw.cloudfront.net/header_images/potential_winner_header.jpg" style="max-width: 600px; width: 100%;" border="0" alt="">
                    </a>
                </td>
            </tr>
        </body>
    </table>

    <br>
    <!-- ** END HEADER ** -->

    <!-- ** START BODY ** -->
    <table style="text-align: center;">
        <body>
            <tr>
                <td>        
                    <h1>
                        Prize Claim Documents Needed
                    </h1>
                </td>
            </tr>
        </body>
    </table>

    <br>

    <table  style="background-color: #537BBE;">
        <tbody>
            <tr>
                <td>
                    <span style="font-size: 14px; color: white;">
                        Prize Amount Total: $<?= number_format($amount, 2); ?>
                    </span>
                </td>
            </tr>
        </tbody>
    </table>

    <br>
    <table>
        <body>
            <tr>
                <td>
                    <span>
                        The total value of your prize winnings from Kizzang&reg; has reached $<?= number_format($total, 2); ?> this calendar year.  As a result, the IRS requires you to send Kizzang&reg; a completed W9-Misc earnings form and confirm your identity.  Kizzang&reg; will send you a 1099 form next year for tax purposes.
                    </span>
                </td>
            </tr>
        </body>
    </table>

    <br>

    <table width="500px" align="center" valign="middle" style="text-align:center; margin: 0px auto;">
        <a href="https://<?= getenv("SWF_SERVER_NAME"); ?>/w2redirect/<?= $uuid; ?>" target="_blank" style="text-decoration: none;"> 
          <div style="background-color: #61b606;
                          color: #ffffff;
                          display: block;
                          font-family: sans-serif;
                          font-size: 36px;
                          font-weight: bold;
                          text-align: center;
                          margin: 0px auto;
                          width: 500px;
                          max-width: 500px;
                          min-width: 200px;
                          border-radius: 40px;
                          height: 75px;
                          line-height: 75px;">COMPLETE DOCUMENTS
          </div>   
        </a>   
    </table>

    <br>

    <table>
        <body>
            <tr>
                <td>
                    <span>Please submit your Prize Claim Documents by <b><?= date('l, F j, Y g:i:s A', strtotime($expirationDate)); ?></b>, otherwise your prize will be forfeited.  You will be unable to play any games until the Prize Claim Documents are completed.  All winnings of $10,000 or less will be paid via PayPal&#8482;.  It may take up to 90 days to process your prize claim.
                        <br>
                        <br>
                        If you have any questions, please email <a href="mailto:winners@kizzang.com">winners@kizzang.com</a>.  We will be glad to assist you through this process.
                        <br>
                        <br>
                        Thank you for playing at Kizzang&reg;!  We hope to see you again soon.
                    </span>
                </td>
            </tr>
        </body>
    </table>

    <br>
    <br>
    <!-- ** END BODY ** -->