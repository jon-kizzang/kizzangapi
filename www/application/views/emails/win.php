<title>You are a potential winner at Kizzang!</title>       

    <br>

    <!-- ** START HEADER ** -->
    <table style="text-align: center;">
        <tbody>
            <tr>
                <td >
                    <a href="https://<?php echo getenv("ENV") == "dev" ? "dev." : ""; ?>kizzang.com/ref?id=claim1&s=9&m=email&t=claim1&c=1&d=1" target="_blank"> 
                        <img src="https://d1w836my735uqw.cloudfront.net/header_images/potential_winner_header.jpg" style="max-width: 600px; width: 100%;" border="0" alt="">
                    </a>
                </td>
            </tr>
        </tbody>
    </table>

    <br>
    <!-- ** END HEADER ** -->

    <!-- ** START BODY ** -->
   
    <table  style="background-color: #537BBE;">
        <tbody>
            <tr>
                <td>
                    <span style="font-size: 14px; color: white;">
                        Prize: <?= $prize; ?>
                        <br>
                        Game Name: <?= $game; ?>
                        <br>
                        Serial Number: <?= $serialNumber; ?>
                        <br>
                        Entry Number: <?= $winnerId; ?>
                    </span>
                </td>
            </tr>
        </tbody>
    </table>

    <br>
    <?php if($uuid) : ?>
    <table>
        <tbody>
            <tr>
                <td>
                    <span>
                        To start the validation process, please click the button below within <b>48 hours</b>.
                    </span>
                </td>
            </tr>
        </tbody>
    </table>

    <br>
    
    <table width="450px" align="center" valign="middle" style="text-align:center; margin: 0px auto;">
        <tr>
            <td>
                <a href="https://<?= getenv("SWF_SERVER_NAME"); ?>/w2redirect/<?= $uuid; ?>" target="_blank" style="text-decoration: none;"> 
                    <div style="background-color: #61b606;
                                    color: #ffffff;
                                    display: block;
                                    font-family: sans-serif;
                                    font-size: 36px;
                                    font-weight: bold;
                                    text-align: center;
                                    margin: 0px auto;
                                    width: 450px;
                                    max-width: 450px;
                                    min-width: 450px;
                                    border-radius: 40px;
                                    height: 75px;
                                    line-height: 75px;">CLAIM MY PRIZE!
                    </div>   
                </a>   
            </td>
        </tr>
    </table>
    <?php endif; ?>
    
    <br>
    <br>

    <table>
        <tbody>
            <tr>
                <td>
                    <span>
                        Your personal information must match the information you provided when you registered for Kizzang&reg;.  
                        <br>
                        <br>
                        Payments are processed via PayPal. It may take up to 90 days to process your prize claim.
                    </span>
                </td>
            </tr>
        </tbody>
    </table>
    <br>
    <br>
    <!-- ** END BODY ** -->
