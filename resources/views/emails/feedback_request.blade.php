<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Feedback Request</title>
  <style>
    body {
      margin: 0;
      padding: 0;
      font-family: Arial, Helvetica, sans-serif;
      background-color: #f9f9f9;
      -webkit-text-size-adjust: 100%;
      -ms-text-size-adjust: 100%;
    }

    table {
      border-collapse: collapse;
    }

    img {
      border: 0;
      outline: none;
      display: block;
    }

    .email-container {
      max-width: 600px;
      margin: 0 auto;
      background-color: #f9f9f9;
    }

    .header {
      padding: 40px 20px 20px;
      text-align: center;
      background-color: #f9f9f9;
    }

    .logo {
      max-width: 200px;
      height: auto;
      margin: 0 auto;
    }

    .content {
      padding: 20px 40px;
      color: #333333;
      line-height: 1.6;
      font-size: 14px;
      background-color: #f9f9f9;
    }

    .content p {
      margin: 0 0 15px 0;
    }

    .button {
      display: inline-block;
      padding: 14px 30px;
      margin: 20px 0;
      background-color: #1a73e8;
      color: #ffffff !important;
      text-decoration: none;
      border-radius: 5px;
      font-weight: bold;
      font-size: 14px;
    }

    .button:hover {
      background-color: #1557b0;
    }

    .footer {
      padding: 30px 40px;
      text-align: center;
      background-color: #f9f9f9;
      color: #666666;
      font-size: 12px;
      line-height: 1.5;
    }

    .footer a {
      color: #1a73e8;
      text-decoration: none;
    }

    @media only screen and (max-width: 600px) {
      .content {
        padding: 20px 25px !important;
      }

      .footer {
        padding: 20px 25px !important;
      }
    }
  </style>
</head>

<body>
  @php
    $companyDetails = getCompanyDetails();
    if (!isset($company) || !$company) {
      $company = getActiveCompany();
    }
    $companyName = $company->name ?? ($companyDetails['name'] ?? 'IMARA LIMS');
    $logoPath = $companyDetails['logo_path'] ?? null;
    if ($company && !empty($company->logo)) {
      $resolvedLogo = getCompanyLogoPath($company->logo);
      if ($resolvedLogo) {
        $logoPath = $resolvedLogo;
      }
    }
  @endphp
  <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f9f9f9; padding: 20px 0;">
    <tr>
      <td align="center">
        <table class="email-container" width="600" cellpadding="0" cellspacing="0" border="0">

          <!-- Header with Logo -->
          <tr>
            <td class="header">
              @if($logoPath)
                <img src="{{ $message->embed($logoPath) }}" alt="{{ $companyName }}" class="logo" style="background-color: #f9f9f9;">
              @else
                <h1 style="margin: 0; color: #333333; font-size: 24px;">{{ $companyName }}</h1>
              @endif
            </td>
          </tr>
      </td>
    </tr>

    <!-- Main Content -->
    <tr>
      <td class="content">
        <p><strong>Dear {{ $recipientName ?? 'Valued Customer' }},</strong></p>

        <p>We value our partnership and we are committed to maintaining the highest standards of technical competence, 
          accuracy, and reliability.As part of our commitment to continuous improvement, we kindly request your 
          feedback on our services.</p>

        @if(!empty($body))
          <p>{!! $body !!}</p>
        @endif

        <p>Kindly click the link below to provide your feedback:</p>

        <div style="text-align: center; margin: 30px 0;">
          <a href="{{ $feedbackLink }}" class="button">Provide Feedback</a>
        </div>

        <p>Best Regards,<br>
          <strong>{{ $companyName }} Team</strong>
        </p>
      </td>
    </tr>

    <!-- Footer -->
    <tr>
      <td class="footer">
        <p style="margin: 0 0 10px 0;"><strong>{{ $companyName }}</strong></p>
        <p style="margin: 0 0 5px 0;">{{ $company->address ?? ($companyDetails['address'] ?? 'P.O. Box 27774 - 00506, Thika') }}</p>
        <p style="margin: 0 0 5px 0;">
          Email: <a href="mailto:{{ $company->email ?? ($companyDetails['email'] ?? 'info@example.com') }}">{{ $company->email
            ?? ($companyDetails['email'] ?? 'info@example.com') }}</a>
        </p>
        <p style="margin: 0 0 5px 0;">
          Phone: {{ $company->cell_phone ?? ($companyDetails['phone'] ?? '+254 202030280') }}
        </p>
        <p style="margin: 0;">
          Website: <a
            href="http://{{ $company->website ?? ($companyDetails['website'] ?? 'www.example.com') }}">{{ $company->website ?? ($companyDetails['website'] ?? 'www.example.com') }}</a>
        </p>
      </td>
    </tr>

  </table>
  </td>
  </tr>
  </table>
</body>

</html>
