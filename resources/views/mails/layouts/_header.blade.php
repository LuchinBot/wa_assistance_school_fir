{{-- 
    PARTIAL: _header.blade.php
    Uso: @include('mails.layouts._header')
    Contiene: banda superior y logo.
--}}

<!-- ============================================================
     BANDA SUPERIOR ROJA
     ============================================================ -->
<tr>
    <td style="height: 4px; background-color: #C8102E; font-size: 0; line-height: 0;">&nbsp;</td>
</tr>

<!-- ============================================================
     LOGO / NOMBRE (opcional - puedes agregar logo aquí)
     ============================================================ -->
<tr>
    <td align="center" style="background-color: #ffffff; padding: 32px 40px 8px;">
        <table role="presentation" border="0" cellpadding="0" cellspacing="0">
            <tr>
                <td>
                    <img src="{{ asset('img/logo.png') }}" alt="CTI" width="120" style="display: block;">
                    <span
                        style="font-family: Arial, Helvetica, sans-serif; font-size: 18px; color: #202124; font-weight: 600; letter-spacing: -0.3px;">
                        CTI
                    </span>
                </td>
            </tr>
        </table>
    </td>
</tr>
<tr>
    <td style="background-color: #ffffff; padding: 0 40px;">
        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
            <tr>
                <td style="height: 1px; background-color: #e8eaed; font-size: 0; line-height: 0;">&nbsp;</td>
            </tr>
        </table>
    </td>
</tr>
