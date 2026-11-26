<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Reply - {{ $ticketId }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .wrapper {
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 30px 20px;
            color: white;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
        }
        .header p {
            margin: 5px 0 0 0;
            opacity: 0.9;
        }
        .content {
            padding: 30px 20px;
        }
        .greeting {
            margin-bottom: 20px;
        }
        .greeting h2 {
            margin: 0;
            color: #2c3e50;
            font-size: 18px;
        }
        .greeting p {
            margin: 10px 0 0 0;
            color: #555;
        }
        .message-box {
            background-color: #f8f9fa;
            padding: 20px;
            border-left: 4px solid #667eea;
            margin: 20px 0;
            border-radius: 4px;
            line-height: 1.8;
        }
        .message-box p {
            margin: 0;
            color: #333;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .ticket-info {
            background-color: #f0f4ff;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
            font-size: 14px;
        }
        .ticket-info strong {
            color: #667eea;
        }
        .action-button {
            text-align: center;
            margin: 30px 0;
            color: #ffffff;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        .button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgb(241, 244, 255);
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #777;
            font-size: 12px;
            border-top: 1px solid #e0e0e0;
        }
        .footer p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <!-- Header -->
        <div class="header">
            <h1>{{ $supportName }}</h1>
        </div>

        <!-- Main Content -->
        <div class="content">
            <!-- Greeting -->
         <div class="greeting">
    @if(!$isSuperAdmin)
        <h2>{{ $userName }}</h2>
    @endif
</div>

            <!-- Message Box -->
            <div class="message-box">
                {!! nl2br(e($messageText)) !!}
            </div>

          

            <!-- Ticket Info -->
            <div class="ticket-info">
                <p><strong>Ticket ID:</strong> #{{ $ticketId }}</p>
             @if($isSuperAdmin)
            <p><strong>Email Id: </strong>{{ $supportEmail }} </p>
            @else
                <p><strong>Email Id: </strong>{{ $userEmail }}</p>
            @endif

                 
            </div>

            <!-- Additional Info -->
            <p>If you have any further questions or concerns, please don't hesitate to reply to this email or visit your support dashboard.</p>

            <!-- Action Button -->
            <div class="action-button">
                <a href="{{ url('/dashboard/support/requests/' . $ticketId) }}"
   style="
       background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color:#ffffff !important;
      padding:10px 20px;
      text-decoration:none !important;
      display:inline-block;
      border-radius:4px;
      mso-line-height-rule:exactly;
   ">
    <span style="color:#ffffff !important;">
        View Full Ticket
    </span>
</a>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <!-- <p>This email was sent from {{ $supportName }} &lt;{{ $supportEmail }}&gt;</p> -->
            <p>© {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            <!-- <p><small>Please do not reply directly to this email. Use the support dashboard instead.</small></p> -->
        </div>
    </div>
</body>
</html>