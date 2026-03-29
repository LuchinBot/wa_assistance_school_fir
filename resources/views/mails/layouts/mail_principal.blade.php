{{-- 
    LAYOUT PRINCIPAL DE MAILS (Google Style)
    Uso en otros correos: @extends('mails.layouts.mail_principal')
    Secciones disponibles:
        - @section('contenido') ... @endsection   ← cuerpo del correo
--}}
<!DOCTYPE html>
<html lang="es" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft.com:vml" xmlns:o="urn:schemas-microsoft.com:office:office">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <meta name="format-detection" content="telephone=no; date=no; user=no; email=no; credit=no;">
    <title>{{ $asunto ?? 'CPDDP' }}</title>

    <!--[if mso]>
    <style type="text/css">
        body { font-family: Arial, Helvetica, sans-serif !important; }
        .hide-mso { display: none !important; width: 0 !important; height: 0 !important; }
    </style>
    <![endif]-->

    <style type="text/css">
        /* ===== RESET ===== */
        body {
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
            font-family: Arial, Helvetica, sans-serif;
            -webkit-font-smoothing: antialiased;
        }
        table {
            border-collapse: collapse;
        }
        img {
            display: block;
            border: none;
            outline: none;
        }
        a {
            text-decoration: none;
        }

        /* ===== RESPONSIVE (max 600px) ===== */
        @media screen and (max-width: 600px) {
            .email-outer {
                width: 100% !important;
                max-width: 100% !important;
            }
            .pad-mobile {
                padding-left: 20px !important;
                padding-right: 20px !important;
            }
            .logo-svg {
                width: 280px !important;
                height: auto !important;
            }
            .title-main {
                font-size: 20px !important;
            }
            .text-body {
                font-size: 13px !important;
            }
            .btn-wrap {
                width: auto !important;
            }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #f5f5f5;" bgcolor="#f5f5f5">

<!--[if mso]>
<table role="presentation" border="0" cellpadding="0" cellspacing="0" align="center" width="600" style="font-family: Arial, Helvetica, sans-serif;">
<tr><td style="padding: 0;">
<![endif]-->

<!-- TABLA CONTENEDORA PRINCIPAL (max 600px) -->
<table role="presentation" border="0" cellpadding="0" cellspacing="0" align="center"
       width="600"
       style="max-width: 600px; width: 100%; background-color: #ffffff; font-family: Arial, Helvetica, sans-serif;"
       class="email-outer">
    <tr>
        <td style="padding: 0;">

            <!-- ===== INCLUYE HEADER ===== -->
            @include('mails.layouts._header')

            <!-- ============================================================
                 ZONA DE CONTENIDO DEL CORREO
                 ============================================================ -->
            <tr>
                <td style="background-color: #ffffff; padding: 40px 44px 36px;" class="pad-mobile">

                    @yield('contenido')

                </td>
            </tr>

            <!-- ===== INCLUYE FOOTER ===== -->
            @include('mails.layouts._footer')

        </td>
    </tr>
</table>

<!--[if mso]>
</td></tr></table>
<![endif]-->

</body>
</html>