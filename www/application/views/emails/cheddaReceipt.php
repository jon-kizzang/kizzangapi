<title>Chedda Store Redemption Confirmation</title>       

    <br>

    <!-- ** START HEADER ** -->
    <table style="text-align: center;">
        <body>
            <tr>
                <td >
                    <a href="https://<?php echo getenv("ENV") == "dev" ? "dev." : ""; ?>kizzang.com/ref?id=csrr1&s=9&m=email&t=csrr1&c=1&d=1" target="_blank"> 
                        <img src="https://d1w836my735uqw.cloudfront.net/header_images/Header08_ExchangeConfirmation.jpg" style="max-width: 600px; width: 100%;" border="0" alt="">
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
                    <h2>
                        Your Chedda&#8482; Coins are being processed
                    </h2>
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
                        Prize: <?= $prize; ?>                        
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
                        The Chedda&#8482; Coins you redeemed have been received and are being processed.  If the contact information you provided is verified, your electronic gift card will be sent to this email address: <?= $email;?>.
                        <br>
                        <br>
                        If you have any questions, please email <a href="mailto:winners@kizzang.com">winners@kizzang.com</a>.  We will be glad to assist you through this process.
                        <br>
                        <br>
                        In the meantime, keep on playing for more cash, Chedda&#8482; Coins, and sweepstakes prizes at Kizzang&reg;.  We hope to see you again soon.
                    </span>
                </td>
            </tr>
        </body>
    </table>

    <br>