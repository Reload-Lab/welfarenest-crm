{{--
  Invito di attivazione account WN+.

  Stessa impalcatura dell'email dei consent request (tabelle, fallback Outlook,
  footer istituzionale) ma con l'identità Welfare Nest Plus: fondo crema e blu
  notte come il sito plus.welfarenest.it, e il verde acqua del logo (#17ABB4) al
  posto dell'oro come colore d'accento.

  Il logo va servito come PNG: i client email non renderizzano SVG e bloccano le
  data: URI.
--}}
<!DOCTYPE html>
<html lang="it" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="x-apple-disable-message-reformatting">
<meta name="format-detection" content="telephone=no, date=no, address=no, email=no">
<meta name="color-scheme" content="light">
<meta name="supported-color-schemes" content="light">
<title>Attiva il tuo account Welfare Nest Plus</title>
<!--[if mso]>
<noscript><xml><o:OfficeDocumentSettings><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml></noscript>
<style>p,td,a,span,strong{font-family:Arial,sans-serif !important;} h1,.serif{font-family:Georgia,serif !important;}</style>
<![endif]-->
<!--[if !mso]><!-->
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;1,600&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<!--<![endif]-->
<style>
  body{margin:0;padding:0;width:100% !important;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;background-color:#F7F3E9;}
  table{border-collapse:collapse;mso-table-lspace:0pt;mso-table-rspace:0pt;}
  img{border:0;outline:none;text-decoration:none;-ms-interpolation-mode:bicubic;display:block;}
  a{color:#0E354D;}
  a.btn:hover{background-color:#0B2738 !important;}
  @media screen and (max-width:620px){
    .container{width:100% !important;}
    .px{padding-left:24px !important;padding-right:24px !important;}
    .h1{font-size:26px !important;line-height:34px !important;}
    .btn{display:block !important;text-align:center !important;}
    .stack{display:block !important;width:100% !important;padding-right:0 !important;padding-bottom:14px !important;}
  }
</style>
</head>
<body style="margin:0;padding:0;background-color:#F7F3E9;">

<div style="display:none;max-height:0;overflow:hidden;mso-hide:all;font-size:1px;line-height:1px;color:#F7F3E9;opacity:0;">
  Imposta la password e scegli i tuoi consensi per entrare nella community WN+.&#8199;&#847;&#8199;&#847;&#8199;&#847;&#8199;&#847;&#8199;&#847;&#8199;&#847;&#8199;&#847;&#8199;&#847;
</div>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#F7F3E9" style="background-color:#F7F3E9;">
  <tr>
    <td align="center" style="padding:40px 16px;">
      <!--[if mso]><table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0"><tr><td><![endif]-->
      <table role="presentation" class="container" width="600" cellpadding="0" cellspacing="0" border="0" style="width:600px;max-width:600px;">

        <tr>
          <td class="px" style="padding:0 48px 28px 48px;">
            <a href="https://plus.welfarenest.it" style="text-decoration:none;">
              <img src="{{ asset('images/logo-wn-plus-email.png') }}" width="224" height="41" alt="Welfare Nest Plus" style="width:224px;max-width:224px;height:41px;font-family:'Playfair Display',Georgia,'Times New Roman',serif;font-size:22px;line-height:28px;font-weight:600;color:#0E354D;">
            </a>
          </td>
        </tr>

        <!-- Corpo -->
        <tr>
          <td bgcolor="#FFFFFF" style="background-color:#FFFFFF;border-top:4px solid #17ABB4;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td class="px" style="padding:44px 48px 4px 48px;">
                  <h1 class="h1 serif" style="margin:0 0 22px 0;font-family:'Playfair Display',Georgia,'Times New Roman',serif;font-size:30px;line-height:38px;font-weight:600;color:#0E354D;">Benvenuto nella <em style="color:#17ABB4;">Community</em></h1>
                  <p style="margin:0 0 16px 0;font-family:'Poppins','Helvetica Neue',Arial,sans-serif;font-size:15px;line-height:25px;color:#3F5A6B;">Ciao {{ $invitation->account->full_name }},</p>
                  <p style="margin:0;font-family:'Poppins','Helvetica Neue',Arial,sans-serif;font-size:15px;line-height:25px;color:#3F5A6B;">è stato creato per te un account per accedere a <strong style="font-weight:600;color:#0E354D;">Welfare Nest Plus</strong>, lo spazio della community di sanità integrativa.</p>
                </td>
              </tr>

              <!-- Dati dell'account -->
              <tr>
                <td class="px" style="padding:26px 48px 0 48px;">
                  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#F7F3E9" style="background-color:#F7F3E9;">
                    <tr>
                      <td style="padding:18px 22px;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                          <tr>
                            <td class="stack" width="50%" valign="top" style="padding-right:12px;">
                              <p style="margin:0 0 4px 0;font-family:'Poppins','Helvetica Neue',Arial,sans-serif;font-size:11px;line-height:16px;letter-spacing:0.08em;text-transform:uppercase;color:#7A8C97;">Email</p>
                              <p style="margin:0;font-family:'Poppins','Helvetica Neue',Arial,sans-serif;font-size:14px;line-height:22px;font-weight:500;color:#0E354D;word-break:break-all;">{{ $invitation->account->email }}</p>
                            </td>
                            @if($invitation->account->organization)
                              <td class="stack" width="50%" valign="top">
                                <p style="margin:0 0 4px 0;font-family:'Poppins','Helvetica Neue',Arial,sans-serif;font-size:11px;line-height:16px;letter-spacing:0.08em;text-transform:uppercase;color:#7A8C97;">Organizzazione</p>
                                <p style="margin:0;font-family:'Poppins','Helvetica Neue',Arial,sans-serif;font-size:14px;line-height:22px;font-weight:500;color:#0E354D;">{{ $invitation->account->organization->name ?? $invitation->account->organization->legal_name }}</p>
                              </td>
                            @endif
                          </tr>
                        </table>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>

              <tr>
                <td class="px" style="padding:26px 48px 0 48px;">
                  <p style="margin:0;font-family:'Poppins','Helvetica Neue',Arial,sans-serif;font-size:15px;line-height:25px;color:#3F5A6B;">Per attivarlo ti basta scegliere una password e indicare le tue preferenze sui consensi: decidi tu cosa rendere visibile agli altri membri della community, e potrai cambiare idea in qualsiasi momento dall’area riservata.</p>
                </td>
              </tr>

              <!-- CTA -->
              <tr>
                <td class="px" style="padding:28px 48px 0 48px;">
                  <!--[if mso]>
                  <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{ $activationUrl }}" style="height:52px;v-text-anchor:middle;width:220px;" arcsize="8%" stroke="f" fillcolor="#0E354D">
                    <w:anchorlock/>
                    <center style="color:#FFFFFF;font-family:Arial,sans-serif;font-size:16px;font-weight:bold;">Attiva account</center>
                  </v:roundrect>
                  <![endif]-->
                  <!--[if !mso]><!-->
                  <a href="{{ $activationUrl }}" class="btn" style="display:inline-block;background-color:#0E354D;color:#FFFFFF;font-family:'Poppins','Helvetica Neue',Arial,sans-serif;font-size:16px;line-height:20px;font-weight:600;text-decoration:none;padding:16px 34px;border-radius:4px;">Attiva account</a>
                  <!--<![endif]-->
                </td>
              </tr>

              <!-- Validità del link -->
              <tr>
                <td class="px" style="padding:30px 48px 0 48px;">
                  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#F7F3E9" style="background-color:#F7F3E9;border-left:3px solid #17ABB4;">
                    <tr>
                      <td style="padding:18px 22px;">
                        <p style="margin:0 0 10px 0;font-family:'Poppins','Helvetica Neue',Arial,sans-serif;font-size:14px;line-height:22px;color:#0E354D;">Il link è personale e resta valido fino al <strong style="font-weight:600;">{{ $invitation->expires_at->format('d/m/Y H:i') }}</strong>.</p>
                        <p style="margin:0;font-family:'Poppins','Helvetica Neue',Arial,sans-serif;font-size:13px;line-height:21px;color:#3F5A6B;">Se il pulsante non funziona, copia e incolla questo indirizzo nel browser:<br>
                          <a href="{{ $activationUrl }}" style="color:#0E354D;text-decoration:underline;word-break:break-all;">{{ $activationUrl }}</a></p>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>

              <!-- Chiusura -->
              <tr>
                <td class="px" style="padding:30px 48px 44px 48px;">
                  <p style="margin:0 0 22px 0;font-family:'Poppins','Helvetica Neue',Arial,sans-serif;font-size:14px;line-height:22px;color:#3F5A6B;">Non stavi aspettando questo invito? Puoi ignorare il messaggio: senza la tua attivazione l’account non viene aperto.</p>
                  <p style="margin:0;font-family:'Poppins','Helvetica Neue',Arial,sans-serif;font-size:15px;line-height:24px;color:#3F5A6B;">Un cordiale saluto,<br>
                    <span style="font-weight:500;color:#0E354D;">Il team Welfare Nest</span></p>
                </td>
              </tr>
            </table>
          </td>
        </tr>

        <!-- Footer istituzionale -->
        <tr>
          <td class="px" bgcolor="#0E354D" style="background-color:#0E354D;padding:28px 48px 30px 48px;">
            <p class="serif" style="margin:0 0 8px 0;font-family:'Playfair Display',Georgia,'Times New Roman',serif;font-size:16px;line-height:22px;font-weight:600;color:#F7F3E9;">Welfare Nest S.r.l. Società benefit</p>
            <p style="margin:0;font-family:'Poppins','Helvetica Neue',Arial,sans-serif;font-size:12px;line-height:20px;color:#B9C6CE;">Via Nomentana 150, 00162 Roma<br>
              P.IVA 16633731001<br>
              <a href="https://plus.welfarenest.it" style="color:#17ABB4;text-decoration:none;">plus.welfarenest.it</a></p>
          </td>
        </tr>
        <tr>
          <td align="center" style="padding:20px 16px 0 16px;">
            <p style="margin:0;font-family:'Poppins','Helvetica Neue',Arial,sans-serif;font-size:11px;line-height:18px;color:#7A8C97;">© {{ date('Y') }} Welfare Nest S.r.l. Società benefit. Tutti i diritti riservati.</p>
          </td>
        </tr>

      </table>
      <!--[if mso]></td></tr></table><![endif]-->
    </td>
  </tr>
</table>
</body>
</html>
