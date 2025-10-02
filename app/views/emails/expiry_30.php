<?php
/** Vars available:
 * $cemName, $recipientName, $graveLabel, $expiryDate, $messageLine, $isAdmin (bool), $subject
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title><?= htmlspecialchars($subject ?? 'Rental Reminder') ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="margin:0;padding:0;background:#f6f7fb;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f6f7fb;">
    <tr>
      <td align="center" style="padding:24px 12px;">
        <table role="presentation" width="620" cellpadding="0" cellspacing="0" style="max-width:620px;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 6px 24px rgba(0,0,0,.08);">
          <!-- Header -->
          <tr>
            <td style="background:#7d1532;color:#fff;padding:20px 24px;font-family:Arial,Helvetica,sans-serif;">
              <div style="font-size:18px;font-weight:700;letter-spacing:.3px;"><?= htmlspecialchars($cemName ?? 'Plaridel Public Cemetery') ?></div>
              <div style="font-size:13px;opacity:.9;margin-top:2px;">Rental Reminder — 30 Days Left</div>
            </td>
          </tr>

          <!-- Body -->
          <tr>
            <td style="padding:22px 24px 8px 24px;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">
              <div style="font-size:15px;margin-bottom:12px;">
                Hello <?= htmlspecialchars($recipientName ?? 'there') ?>,
              </div>

              <div style="font-size:14px;line-height:1.6;margin-bottom:12px;">
                <?= nl2br(htmlspecialchars($messageLine ?? '')) ?>
              </div>

              <table role="presentation" cellpadding="0" cellspacing="0" style="width:100%;margin:14px 0;background:#faf6f7;border:1px solid #f0e3e7;border-radius:8px;">
                <tr>
                  <td style="padding:12px 14px;font-family:Arial,Helvetica,sans-serif;font-size:13px;color:#4b5563;">
                    <div><strong>Grave:</strong> <?= htmlspecialchars($graveLabel ?? '') ?></div>
                    <?php if (!empty($expiryDate)): ?>
                      <div><strong>Expiry date:</strong> <?= htmlspecialchars($expiryDate) ?></div>
                    <?php endif; ?>
                    <div><strong>Status:</strong> Expires in 30 days</div>
                  </td>
                </tr>
              </table>

              <div style="font-size:12px;color:#6b7280;margin-top:10px;">
                This is an automated reminder. Please keep your records up to date.
              </div>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td style="padding:16px 24px 20px 24px;font-family:Arial,Helvetica,sans-serif;">
              <hr style="border:none;border-top:1px solid #eee;margin:0 0 10px 0;">
              <div style="font-size:12px;color:#9ca3af;line-height:1.5;">
                © <?= date('Y') ?> <?= htmlspecialchars($cemName ?? 'Plaridel Public Cemetery') ?>. All rights reserved.
              </div>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
