<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Disposisi</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f4f9; font-family: Arial, sans-serif;">
  <table align="center" cellpadding="0" cellspacing="0" width="600" style="border: 1px solid #e0e0e0; background-color: #ffffff; margin-top: 30px;">
    
    <!-- Header -->
    <tr>
      <td style="padding: 20px; border-bottom: 1px solid #e0e0e0;">
        <table cellpadding="0" cellspacing="0" width="100%">
          <tr>
            <td width="60">
              <img src="{{ asset('img/logoweb.png') }}" alt="{{ app(\App\Services\OrganizationContext::class)->shortName() }} Logo" width="50" style="display: block;">
            </td>
            <td style="padding-left: 10px;">
              <h2 style="margin: 0; font-size: 20px; color: #007BFF;">{{ app(\App\Services\OrganizationContext::class)->shortName() }}</h2>
              <p style="margin: 0; font-size: 14px; color: #777777;">Informasi Disposisi</p>
            </td>
          </tr>
        </table>
      </td>
    </tr>

    <!-- Content -->
    <tr>
      <td style="padding: 30px; color: #333333; font-size: 14px;">
        <p style="margin-top: 0;">Dear <strong>{{ $user->name }}</strong>,</p>
        <p>Terdapat disposisi untuk anda dengan detail sebagai berikut:</p>
        <ul style="padding-left: 20px;">
          <li><strong>Perintah:</strong> {{ $order->instruction }}</li>
          <li><strong>Tanggal:</strong> {{ $order->order_date }}</li>
          <li><strong>Jam:</strong> {{ $order->order_time }}</li>
          @if($order->note)
            <li><strong>Keterangan:</strong> {{ $order->note }}</li>
          @endif
        </ul>
        <p>Mohon untuk dapat dipersiapkan. Informasi lebih lanjut silakan klik tautan di bawah ini:</p>
        <p>
          <a href="{{ url('/disposisi/'.$order->order_slug) }}"
             style="color: #007BFF; text-decoration: none; font-weight: bold;"
             title="Lihat Detail Disposisi">
            Lihat Detail Disposisi
          </a>
        </p>
        <p style="margin-bottom: 0;">Terima kasih atas perhatian anda.</p>
      </td>
    </tr>

    <!-- Footer -->
    <tr>
      <td style="text-align: center; font-size: 12px; color: #999999; padding: 20px;">
        &copy; {{ date('Y') }} {{ app(\App\Services\OrganizationContext::class)->shortName() }} – All rights reserved.
      </td>
    </tr>
  </table>
</body>
</html>
