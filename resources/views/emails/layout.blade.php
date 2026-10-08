<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('email-title', 'Email')</title>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background: linear-gradient(to bottom right, #f9fafb, #f3f4f6);">
    <table role="presentation" style="width: 100%; border-collapse: collapse; background: linear-gradient(to bottom right, #f9fafb, #f3f4f6); padding: 20px 0;">
        <tr>
            <td align="center">
                <table role="presentation" style="width: 600px; max-width: 100%; border-collapse: collapse; background-color: #ffffff; border-radius: 12px; border: 1px solid #e5e7eb; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); overflow: hidden;">
                    <!-- Header with Logo -->
                    <tr>
                        <td style="background: linear-gradient(to right, #1f2937, #374151); padding: 30px 30px 20px 30px; text-align: center;">
                            <img src="https://iconicpages.com/wp-content/themes/iconicpages-theme/assets/images/logo.png" alt="Iconic Pages Logo" style="max-width: 200px; height: auto; margin-bottom: 15px; display: block; margin-left: auto; margin-right: auto;">
                            @hasSection('email-header-title')
                                <h1 style="margin: 0; color: #ffffff; font-size: 22px; font-weight: 700;">@yield('email-header-title')</h1>
                            @endif
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px; background-color: #ffffff;">
                            @yield('email-content')
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f9fafb; padding: 20px 30px; text-align: center; border-top: 1px solid #e5e7eb;">
                            @hasSection('email-footer')
                                @yield('email-footer')
                            @else
                                <p style="margin: 0; color: #6b7280; font-size: 12px;">
                                    This is an automated email from Iconic Pages CRM system.
                                </p>
                                <p style="margin: 5px 0 0 0; color: #9ca3af; font-size: 12px;">
                                    {{ date('F d, Y') }}
                                </p>
                            @endif
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
