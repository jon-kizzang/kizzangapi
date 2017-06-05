<html><head>

<style>
/*    table, td, th, tr {
        border: 1px solid black;
        padding: 0px;
    }*/

    table {
        max-width: 600px;
        width: 100%;
        text-align: left;
        margin-top: 0px;
        margin-bottom: 0px;
        margin-left: auto;
        margin-right: auto;
    }

    h1 {
        font-family: arial;
        font-size: 36px;
        font-style: normal;
        font-weight: bold;
        text-align: left;
        text-decoration: none;
        color: #345E9E;
        display: inline;
    }

    h2 {
        font-family: arial;
        font-size: 28px;
        font-style: normal;
        font-weight: bold;
        text-align: left;
        text-decoration: none;
        color: #345E9E;
        display: inline;
    }

    span {
        font-family: arial;
        font-size: 16px;
        font-style: normal;
        text-align: left;
        color: #000000;
        text-decoration: none;
        font-weight: normal;
    }

    span.info {
        font-style: italic;
    }

    span.footer {
        font-size: 12px;
        word-wrap: break-word;
        padding-left: 10px;
        padding-right: 10px;
        color: #A1A1A1
    }

    table.social_footer {
        color: #606060; 
        font-family: Arial; 
        font-size: 11px; 
        text-align: center; 
        text-decoration: none;
    }

</style>

</head><body>
<?= $content; ?>
 <!-- ** START FOOTER ** -->
    <table style="text-align: center;">
        <tbody>
            <tr>
                <td>
                    <!-- ** PRIVACY TEXT ** -->
                    <span class="footer">
                        &copy; 2016 Kizzang&reg;. All rights reserved.  <b>NO PURCHASE NECESSARY.</b> Must be a legal resident of the United States, residing in one of the 50 states or District of Columbia. Must be 18 years of age or older to participate.  In the event that you are less than the age of majority in your state of primary residence (nineteen (19) in Alabama and Nebraska and twenty-one (21) in Mississippi) you must have a parent/legal guardian’s permission to participate.
                    </span>
                    <br>
 
                    <!-- ** LEGAL ** -->
                    <span class="footer">
                        Android, Google Play and the Google Play logo are trademarks of Google Inc. Apple, the Apple logo, and iPhone are trademarks of Apple Inc., registered in the U.S. and other countries. App Store is a service mark of Apple Inc.
                    </span>
                    <br>
 
                    <!-- ** OPT OUT KEY ** -->
                    <span class="footer">
                        Don't want to receive these promotional reminder emails? 
                        <a href="https://<?php echo getenv("ENV") == "dev" ? "dev." : ""; ?>kizzang.com/accounts/optout/<?= $emailCode; ?>" >Click here to unsubscribe.</a>
                    </span>
                    <br>
 
                    <!-- ** ADDRESS ** -->
                    <span class="footer">
                        Kizzang&reg; LLC  |  PO Box 82160  |  Las Vegas, NV 89180  |  customerservice@kizzang.com<br>
                    </span>
                    <br>          
                </td>
            </tr>
        </tbody>
    </table>
    <!-- ** END FOOTER ** -->

</body></html>
