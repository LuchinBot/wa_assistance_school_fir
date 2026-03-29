{{-- 
    CORREO: Welcome / Bienvenida
    Extiende el layout principal de mails.
    Variables esperadas (opcionales):
        - $person       : objeto con datos del usuario (firstname)
        - $asunto       : asunto del correo
--}}
@extends('mails.layouts.mail_principal')

@section('contenido')

<!-- ============================================================
     TÍTULO PRINCIPAL
     ============================================================ -->
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
        <td style="padding-bottom: 20px;">
            <h1 style="font-family: Arial, Helvetica, sans-serif; font-size: 24px; color: #202124; font-weight: 400; margin: 0; line-height: 1.3;">
                Bienvenido al CTI
            </h1>
        </td>
    </tr>
</table>

<!-- ============================================================
     MENSAJE PRINCIPAL
     ============================================================ -->
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
        <td style="padding-bottom: 20px;">
            <p style="font-family: Arial, Helvetica, sans-serif; font-size: 14px; color: #3c4043; line-height: 1.65; margin: 0 0 16px 0;">
                Hola{{ isset($person) ? ' ' . $person->firstname : '' }},
            </p>
            <p style="font-family: Arial, Helvetica, sans-serif; font-size: 14px; color: #3c4043; line-height: 1.65; margin: 0 0 16px 0;">
                Nos complace darte la bienvenida al <strong style="color: #202124;">Círculo de Periodistas Deportivos del Perú</strong>. Desde nuestros inicios, hemos sido un espacio de encuentro y excelencia en el periodismo deportivo nacional.
            </p>
            <p style="font-family: Arial, Helvetica, sans-serif; font-size: 14px; color: #3c4043; line-height: 1.65; margin: 0;">
                Como miembro de nuestra comunidad, ahora tienes acceso a nuestro intranet donde podrás iniciar tu proceso de acreditación como periodista deportivo.
            </p>
        </td>
    </tr>
</table>

<!-- ============================================================
     LÍNEA DIVISORIA
     ============================================================ -->
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
        <td style="padding-top: 8px; padding-bottom: 24px;">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td style="height: 1px; background-color: #e8eaed;"></td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<!-- ============================================================
     FIRMA SIMPLE
     ============================================================ -->
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
        <td>
            <p style="font-family: Arial, Helvetica, sans-serif; font-size: 14px; color: #3c4043; line-height: 1.6; margin: 0 0 12px 0;">
                Saludos,
            </p>
            <p style="font-family: Arial, Helvetica, sans-serif; font-size: 14px; color: #202124; font-weight: 500; margin: 0;">
                El equipo del CTI
            </p>
        </td>
    </tr>
</table>

@endsection