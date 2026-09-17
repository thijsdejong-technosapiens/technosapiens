<?php

/**
 * @var string $formSubject
 * @var string $formMessage
 * @var array $formValues
 * @var string $formTitle
 * @var bool $showAllFields
 */

use TechnoSapiens\Core\Image;
use TechnoSapiens\GFMailSettings;
use TechnoSapiens\GravityFormMailComponent;

//get logo from settings
$logoWidth = 0;
$logoHeight = 0;
$logoImage = '';
$logoImageId = get_field('gf_logo_image', GFMailSettings::MENU_SLUG) ?? '';
if ($logoImageId) {
    $logoImage = Image::urlFromId($logoImageId);
    $logoDimensions = Image::dimensionsFromId($logoImageId);
    $logoWidth = $logoDimensions->width;
    $logoHeight = $logoDimensions->height;
}

//get the settings from Social Media
$gfSocialMediaEnabled = get_field('gf_social_media_enabled', GFMailSettings::MENU_SLUG) === true;
$gfSocialLinks = GFMailSettings::getGfSocialLinks();
$gfHasSocialLinks = $gfSocialMediaEnabled && count($gfSocialLinks) > 0;

//get the settings from the footer bar
$gfCopyrightEnabled = get_field('gf_copyright_text_enabled', GFMailSettings::MENU_SLUG) === true;
$gfFooterCopyright = get_field('gf_footer_bar_copyright', GFMailSettings::MENU_SLUG) ?: '';

$mailStyles = GravityFormMailComponent::getInstance();

//configure the background colors
$gfMailBodyBg = $mailStyles->getMailVariable('gf-mail-body-bg', '#f6f5f5');
$gfMailHeaderBg = $mailStyles->getMailVariable('gf-mail-header-bg', '#ffffff');
$gfMailMainBg = $mailStyles->getMailVariable('gf-mail-main-bg', '#ffffff');
$gfMailFooterBg = $mailStyles->getMailVariable('gf-mail-footer-bg', '#fce512');
$gfMailFooterBarBg = $mailStyles->getMailVariable('gf-mail-footer-bar-bg', '#ffffff');
$gfMailLabel = $mailStyles->getMailVariable('gf-mail-label', '#eeeeee');
$gfMailValue = $mailStyles->getMailVariable('gf-mail-value', '#ffffff');

//configure the text colors
$gfMailMainText = $mailStyles->getMailVariable('gf-mail-main-text', '#070733');
$gfMailLabelText = $mailStyles->getMailVariable('gf-mail-label-text', '#070733');
$gfMailValueText = $mailStyles->getMailVariable('gf-mail-value-text', '#070733');
$gfMailFooterBarText = $mailStyles->getMailVariable('gf-mail-footer-bar-text', '#070733');

//configure border
$gfMailBorder = $mailStyles->getMailVariable('gf-mail-border', '1px solid #eeeeee');

?>

<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml"
      xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <title>

    </title> <!--[if !mso]><!-->
    <meta http-equiv="X-UA-Compatible" content="IE=edge"> <!--<![endif]-->
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style type="text/css">
        #outlook a {
            padding: 0;
        }

        body {
            margin: 0;
            padding: 0;
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }

        table, td {
            border-collapse: collapse;
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }

        img {
            border: 0;
            height: auto;
            line-height: 100%;
            outline: none;
            text-decoration: none;
            -ms-interpolation-mode: bicubic;
        }

        p {
            display: block;
            margin: 13px 0;
        }
    </style>
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:AllowPNG/>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]--> <!--[if lte mso 11]>
    <style type="text/css">
        .mj-outlook-group-fix {
            width: 100% !important;
        }
    </style>
    <![endif]-->
    <style type="text/css">
        @media only screen and (min-width: 480px) {
            .mj-column-per-100 {
                width: 100% !important;
                max-width: 100%;
            }
        }
    </style>
    <style media="screen and (min-width:480px)">
        .moz-text-html .mj-column-per-100 {
            width: 100% !important;
            max-width: 100%;
        }
    </style>
    <style type="text/css">


        @media only screen and (max-width: 480px) {
            table.mj-full-width-mobile {
                width: 100% !important;
            }

            td.mj-full-width-mobile {
                width: auto !important;
            }
        }

    </style>
    <style type="text/css">

    </style>
</head>
<body style="word-spacing:normal;background-color:<?php echo $gfMailBodyBg ?>;">
<div style="background-color:<?php echo $gfMailBodyBg ?>;"><!--[if mso | IE]>
    <table align="center" border="0" cellpadding="0" cellspacing="0" class="" role="presentation" style="width:600px;"
           width="600">
        <tr>
            <td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
    <div style="margin:0px auto;max-width:600px;">
        <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
            <tbody>
            <tr>
                <td style="direction:ltr;font-size:0px;padding:0;text-align:center;"><!--[if mso | IE]>
                    <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                        <tr>
                            <td class="" style="vertical-align:top;width:600px;"><![endif]-->
                    <div class="mj-column-per-100 mj-outlook-group-fix"
                         style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
                        <table border="0" cellpadding="0" cellspacing="0" role="presentation" width="100%">
                            <tbody>
                            <tr>
                                <td style="vertical-align:top;padding:0;">
                                    <table border="0" cellpadding="0" cellspacing="0" role="presentation" style=""
                                           width="100%">
                                        <tbody>
                                        <tr>
                                            <td align="center" style="font-size:0px;padding:0;word-break:break-word;"><p
                                                    style="border-top:solid 20px <?php echo $gfMailBodyBg ?>;font-size:1px;margin:0px auto;width:100%;"></p>
                                                <!--[if mso | IE]><table align="center" border="0" cellpadding="0" cellspacing="0" style="border-top:solid 20px <?php echo $gfMailBodyBg ?>;font-size:1px;margin:0px auto;width:600px;" role="presentation" width="600px" ><tr><td style="height:0;line-height:0;"> &nbsp;
</td></tr></table><![endif]--></td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <!--[if mso | IE]></td></tr></table><![endif]--></td>
            </tr>
            </tbody>
        </table>
    </div> <!--[if mso | IE]></td></tr></table>
<?php if ($logoImage): ?>
<table align="center" border="0" cellpadding="0" cellspacing="0" class="" role="presentation" style="width:600px;" width="600" bgcolor="<?php echo $gfMailHeaderBg ?>" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
    <div
        style="background:<?php echo $gfMailHeaderBg ?>;background-color:<?php echo $gfMailHeaderBg ?>;margin:0px auto;max-width:600px;">
        <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation"
               style="background:<?php echo $gfMailHeaderBg ?>;background-color:<?php echo $gfMailHeaderBg ?>;width:100%;">
            <tbody>
            <tr>
                <td style="border-bottom:<?php echo $gfMailBorder ?>;direction:ltr;font-size:0px;padding:20px;text-align:center;">
                    <!--[if mso | IE]>
                    <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                        <tr>
                            <td class="" style="vertical-align:top;width:560px;"><![endif]-->
                    <div class="mj-column-per-100 mj-outlook-group-fix"
                         style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
                        <table border="0" cellpadding="0" cellspacing="0" role="presentation" width="100%">
                            <tbody>
                            <tr>
                                <td style="vertical-align:top;padding:0;">
                                    <table border="0" cellpadding="0" cellspacing="0" role="presentation" style=""
                                           width="100%">
                                        <tbody>
                                        <tr>
                                            <td align="center" style="font-size:0px;padding:0;word-break:break-word;">
                                                <table border="0" cellpadding="0" cellspacing="0" role="presentation"
                                                       style="border-collapse:collapse;border-spacing:0px;">
                                                    <tbody>
                                                    <tr>
                                                        <td style="width:225px;">

                                                            <img src="<?php echo $logoImage; ?>"
                                                                 width="100%"
                                                                 height="auto"
                                                                 alt="Logo <?php echo get_bloginfo('name'); ?>"/>
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
                            </tbody>
                        </table>
                    </div>
                    <!--[if mso | IE]></td></tr></table><![endif]--></td>
            </tr>
            </tbody>
        </table>
    </div> <!--[if mso | IE]></td></tr></table>
<?php endif; ?>
<table align="center" border="0" cellpadding="0" cellspacing="0" class="" role="presentation" style="width:600px;" width="600" bgcolor="<?php echo $gfMailMainBg ?>" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
    <div
        style="background:<?php echo $gfMailMainBg ?>;background-color:<?php echo $gfMailMainBg ?>;margin:0px auto;max-width:600px;">
        <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation"
               style="background:<?php echo $gfMailMainBg ?>;background-color:<?php echo $gfMailMainBg ?>;width:100%;">
            <tbody>
            <tr>
                <td style="border-bottom:<?php echo $gfMailBorder ?>;direction:ltr;font-size:0px;padding:20px;text-align:center;">
                    <!--[if mso | IE]>
                    <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                        <tr>
                            <td class="" style="vertical-align:top;width:560px;"><![endif]-->
                    <div class="mj-column-per-100 mj-outlook-group-fix"
                         style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
                        <table border="0" cellpadding="0" cellspacing="0" role="presentation" width="100%">
                            <tbody>
                            <tr>
                                <td style="vertical-align:top;padding:0;">
                                    <table border="0" cellpadding="0" cellspacing="0" role="presentation" style=""
                                           width="100%">
                                        <tbody>
                                        <?php if ($formSubject): ?>
                                            <tr>
                                                <td align="center"
                                                    style="font-size:0px;padding:0 0 10px;word-break:break-word;">
                                                    <div
                                                        style="font-family:Arial, sans-serif;font-size:20px;font-weight:700;line-height:25px;text-align:center;color:<?php echo $gfMailMainText ?>;">
                                                        <?php echo esc_html($formSubject); ?>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php if ($formMessage): ?>
                                                <tr>
                                                    <td align="center"
                                                        style="font-size:0px;padding:0 0 10px;word-break:break-word;">
                                                        <div
                                                            style="font-family:Arial, sans-serif;font-size:13px;font-weight:400;line-height:20px;text-align:center;color:<?php echo $gfMailMainText ?>;">
                                                            <?php echo wp_kses_post($formMessage); ?>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endif; ?>

                                        <?php endif; ?>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <!--[if mso | IE]></td></tr></table><![endif]--></td>
            </tr>
            </tbody>
        </table>
    </div> <!--[if mso | IE]></td></tr></table>
<?php if ($showAllFields): ?>
<table align="center" border="0" cellpadding="0" cellspacing="0" class="" role="presentation" style="width:600px;" width="600" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
    <div style="margin:0px auto;max-width:600px;">
        <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
            <tbody>
            <tr>
                <td style="direction:ltr;font-size:0px;padding:0;text-align:center;"><!--[if mso | IE]>
                    <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                        <tr>
                            <td class="" style="vertical-align:top;width:600px;"><![endif]-->
                    <div class="mj-column-per-100 mj-outlook-group-fix"
                         style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
                        <table border="0" cellpadding="0" cellspacing="0" role="presentation" width="100%">
                            <?php foreach ($formValues

                            as $formKey => $formValue): ?>

                            <tbody>
                            <tr>
                                <td style="background-color:<?php echo $gfMailLabel ?>;border-bottom:<?php echo $gfMailBorder ?>;vertical-align:top;padding:12px 15px;">
                                    <table border="0" cellpadding="0" cellspacing="0" role="presentation" style=""
                                           width="100%">
                                        <tbody>
                                        <tr>
                                            <td align="left" style="font-size:0px;padding:0;word-break:break-word;">
                                                <div
                                                    style="font-family:Arial, sans-serif;font-size:13px;font-weight:700;line-height:20px;text-align:left;color:<?php echo $gfMailValueText ?>;">
                                                    <?php echo esc_html($formKey); ?>
                                                </div>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <!--[if mso | IE]></td></tr></table><![endif]--></td>
            </tr>
            </tbody>
        </table>
    </div>
    <!--[if mso | IE]></td></tr></table>
    <table align="center" border="0" cellpadding="0" cellspacing="0" class="" role="presentation" style="width:600px;"
           width="600">
        <tr>
            <td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
    <div style="margin:0px auto;max-width:600px;">
        <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
            <tbody>
            <tr>
                <td style="direction:ltr;font-size:0px;padding:0;text-align:center;"><!--[if mso | IE]>
                    <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                        <tr>
                            <td class="" style="vertical-align:top;width:600px;"><![endif]-->
                    <div class="mj-column-per-100 mj-outlook-group-fix"
                         style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
                        <table border="0" cellpadding="0" cellspacing="0" role="presentation" width="100%">
                            <tbody>
                            <tr>
                                <td style="background-color:<?php echo $gfMailValue ?>;border-bottom:<?php echo $gfMailBorder ?>;vertical-align:top;padding:12px 15px;">
                                    <table border="0" cellpadding="0" cellspacing="0" role="presentation" style=""
                                           width="100%">
                                        <tbody>
                                        <tr>
                                            <td align="left" style="font-size:0px;padding:0;word-break:break-word;">
                                                <div
                                                    style="font-family:Arial, sans-serif;font-size:13px;font-weight:400;line-height:20px;text-align:left;color:<?php echo $gfMailValueText ?>;">
                                                    <?php echo wp_kses_post($formValue); ?>
                                                </div>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <!--[if mso | IE]></td></tr></table><![endif]--></td>
            </tr>
            </tbody>
        </table>
    </div> <!--[if mso | IE]></td></tr></table>
<?php endif; ?>
<?php if ($gfHasSocialLinks): ?>
<table align="center" border="0" cellpadding="0" cellspacing="0" class="" role="presentation" style="width:600px;" width="600" bgcolor="<?php echo $gfMailFooterBg ?>" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
    <div
        style="background:<?php echo $gfMailFooterBg ?>;background-color:<?php echo $gfMailFooterBg ?>;margin:0px auto;max-width:600px;">
        <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation"
               style="background:<?php echo $gfMailFooterBg ?>;background-color:<?php echo $gfMailFooterBg ?>;width:100%;">
            <tbody>
            <tr>
                <td style="border-bottom:<?php echo $gfMailBorder ?>;direction:ltr;font-size:0px;padding:10px;text-align:center;">
                    <!--[if mso | IE]>
                    <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                        <tr>
                            <td class="" style="vertical-align:top;width:580px;"><![endif]-->
                    <div class="mj-column-per-100 mj-outlook-group-fix"
                         style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
                        <table border="0" cellpadding="0" cellspacing="0" role="presentation" width="100%">
                            <tbody>
                            <tr>
                                <td style="vertical-align:top;padding:0;">
                                    <table border="0" cellpadding="0" cellspacing="0" role="presentation" style=""
                                           width="100%">
                                        <tbody>
                                        <tr>
                                            <td align="center" style="font-size:0px;padding:0;word-break:break-word;">
                                                <!--[if mso | IE]>
                                                <table align="center" border="0" cellpadding="0" cellspacing="0"
                                                       role="presentation">
                                                    <tr>
                                                        <td><![endif]-->
                                                <table align="center" border="0" cellpadding="0" cellspacing="0"
                                                       role="presentation" style="float:none;display:inline-table;">
                                                    <tbody>
                                                    <tr>
                                                        <?php foreach ($gfSocialLinks as $gfSocialLink) : ?>
                                                            <td style="padding:5px;vertical-align:middle;">
                                                                <table border="0" cellpadding="0" cellspacing="0"
                                                                       role="presentation"
                                                                       style="border-radius:3px;width:35px;">
                                                                    <tbody>
                                                                    <tr>
                                                                        <td style="font-size:0;height:35px;vertical-align:middle;width:35px;">
                                                                            <a href="<?php echo $gfSocialLink->link; ?>"
                                                                               target="_blank">

                                                                                <img height="35"
                                                                                     src="<?php echo $gfSocialLink->icon; ?>"
                                                                                     style="border-radius:3px;display:block;"
                                                                                     width="35">
                                                                            </a>
                                                                        </td>
                                                                    </tr>
                                                                    </tbody>
                                                                </table>
                                                            </td>
                                                        <?php endforeach; ?>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                                <!--[if mso | IE]></td></tr></table><![endif]--></td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <!--[if mso | IE]></td></tr></table><![endif]--></td>
            </tr>
            </tbody>
        </table>
    </div> <!--[if mso | IE]></td></tr></table>
<?php endif; ?>
     <?php if ($gfCopyrightEnabled && $gfFooterCopyright): ?>
<table align="center" border="0" cellpadding="0" cellspacing="0" class="" role="presentation" style="width:600px;" width="600" bgcolor="<?php echo $gfMailFooterBarBg ?>" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
    <div
        style="background:<?php echo $gfMailFooterBarBg ?>;background-color:<?php echo $gfMailFooterBarBg ?>;margin:0px auto;max-width:600px;">
        <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation"
               style="background:<?php echo $gfMailFooterBarBg ?>;background-color:<?php echo $gfMailFooterBarBg ?>;width:100%;">
            <tbody>
            <tr>
                <td style="direction:ltr;font-size:0px;padding:10px 0 0;text-align:center;"><!--[if mso | IE]>
                    <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                        <tr>
                            <td class="" style="vertical-align:top;width:600px;"><![endif]-->
                    <div class="mj-column-per-100 mj-outlook-group-fix"
                         style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
                        <table border="0" cellpadding="0" cellspacing="0" role="presentation" width="100%">
                            <tbody>
                            <tr>
                                <td style="vertical-align:top;padding:0;">
                                    <table border="0" cellpadding="0" cellspacing="0" role="presentation" style=""
                                           width="100%">
                                        <tbody>
                                        <tr>
                                            <td align="center"
                                                style="font-size:0px;padding:0 0 10px;word-break:break-word;">
                                                <div
                                                    style="font-family:Arial, sans-serif;font-size:14px;font-weight:400;line-height:20px;text-align:center;color:<?php echo $gfMailMainText ?>;">
                                                    <?php echo str_replace('{{CURRENT_YEAR}}', date("Y"), $gfFooterCopyright); ?>
                                                </div>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <!--[if mso | IE]></td></tr></table><![endif]--></td>
            </tr>
            </tbody>
        </table>
    </div> <!--[if mso | IE]></td></tr></table>
   <?php endif; ?>
<table align="center" border="0" cellpadding="0" cellspacing="0" class="" role="presentation" style="width:600px;" width="600" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
    <div style="margin:0px auto;max-width:600px;">
        <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
            <tbody>
            <tr>
                <td style="direction:ltr;font-size:0px;padding:0;text-align:center;"><!--[if mso | IE]>
                    <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                        <tr>
                            <td class="" style="vertical-align:top;width:600px;"><![endif]-->
                    <div class="mj-column-per-100 mj-outlook-group-fix"
                         style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
                        <table border="0" cellpadding="0" cellspacing="0" role="presentation" width="100%">
                            <tbody>
                            <tr>
                                <td style="vertical-align:top;padding:0;">
                                    <table border="0" cellpadding="0" cellspacing="0" role="presentation" style=""
                                           width="100%">
                                        <tbody>
                                        <tr>
                                            <td align="center" style="font-size:0px;padding:0;word-break:break-word;"><p
                                                    style="border-top:solid 20px <?php echo $gfMailBodyBg ?>;font-size:1px;margin:0px auto;width:100%;"></p>
                                                <!--[if mso | IE]><table align="center" border="0" cellpadding="0" cellspacing="0" style="border-top:solid 20px <?php echo $gfMailBodyBg ?>;font-size:1px;margin:0px auto;width:600px;" role="presentation" width="600px" ><tr><td style="height:0;line-height:0;"> &nbsp;
</td></tr></table><![endif]--></td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <!--[if mso | IE]></td></tr></table><![endif]--></td>
            </tr>
            </tbody>
        </table>
    </div>
    <!--[if mso | IE]></td></tr></table><![endif]-->
</div>
</body>
</html>