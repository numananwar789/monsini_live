<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login Alert — Monsini</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background-color: #f0f4f8;
            font-family: 'Segoe UI', Arial, sans-serif;
            color: #334155;
            padding: 32px 16px;
        }

        .wrapper {
            max-width: 600px;
            margin: 0 auto;
        }

        /* ── Header ── */
        .header {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            border-radius: 16px 16px 0 0;
            padding: 36px 40px;
            text-align: center;
        }

        .header .brand {
            font-size: 28px;
            font-weight: 800;
            color: #f8fafc;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .header .brand span {
            color: #38bdf8;
        }

        .header .tagline {
            margin-top: 6px;
            font-size: 13px;
            color: #94a3b8;
            letter-spacing: 0.5px;
        }

        /* ── Alert banner ── */
        .alert-banner {
            background: linear-gradient(90deg, #0ea5e9 0%, #6366f1 100%);
            padding: 14px 40px;
            text-align: center;
        }

        .alert-banner p {
            font-size: 15px;
            font-weight: 600;
            color: #ffffff;
            letter-spacing: 0.3px;
        }

        .alert-banner span {
            font-size: 18px;
            margin-right: 6px;
        }

        /* ── Body card ── */
        .body-card {
            background: #ffffff;
            padding: 36px 40px;
        }

        .greeting {
            font-size: 17px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 8px;
        }

        .intro-text {
            font-size: 14px;
            color: #64748b;
            line-height: 1.7;
            margin-bottom: 28px;
        }

        /* ── Info grid ── */
        .info-grid {
            display: table;
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 10px;
        }

        .info-row {
            display: table-row;
        }

        .info-label,
        .info-value {
            display: table-cell;
            padding: 12px 16px;
            font-size: 14px;
            vertical-align: middle;
        }

        .info-label {
            background: #f1f5f9;
            color: #475569;
            font-weight: 600;
            width: 38%;
            border-radius: 8px 0 0 8px;
            white-space: nowrap;
        }

        .info-value {
            background: #f8fafc;
            color: #1e293b;
            font-weight: 500;
            border-radius: 0 8px 8px 0;
        }

        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-superadmin { background: #fef3c7; color: #92400e; }
        .badge-admin      { background: #dbeafe; color: #1e40af; }
        .badge-editor     { background: #dcfce7; color: #166534; }
        .badge-customer   { background: #f3e8ff; color: #6b21a8; }
        .badge-default    { background: #e2e8f0; color: #475569; }

        /* ── IP chip ── */
        .ip-chip {
            display: inline-block;
            background: #0f172a;
            color: #38bdf8;
            padding: 4px 12px;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 1px;
        }

        /* ── Location chip ── */
        .location-chip {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: #eff6ff;
            color: #1d4ed8;
            padding: 4px 12px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
        }

        /* ── Divider ── */
        .divider {
            border: none;
            border-top: 1px solid #e2e8f0;
            margin: 28px 0;
        }

        .note {
            font-size: 13px;
            color: #94a3b8;
            line-height: 1.6;
            text-align: center;
        }

        /* ── Footer ── */
        .footer {
            background: #1e293b;
            border-radius: 0 0 16px 16px;
            padding: 24px 40px;
            text-align: center;
        }

        .footer p {
            font-size: 12px;
            color: #64748b;
            line-height: 1.8;
        }

        .footer strong {
            color: #94a3b8;
        }
    </style>
</head>
<body>
<div class="wrapper">

    <!-- Header -->
    <div class="header">
        <div class="brand">Mon<span>sini</span></div>
        <div class="tagline">Secure Access Monitoring</div>
    </div>

    <!-- Alert banner -->
    <div class="alert-banner">
        <p><span>🔐</span> New Login Detected on Your Platform</p>
    </div>

    <!-- Body -->
    <div class="body-card">
        <p class="greeting">Hello, Super Admin 👋</p>
        <p class="intro-text">
            A user has just successfully signed in to the Monsini platform.
            Below are the details of this login session for your records and security review.
        </p>

        <!-- Info table -->
        <div class="info-grid">

            <div class="info-row">
                <div class="info-label">👤 &nbsp;Name</div>
                <div class="info-value">{{ $userName }}</div>
            </div>

            <div class="info-row">
                <div class="info-label">📧 &nbsp;Email</div>
                <div class="info-value">{{ $userEmail }}</div>
            </div>

            <div class="info-row">
                <div class="info-label">🎭 &nbsp;Role</div>
                <div class="info-value">
                    @php
                        $roleClass = match(strtolower($userRole)) {
                            'superadmin' => 'badge-superadmin',
                            'admin'      => 'badge-admin',
                            'editor'     => 'badge-editor',
                            'customer'   => 'badge-customer',
                            default      => 'badge-default',
                        };
                    @endphp
                    <span class="badge {{ $roleClass }}">{{ $userRole }}</span>
                </div>
            </div>

            <div class="info-row">
                <div class="info-label">🌐 &nbsp;IP Address</div>
                <div class="info-value">
                    <span class="ip-chip">{{ $ipAddress }}</span>
                </div>
            </div>

            <div class="info-row">
                <div class="info-label">🏙️ &nbsp;City</div>
                <div class="info-value">
                    <span class="location-chip">📍 {{ $city }}</span>
                </div>
            </div>

            <div class="info-row">
                <div class="info-label">🌍 &nbsp;Country</div>
                <div class="info-value">
                    <span class="location-chip">🌐 {{ $country }}</span>
                </div>
            </div>

            <div class="info-row">
                <div class="info-label">🕐 &nbsp;Login Time</div>
                <div class="info-value">{{ $loginTime }}</div>
            </div>

        </div>

        <hr class="divider" />

        <p class="note">
            If this login looks suspicious, please take immediate action by reviewing the account
            or contacting your security team. This is an automated security alert — no action is required
            if the login was expected.
        </p>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>
            <strong>Monsini Platform</strong> &mdash; Automated Security Notification<br />
            This email was generated automatically. Please do not reply to this message.
        </p>
    </div>

</div>
</body>
</html>
