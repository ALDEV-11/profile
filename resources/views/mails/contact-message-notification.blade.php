<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>New Contact Message</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background: #f4f6fc;
            font-family: Arial, Helvetica, sans-serif;
        }
        .email-wrap {
            max-width: 640px;
            margin: 0 auto;
            padding: 32px 16px;
        }
        .email-card {
            background: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid #e8ecf7;
        }
        .email-header {
            background: linear-gradient(135deg, #3c3489, #e24b4a);
            color: #ffffff;
            padding: 24px 32px;
        }
        .email-header h1 {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
        }
        .email-header p {
            margin: 6px 0 0;
            opacity: 0.85;
            font-size: 14px;
        }
        .email-body {
            padding: 32px;
        }
        .email-body p {
            color: #475569;
            font-size: 14px;
            line-height: 1.6;
            margin: 0 0 20px;
        }
        .info-block {
            margin-bottom: 24px;
        }
        .info-block .label {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #94a3b8;
            margin-bottom: 4px;
        }
        .info-block .value {
            font-size: 15px;
            font-weight: 600;
            color: #0f172a;
            word-break: break-word;
        }
        .message-box {
            background: #f8fafc;
            border-left: 4px solid #e24b4a;
            border-radius: 8px;
            padding: 16px 20px;
            margin-top: 24px;
        }
        .message-box .value {
            color: #334155;
            font-weight: 400;
            line-height: 1.7;
            white-space: pre-wrap;
        }
        .email-footer {
            padding: 16px 32px;
            background: #f8fafc;
            border-top: 1px solid #e8ecf7;
            color: #94a3b8;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="email-wrap">
        <div class="email-card">
            <div class="email-header">
                <h1>📩 New Contact Message</h1>
                <p>Someone just submitted the contact form on your website.</p>
            </div>

            <div class="email-body">
                <div class="info-block">
                    <div class="label">Name</div>
                    <div class="value">{{ $contactMessage->name }}</div>
                </div>

                <div class="info-block">
                    <div class="label">Email</div>
                    <div class="value"><a href="mailto:{{ $contactMessage->email }}">{{ $contactMessage->email }}</a></div>
                </div>

                <div class="info-block">
                    <div class="label">Subject</div>
                    <div class="value">{{ $contactMessage->subject }}</div>
                </div>

                <div class="info-block">
                    <div class="label">Received At</div>
                    <div class="value">{{ $contactMessage->created_at->format('d M Y, H:i') }}</div>
                </div>

                <div class="message-box">
                    <div class="label">Message</div>
                    <div class="value">{{ $contactMessage->message }}</div>
                </div>
            </div>

            <div class="email-footer">
                This email was sent automatically from {{ config('app.name') }}.
            </div>
        </div>
    </div>
</body>
</html>
