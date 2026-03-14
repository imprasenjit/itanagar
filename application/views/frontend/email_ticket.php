<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="x-apple-disable-message-reformatting" />
    <title>Email Marketing Example</title>
    <style type="text/css">
        @media screen and (max-width: 450px) {
            td {
                display: block;
                padding: 0;
            }

            .background_style_for_mobile {
                background-image: url('<?php echo base_url('assets/imglogo') . "/" . $value["range"]->logo; ?>');
                background-size: cover !important;
                background-position: top center !important;
                height: 300px !important;
            }

            .text_style_for_mobile {
                padding: padding:21px 10px 0px 603px !important;
                font-size: 36px !important;
                font-weight: bold !important;
                line-height: 24px !important;
            }
        }
    </style>
</head>

<body bgcolor="#e0e0e0" width="100%" style="margin: 0; -webkit-text-size-adjust:none;">
    <table width="970" align="center" border="0" cellpadding="0" cellspacing="0">
        <tr>
            <td>
                <p>Order ID : <?= $details['razorpay_order_id']; ?></p>
                <p>Payment ID : <?= $details['razorpay_payment_id']; ?></p>
            </td>
        </tr>
    </table>
    <?php foreach ($ticket_details as $key => $value) { ?>
        <table width="970" align="center" border="0" cellpadding="0" cellspacing="0">
            <tr>
                <td class="background_style_for_mobile" height="450" valign="top" background="<?php echo base_url('assets/imglogo') . "/" . $value["range"]->logo; ?>" bgcolor="#FFFFFF" style="background-position:center;background-size:cover;background-repeat:no-repeat;text-align:center;font-size:0;line-height:0;height:450px;">
                    <!--[if mso]>
  		  		<v:image xmlns:v="urn:schemas-microsoft-com:vml" style="behavior:url(#default#VML);display:inline-block;position:absolute;width:450pt;height:277.5pt;top:0;left:0;border:0;z-index:1;" src="<?php echo base_url('assets/imglogo') . "/" . $value["range"]->logo; ?>" />
  		  		<v:rect xmlns:v="urn:schemas-microsoft-com:vml" fill="true" stroke="false" style="border:0;display:inline-block;position:absolute;width:450pt;height:277.5pt;">
  		  		<v:fill opacity="0%" color="#FFFFFF" />
  		  		<v:textbox inset="0,0,0,0">
  		  		<![endif]-->
                    <div>
                        <div style="font-size:0;">
                            <table width="100%" align="center" border="0" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="padding:0px;">
                                        <table width="100%" align="center" border="0" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td class="text_style_for_mobile" style="font-family:sans-serif;font-size:30px;mso-height-rule:exactly;line-height:34px;mso-line-height-rule:exactly;color:#000000;text-align:center;padding:21px 10px 0px 603px;font-weight:bold"><?= $value["ticketNo"]; ?></td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <!--[if mso]>
  				</v:textbox>
  				</v:fill>
  				</v:rect>
  				<![endif]-->
                </td>
            </tr>
        </table>
    <?php } ?>
</body>

</html>