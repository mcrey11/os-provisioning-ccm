<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="format-detection" content="telephone=no, date=no, address=no, email=no">
    <style type="text/css">
        html, body {
            margin: 0;
            padding: 0;
            -webkit-text-size-adjust: none;
            -ms-text-size-adjust: none;
        }
        .appleLinks a {
            color: #333 !important;
            text-decoration: none;
        }
    </style>
</head>
<body bgcolor="#EBEBEB">
    <table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#EBEBEB">
        <tr>
            <td align="center" style="padding: 20px 0;">
                <table width="700" bgcolor="#fff" border="0" cellspacing="0" cellpadding="0" align="center" class="module" style="border: 1px solid #cdcdcd; border-radius: 3px; background: #fff;">
                    <tbody>
                        <tr>
                            <td style="border-collapse: collapse; border: 0px solid #cdcdcd;">
                                <!-- Top Spacing -->
                                <table border="0" width="700" class="module" cellpadding="0" cellspacing="0" align="center" style="border: 0;">
                                    <tbody>
                                        <tr>
                                            <td width="20" style="font-size: 20px; line-height: 20px;">&nbsp;<br></td>
                                        </tr>
                                    </tbody>
                                </table>

                                <!-- Header Section -->
                                <br>
                                {{--
                                <table width="700" border="0" cellpadding="0" cellspacing="0" align="center" class="module">
                                    <tbody>
                                        <tr>
                                            <td width="20" class="left-margin">&nbsp;</td>
                                            <td>
                                                <!-- Logo placeholder - can be replaced with actual logo if available -->
                                            </td>
                                        </tr>
                                        <tr>
                                            <td width="20" style="font-size: 25px; mso-line-height-rule: exactly; line-height: 25px;">&nbsp;<br></td>
                                        </tr>
                                    </tbody>
                                </table>
                                --}}

                                <!-- Main Content -->
                                <table width="800" border="0" cellpadding="0" cellspacing="0" align="center" class="module">
                                    <tbody>
                                        <tr>
                                            <td width="30" class="left-margin">&nbsp;</td>
                                            <td width="760" class="content">
                                                <!-- Main Heading -->
                                                <table width="760" class="content" border="0" cellspacing="0" cellpadding="0">
                                                    <tbody>
                                                        <tr>
                                                            <td style="font-family: Arial, Helvetica, sans-serif; font-size: 30px; mso-line-height-rule: exactly; line-height: 34px; color: #1b5a9d;">
                                                                {{ trans('billingbase::messages.invoiceNotification.subject') }}<br>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td width="10" style="font-size: 10px; mso-line-height-rule: exactly; line-height: 10px;">&nbsp;<br></td>
                                                        </tr>
                                                    </tbody>
                                                </table>

                                                <!-- Greeting -->
                                                <table width="760" class="content" border="0" cellspacing="0" cellpadding="0">
                                                    <tbody>
                                                        <tr>
                                                            <td style="font-size: 10px; mso-line-height-rule: exactly; line-height: 10px;">&nbsp;<br></td>
                                                        </tr>
                                                        <tr>
                                                            <td style="font-family: Arial, Helvetica, sans-serif; font-size: 20px; mso-line-height-rule: exactly; line-height: 25px; color: #333;">
                                                                <b>{{ $salutation }},</b><br>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td style="font-size: 10px; mso-line-height-rule: exactly; line-height: 10px;">&nbsp;<br></td>
                                                        </tr>
                                                    </tbody>
                                                </table>

                                                <!-- Body Content -->
                                                <table width="760" class="content" border="0" cellspacing="0" cellpadding="0">
                                                    <tbody>
                                                        <tr>
                                                            <td style="font-family: Arial, Helvetica, sans-serif; font-size: 14px; mso-line-height-rule: exactly; line-height: 20px; color: #333;">
                                                                {{ trans('billingbase::messages.invoiceNotification.intro') }}
                                                                <br><br>
                                                                {{ trans('billingbase::messages.invoiceNotification.infoTableHeader') }}
                                                                <br>
                                                                {{ "$contract->number | $invoiceNumbers | $invoiceDate | $totalGross" }}
                                                                <br><br>
                                                                {!! trans('billingbase::messages.invoiceNotification.portalLogin', ['url' => e($route)]) !!}
                                                                <br><br>
                                                                {{ trans('billingbase::messages.invoiceNotification.currentInvoiceHint') }}
                                                                <br><br>
                                                                {{ trans('billingbase::messages.invoiceNotification.closing') }}<br>
                                                                {{ $company->name }}<br>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                            <td width="20" class="right-margin">&nbsp;</td>
                                        </tr>
                                    </tbody>
                                </table>

                                <!-- Bottom Spacing -->
                                <table width="700" border="0" cellpadding="0" cellspacing="0" align="center" class="module" style="border-collapse: collapse;">
                                    <tbody>
                                        <tr>
                                            <td style="mso-line-height-rule: exactly; line-height: 20px;">&nbsp;</td>
                                        </tr>
                                    </tbody>
                                </table>
                                <table width="700" border="0" cellpadding="0" cellspacing="0" align="center" class="module" style="border-collapse: collapse;">
                                    <tbody>
                                        <tr>
                                            <td style="mso-line-height-rule: exactly; line-height: 30px;">&nbsp;</td>
                                        </tr>
                                    </tbody>
                                </table>

                                <!-- Divider -->
                                <table width="800" class="module" border="0" cellpadding="0" cellspacing="0" align="center">
                                    <tbody>
                                        <tr>
                                            <td width="660" style="border-collapse: collapse;">
                                                <table border="0" width="100%" cellpadding="0" cellspacing="0">
                                                    <tbody>
                                                        <tr>
                                                            <td style="background: #d7dfe3; width: 100%; mso-line-height-rule: exactly; line-height: 1px;">&nbsp;</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>

                                <!-- Signature / Footer -->
                                <table width="700" class="module" bgcolor="#f4f4f4" border="0" cellpadding="0" cellspacing="0" align="center" style="border: 0;">
                                    <tbody>
                                        <tr>
                                            <td style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; mso-line-height-rule: exactly; line-height: 18px; color: #333; padding: 10px;">
                                                {{ $provider }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
