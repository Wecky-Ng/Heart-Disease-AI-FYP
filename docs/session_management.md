# Session Management Documentation

## Overview

This document explains the session management system implemented to prevent unexpected logouts in the Heart Disease AI application. The system includes both server-side PHP code and client-side JavaScript to maintain active sessions for users.

## Features

- Extended session timeout (1 hour by default)
- Automatic session refresh while users are active
- Session timeout warning dialog before expiration
- Activity detection to refresh sessions when users interact with the page

## Implementation Details

### Server-Side (PHP)

1. **Session Configuration**
   - Session timeout set to 1 hour (3600 seconds)
   - Session cookies expire when browser closes

2. **Session Management Functions**
   - `checkAndRefreshSession()`: Checks if session needs refreshing and updates last activity time
   - `getRemainingSessionTime()`: Returns remaining session time in seconds
   - AJAX endpoint for client-side session refresh requests

### Client-Side (JavaScript)

1. **Session Manager**
   - Automatically refreshes session every 5 minutes
   - Displays warning dialog when session is about to expire
   - Detects user activity to maintain session

## Integration Instructions

### How to Add Session Management to Pages

1. Make sure `session.php` is included at the top of your PHP files:

```php
<?php
require_once 'session.php';
?>
```

2. Include the session refresh script in your page headers (after session.php):

```php
<?php
require_once 'includes/session_refresh.php';
?>
```

3. Add the 'logged-in' class to the body tag for logged-in users:

```php
<body class="<?php echo isLoggedIn() ? 'logged-in' : ''; ?>">
```

### Customizing Session Behavior

#### Changing Session Timeout

To change the session timeout duration, modify the `SESSION_TIMEOUT` constant in `session.php`:

```php
// Define session timeout (in seconds)
define('SESSION_TIMEOUT', 7200); // 2 hours
```

#### Adjusting JavaScript Refresh Interval

To change how often the session is refreshed, modify the `refreshInterval` property in `js/session_manager.js`:

```javascript
refreshInterval: 10 * 60 * 1000, // Refresh session every 10 minutes
```

#### Changing Warning Threshold

To adjust when the warning appears before timeout, modify the `warningThreshold` property:

```javascript
warningThreshold: 5 * 60, // Show warning when 5 minutes remaining
```

## Troubleshooting

- If sessions still expire unexpectedly, check that `session_refresh.php` is properly included
- Verify that the JavaScript console doesn't show any errors related to the session manager
- Make sure the server's PHP session garbage collection settings match your configuration

## Security Considerations

- The session refresh mechanism only extends sessions for active users
- Sessions will still expire after the configured timeout if there's no activity
- All session cookies are set to expire when the browser closes for additional security