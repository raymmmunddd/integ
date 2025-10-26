<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Verification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            background-color: #75BDE5;
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 40px 30px;
            text-align: center;
        }
        .content p {
            color: #666;
            line-height: 1.6;
            margin-bottom: 20px;
        }
        .otp-box {
            background-color: #f9f9f9;
            border: 2px dashed #75BDE5;
            border-radius: 8px;
            padding: 20px;
            margin: 30px 0;
        }
        .otp-code {
            font-size: 36px;
            font-weight: bold;
            color: #75BDE5;
            letter-spacing: 8px;
            margin: 10px 0;
        }
        .warning {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            text-align: left;
        }
        .warning p {
            margin: 0;
            color: #856404;
            font-size: 14px;
        }
        .footer {
            background-color: #f9f9f9;
            padding: 20px;
            text-align: center;
            color: #999;
            font-size: 12px;
            border-top: 1px solid #eee;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Mental Health Management System</h1>
        </div>
        
        <div class="content">
            <h2 style="color: #333; margin-bottom: 10px;">Email Verification</h2>
            <p>Hello {{ $userName }},</p>
            <p>Thank you for registering! Please use the following One-Time Password (OTP) to verify your email address and complete your registration.</p>
            
            <div class="otp-box">
                <p style="margin: 0; color: #666; font-size: 14px;">Your verification code is:</p>
                <div class="otp-code">{{ $otp }}</div>
            </div>
            
            <div class="warning">
                <p><strong>Important:</strong> This code will expire in 10 minutes. Please do not share this code with anyone.</p>
            </div>
            
            <p style="margin-top: 30px;">If you didn't request this code, please ignore this email.</p>
        </div>
        
        <div class="footer">
            <p>&copy; {{ date('Y') }} Mental Health Management System. All rights reserved.</p>
            <p>This is an automated email. Please do not reply to this message.</p>
        </div>
    </div>
</body>
</html>