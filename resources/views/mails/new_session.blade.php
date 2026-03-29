@extends('mails.layouts.mail_principal')

@section('contenido')

<!-- ============================================================
     TÍTULO PRINCIPAL
     ============================================================ -->
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
        <td style="padding-bottom: 24px;">
            <h1 style="font-family: Arial, Helvetica, sans-serif; font-size: 24px; color: #202124; font-weight: 400; margin: 0; line-height: 1.3;">
                Se detectó un nuevo inicio de sesión
            </h1>
        </td>
    </tr>
</table>

<!-- ============================================================
     MENSAJE PRINCIPAL
     ============================================================ -->
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
        <td style="padding-bottom: 24px;">
            <p style="font-family: Arial, Helvetica, sans-serif; font-size: 14px; color: #3c4043; line-height: 1.6; margin: 0 0 16px 0;">
                Hola {{ $user->person->firstname }},
            </p>
            <p style="font-family: Arial, Helvetica, sans-serif; font-size: 14px; color: #3c4043; line-height: 1.6; margin: 0;">
                Alguien inició sesión en tu cuenta hace poco. Revisa esta actividad y asegúrate de que fuiste tú.
            </p>
        </td>
    </tr>
</table>

<!-- ============================================================
     CAJA DE INFORMACIÓN (estilo Google)
     ============================================================ -->
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f8f9fa; border-radius: 8px; border: 1px solid #dadce0;">
    <tr>
        <td style="padding: 24px;">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td style="padding-bottom: 16px;">
                        <p style="font-family: Arial, Helvetica, sans-serif; font-size: 13px; color: #5f6368; margin: 0 0 4px 0;">
                            {{ $fecha }} • {{ $hora }}
                        </p>
                        <p style="font-family: Arial, Helvetica, sans-serif; font-size: 14px; color: #202124; margin: 0; font-weight: 500;">
                            {{ $dispositivo }}
                        </p>
                    </td>
                </tr>
                @if(isset($ubicacion) && $ubicacion)
                <tr>
                    <td>
                        <p style="font-family: Arial, Helvetica, sans-serif; font-size: 13px; color: #5f6368; margin: 0;">
                            {{ $ubicacion }}
                        </p>
                    </td>
                </tr>
                @endif
            </table>
        </td>
    </tr>
</table>

<!-- ============================================================
     TEXTO SECUNDARIO
     ============================================================ -->
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
        <td style="padding-top: 24px; padding-bottom: 8px;">
            <p style="font-family: Arial, Helvetica, sans-serif; font-size: 14px; color: #3c4043; line-height: 1.6; margin: 0;">
                ¿No fuiste tú?
            </p>
        </td>
    </tr>
    <tr>
        <td style="padding-bottom: 24px;">
            <!-- Link simple -->
            <a href="{{ env('APP_URL') }}" 
               style="font-family: Arial, Helvetica, sans-serif; font-size: 14px; color: #ffffff; text-decoration: none; font-weight: 500; background-color: #c60e0e; padding: 5px 10px; border-radius: 5px;"
               target="_blank">
                Protege tu cuenta
            </a>
        </td>
    </tr>
</table>
@endsection