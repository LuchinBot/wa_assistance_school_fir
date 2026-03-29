{{-- 
    CORREO: Notificación de Estado de Inscripción
    Extiende el layout principal de mails.
    Variables esperadas:
        - $inscription : objeto con datos de la inscripción
            - $inscription->status : estado (approved, rejected, pending, in_review, etc.)
            - $inscription->period->name_year : nombre del periodo
            - $inscription->user->person : datos del usuario
            - $inscription->reason : razón de rechazo (opcional)
            - $inscription->notes : notas adicionales (opcional)
--}}
@extends('mails.layouts.mail_principal')

@section('contenido')

    <!-- ============================================================
             TÍTULO PRINCIPAL
             ============================================================ -->
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td style="padding-bottom: 20px;">
                <h1
                    style="font-family: Arial, Helvetica, sans-serif; font-size: 24px; color: #202124; font-weight: 400; margin: 0; line-height: 1.3;">
                    Estado de inscripción
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
                <p
                    style="font-family: Arial, Helvetica, sans-serif; font-size: 14px; color: #3c4043; line-height: 1.65; margin: 0 0 16px 0;">
                    Hola {{ $inscription->journalist->person->firstname }},
                </p>

                @if ($inscription->status == 'Y' || $inscription->status == 'Y')
                    <p
                        style="font-family: Arial, Helvetica, sans-serif; font-size: 14px; color: #3c4043; line-height: 1.65; margin: 0 0 16px 0;">
                        ¡Tenemos buenas noticias! Tu inscripción al periodo <strong
                            style="color: #202124;">{{ $inscription->period->name_year }}</strong> ha sido aprobada.  Ya puedes acceder a todos los beneficios y servicios disponibles para miembros activos del CTI.
                    </p>
                @elseif($inscription->status == 'R' || $inscription->status == 'R')
                    <p
                        style="font-family: Arial, Helvetica, sans-serif; font-size: 14px; color: #3c4043; line-height: 1.65; margin: 0 0 16px 0;">
                        Lamentamos informarte que tu inscripción al periodo <strong
                            style="color: #202124;">{{ $inscription->period->name_year }}</strong> no ha sido aprobada.
                        @if (isset($inscription->reason) && $inscription->reason)
                            Si tienes dudas o deseas más información, puedes contactarnos a través del intranet.
                        @else
                            Para más información sobre este resultado, puedes contactarnos a través del intranet.
                        @endif
                    </p>
                @elseif($inscription->status == 'A' || $inscription->status == 'A')
                    <p
                        style="font-family: Arial, Helvetica, sans-serif; font-size: 14px; color: #3c4043; line-height: 1.65; margin: 0 0 16px 0;">
                        Tu inscripción al periodo <strong
                            style="color: #202124;">{{ $inscription->period->name_year }}</strong> se encuentra actualmente
                        en proceso de revisión.                         Te notificaremos por este medio cuando tengamos una actualización sobre el estado de tu solicitud.

                    </p>
                @else
                    <p
                        style="font-family: Arial, Helvetica, sans-serif; font-size: 14px; color: #3c4043; line-height: 1.65; margin: 0;">
                        Hemos actualizado el estado de tu inscripción al periodo <strong
                            style="color: #202124;">{{ $inscription->period->name_year }}</strong>.
                    </p>
                @endif
            </td>
        </tr>
    </table>

    <!-- ============================================================
             CAJA DE INFORMACIÓN
             ============================================================ -->
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%"
        style="background-color: {{ $inscription->status == 'approved' || $inscription->status == 'aprobado' ? '#e6f4ea' : ($inscription->status == 'rejected' || $inscription->status == 'rechazado' ? '#fce8e6' : '#f8f9fa') }}; border-radius: 8px; border: 1px solid {{ $inscription->status == 'approved' || $inscription->status == 'aprobado' ? '#c6e1c6' : ($inscription->status == 'rejected' || $inscription->status == 'rechazado' ? '#f4c7c3' : '#dadce0') }};">
        <tr>
            <td style="padding: 24px;">
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                    <!-- Periodo -->
                    <tr>
                        <td style="padding-bottom: 16px;">
                            <p
                                style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; color: #5f6368; margin: 0 0 4px 0; text-transform: uppercase; letter-spacing: 0.5px;">
                                Periodo
                            </p>
                            <p
                                style="font-family: Arial, Helvetica, sans-serif; font-size: 15px; color: #202124; margin: 0; font-weight: 600;">
                                {{ $inscription->period->name_year }}
                            </p>
                        </td>
                    </tr>

                    <!-- Estado -->
                    <tr>
                        <td
                            style="padding-bottom: {{ (isset($inscription->reason) && $inscription->reason) || (isset($inscription->notes) && $inscription->notes) ? '16px' : '0' }};">
                            <p
                                style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; color: #5f6368; margin: 0 0 4px 0; text-transform: uppercase; letter-spacing: 0.5px;">
                                Estado
                            </p>
                            <p
                                style="font-family: Arial, Helvetica, sans-serif; font-size: 15px; color: {{ $inscription->status == 'Y' || $inscription->status == 'Y' ? '#137333' : ($inscription->status == 'R' || $inscription->status == 'R' ? '#c5221f' : '#5f6368') }}; margin: 0; font-weight: 600;">
                                @if ($inscription->status == 'Y' || $inscription->status == 'Y')
                                    Aprobado
                                @elseif($inscription->status == 'R' || $inscription->status == 'R')
                                    No aprobado
                                @elseif($inscription->status == 'A' || $inscription->status == 'A')
                                    En revisión
                                @elseif($inscription->status == 'A' || $inscription->status == 'A')
                                    Pendiente
                                @else
                                    {{ ucfirst($inscription->status) }}
                                @endif
                            </p>
                        </td>
                    </tr>

                    <!-- Razón de rechazo (solo si existe y fue rechazado) -->
                    @if (($inscription->status == 'R' || $inscription->status == 'R') && isset($inscription->reason) && $inscription->reason)
                        <tr>
                            <td
                                style="padding-bottom: 0;">
                                <p
                                    style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; color: #5f6368; margin: 0 0 8px 0; text-transform: uppercase; letter-spacing: 0.5px;">
                                    Motivo
                                </p>
                                <p
                                    style="font-family: Arial, Helvetica, sans-serif; font-size: 14px; color: #3c4043; margin: 0; line-height: 1.6;">
                                    {{ $inscription->reason }}
                                </p>
                            </td>
                        </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    <!-- ============================================================
             BOTÓN DE ACCIÓN
             ============================================================ -->
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td style="padding-top: 32px; padding-bottom: 8px;">
                <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                        <td align="center" bgcolor="#1a73e8" style="border-radius: 4px;" valign="middle">
                            <!--[if mso]>
                                <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word"
                                    href="https://www.cpddperu.com/intranet/inscripciones"
                                    style="width:200px;height:40px;" arcsize="10%" fillcolor="#1a73e8" strokecolor="none">
                                    <w:textbox style="inset:0; text-align:center;">
                                        <div align="center" style="line-height:40px; font-family:Arial,Helvetica,sans-serif; font-size:14px; font-weight:500; color:#FFFFFF;">
                                            Ir al intranet
                                        </div>
                                    </w:textbox>
                                </v:roundrect>
                                <![endif]-->
                            <a href="{{ env('APP_URL') }}"
                                style="display: inline-block; background-color: #1a73e8; color: #FFFFFF; font-family: Arial, Helvetica, sans-serif; font-size: 14px; font-weight: 500; text-decoration: none; padding: 10px 24px; border-radius: 4px;"
                                target="_blank">
                                Ir al intranet
                            </a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- ============================================================
             LÍNEA DIVISORIA
             ============================================================ -->
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td style="padding-top: 32px; padding-bottom: 24px;">
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
                <p
                    style="font-family: Arial, Helvetica, sans-serif; font-size: 14px; color: #3c4043; line-height: 1.6; margin: 0 0 12px 0;">
                    Saludos,
                </p>
                <p
                    style="font-family: Arial, Helvetica, sans-serif; font-size: 14px; color: #202124; font-weight: 500; margin: 0;">
                    El equipo del CTI
                </p>
            </td>
        </tr>
    </table>

@endsection
