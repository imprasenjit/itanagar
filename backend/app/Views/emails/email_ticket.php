<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="x-apple-disable-message-reformatting" />
    <title>Payment Successful – Itanagarchoice</title>
</head>
<body style="margin:0;padding:0;background-color:#f3f4f6;-webkit-text-size-adjust:none;">

<?php
$font = "-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif";
?>

<table width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#f3f4f6">
  <tr>
    <td align="center" style="padding:32px 16px 64px;">

      <table width="600" cellpadding="0" cellspacing="0" border="0" style="width:100%;max-width:600px;">

        <!-- ── Success header card ── -->
        <tr>
          <td bgcolor="#ffffff" style="background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;padding:40px 32px;text-align:center;">

            <!-- Checkmark circle -->
            <table cellpadding="0" cellspacing="0" border="0" align="center" style="margin:0 auto 20px;">
              <tr>
                <td width="80" height="80"
                    bgcolor="#d1fae5"
                    style="width:80px;height:80px;border-radius:40px;border:2px solid #a7f3d0;text-align:center;vertical-align:middle;font-size:36px;line-height:80px;color:#10b981;">
                  ✓
                </td>
              </tr>
            </table>

            <h1 style="margin:0 0 8px;font-size:24px;font-weight:700;color:#111827;font-family:<?= $font ?>;">Payment Successful!</h1>
            <p  style="margin:0 0 24px;font-size:14px;color:#6b7280;font-family:<?= $font ?>;">Your tickets have been confirmed. Good luck! 🍀</p>

            <!-- Order / Payment IDs -->
            <table width="100%" cellpadding="0" cellspacing="0" border="0"
                   style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:12px;">
              <tr>
                <td style="padding:16px;">
                  <table width="100%" cellpadding="0" cellspacing="0" border="0">
                    <?php if (!empty($details['razorpay_order_id'])): ?>
                    <tr>
                      <td style="font-size:14px;color:#6b7280;font-family:<?= $font ?>;padding-bottom:8px;white-space:nowrap;">Order ID</td>
                      <td align="right" style="font-size:12px;color:#1f2937;font-family:monospace,monospace;word-break:break-all;padding-bottom:8px;"><?= esc($details['razorpay_order_id']) ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if (!empty($details['razorpay_payment_id'])): ?>
                    <tr>
                      <td style="font-size:14px;color:#6b7280;font-family:<?= $font ?>;white-space:nowrap;">Payment ID</td>
                      <td align="right" style="font-size:12px;color:#1f2937;font-family:monospace,monospace;word-break:break-all;"><?= esc($details['razorpay_payment_id']) ?></td>
                    </tr>
                    <?php endif; ?>
                  </table>
                </td>
              </tr>
            </table>

            <p style="margin:16px 0 0;font-size:12px;color:#9ca3af;font-family:<?= $font ?>;">A confirmation email has been sent to your registered email address.</p>
          </td>
        </tr>

        <?php if (!empty($ticket_details)): ?>

        <tr><td height="24"></td></tr>

        <!-- ── Tickets heading ── -->
        <tr>
          <td style="padding:0 4px 10px;">
            <p style="margin:0;font-size:11px;font-weight:600;color:#374151;text-transform:uppercase;letter-spacing:0.08em;font-family:<?= $font ?>;">Your Tickets</p>
          </td>
        </tr>

        <?php foreach ($ticket_details as $ticket): ?>
          <?php
            $range    = $ticket['range'];
            $webInfo  = $ticket['webInfo'];
            $ticketNo = $ticket['ticketNo'] ?? '';

            // Support both object (CI Query result) and array
            $gameName   = is_object($webInfo) ? ($webInfo->name       ?? '') : ($webInfo['name']        ?? '');
            $heading    = is_object($range)   ? ($range->heading      ?? '') : ($range['heading']       ?? '');
            $logo       = is_object($range)   ? ($range->logo         ?? '') : ($range['logo']          ?? '');
            $resultDate = is_object($range)   ? ($range->result_date  ?? '') : ($range['result_date']   ?? '');
            $price      = is_object($range)   ? ($range->price        ?? '') : ($range['price']         ?? '');

            $logoUrl       = $logo ? base_url('imglogo') . '/' . $logo : '';
            $formattedDate = $resultDate ? date('d M Y', strtotime($resultDate)) : '';
            $formattedPrice = $price ? '&#8377;' . number_format((float)$price, 0, '.', ',') : '';
            $shortHeading  = $heading ? (mb_strlen($heading) > 120 ? mb_substr($heading, 0, 120) . '…' : $heading) : '';
          ?>

        <!-- ── Ticket card ── -->
        <tr>
          <td bgcolor="#ffffff"
              style="background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;">

            <!-- Banner -->
            <?php if ($logoUrl): ?>
            <!-- Logo banner with background image -->
            <table width="100%" cellpadding="0" cellspacing="0" border="0"
                   background="<?= esc($logoUrl) ?>"
                   style="background-image:url('<?= esc($logoUrl) ?>');background-size:cover;background-position:center center;min-height:160px;">
              <tr>
                <td style="background:linear-gradient(to top,rgba(0,0,0,0.72) 0%,rgba(0,0,0,0.15) 55%,transparent 100%);min-height:160px;padding:12px;">
                  <table width="100%" cellpadding="0" cellspacing="0" border="0">
                    <!-- Top row: spacer + ticket badge -->
                    <tr>
                      <td style="min-height:80px;">&nbsp;</td>
                      <td align="right" valign="top">
                        <table cellpadding="0" cellspacing="0" border="0"
                               style="background:rgba(255,255,255,0.92);border-radius:12px;">
                          <tr>
                            <td style="padding:6px 12px;text-align:right;">
                              <p style="margin:0;font-size:10px;color:#6b7280;font-weight:500;text-transform:uppercase;letter-spacing:0.05em;font-family:<?= $font ?>;">Ticket No.</p>
                              <p style="margin:2px 0 0;font-size:20px;font-weight:700;color:#111827;font-family:monospace,monospace;line-height:1.2;"><?= esc($ticketNo) ?></p>
                            </td>
                          </tr>
                        </table>
                      </td>
                    </tr>
                    <!-- Bottom row: game name + heading -->
                    <tr>
                      <td colspan="2" style="padding-top:32px;">
                        <p style="margin:0;font-size:17px;font-weight:700;color:#ffffff;font-family:<?= $font ?>;line-height:1.3;"><?= esc($gameName) ?></p>
                        <?php if ($shortHeading): ?>
                        <p style="margin:4px 0 0;font-size:12px;color:rgba(255,255,255,0.8);font-family:<?= $font ?>;line-height:1.4;"><?= esc($shortHeading) ?></p>
                        <?php endif; ?>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>

            <?php else: ?>
            <!-- Fallback gradient banner (no logo) -->
            <table width="100%" cellpadding="0" cellspacing="0" border="0"
                   bgcolor="#4f46e5"
                   style="background:linear-gradient(to right,#6366f1,#4338ca);">
              <tr>
                <td style="padding:20px 20px;">
                  <table width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                      <td valign="middle">
                        <p style="margin:0;font-size:17px;font-weight:700;color:#ffffff;font-family:<?= $font ?>;"><?= esc($gameName) ?></p>
                        <?php if ($shortHeading): ?>
                        <p style="margin:4px 0 0;font-size:12px;color:rgba(255,255,255,0.8);font-family:<?= $font ?>;"><?= esc($shortHeading) ?></p>
                        <?php endif; ?>
                      </td>
                      <td align="right" valign="middle" style="padding-left:16px;white-space:nowrap;">
                        <table cellpadding="0" cellspacing="0" border="0"
                               style="background:rgba(255,255,255,0.2);border-radius:12px;">
                          <tr>
                            <td style="padding:6px 12px;text-align:right;">
                              <p style="margin:0;font-size:10px;color:rgba(255,255,255,0.7);text-transform:uppercase;letter-spacing:0.05em;font-family:<?= $font ?>;">Ticket No.</p>
                              <p style="margin:2px 0 0;font-size:20px;font-weight:700;color:#ffffff;font-family:monospace,monospace;line-height:1.2;"><?= esc($ticketNo) ?></p>
                            </td>
                          </tr>
                        </table>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
            <?php endif; ?>

            <!-- Details row: Draw Date | Price | Confirmed -->
            <table width="100%" cellpadding="0" cellspacing="0" border="0"
                   bgcolor="#ffffff"
                   style="background:#ffffff;border-top:1px solid #f3f4f6;">
              <tr>
                <td style="padding:12px 16px;">
                  <table width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                      <?php if ($formattedDate): ?>
                      <td valign="middle">
                        <p style="margin:0;font-size:10px;color:#9ca3af;text-transform:uppercase;letter-spacing:0.05em;font-family:<?= $font ?>;">Draw Date</p>
                        <p style="margin:2px 0 0;font-size:14px;font-weight:600;color:#1f2937;font-family:<?= $font ?>;"><?= esc($formattedDate) ?></p>
                      </td>
                      <?php endif; ?>

                      <?php if ($formattedPrice): ?>
                      <td valign="middle" style="padding-left:16px;white-space:nowrap;">
                        <p style="margin:0;font-size:10px;color:#9ca3af;text-transform:uppercase;letter-spacing:0.05em;font-family:<?= $font ?>;">Price</p>
                        <p style="margin:2px 0 0;font-size:14px;font-weight:600;color:#1f2937;font-family:<?= $font ?>;"><?= $formattedPrice ?></p>
                      </td>
                      <?php endif; ?>

                      <!-- Confirmed badge -->
                      <td align="right" valign="middle" style="padding-left:16px;white-space:nowrap;">
                        <table cellpadding="0" cellspacing="0" border="0"
                               style="background:#ecfdf5;border:1px solid #a7f3d0;border-radius:8px;display:inline-table;">
                          <tr>
                            <td style="padding:4px 10px;font-size:12px;font-weight:600;color:#065f46;font-family:<?= $font ?>;">
                              ✓ Confirmed
                            </td>
                          </tr>
                        </table>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>

          </td>
        </tr>
        <tr><td height="16"></td></tr>

        <?php endforeach; ?>
        <?php endif; ?>

      </table>
    </td>
  </tr>
</table>

</body>
</html>
<?php /* ── end email_ticket.php ── */ ?>