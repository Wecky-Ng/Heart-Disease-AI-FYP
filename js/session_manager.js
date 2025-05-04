/**
 * Session Manager - Handles automatic session refresh and timeout warnings
 */

const SessionManager = {
    // Configuration
    refreshInterval: 5 * 60 * 1000, // Refresh session every 5 minutes (in milliseconds)
    warningThreshold: 10 * 60, // Show warning when 10 minutes remaining (in seconds)
    warningDisplayTime: 30 * 1000, // How long to show the warning (in milliseconds)
    sessionEndpoint: 'session.php?refresh_session=true',
    
    // Internal properties
    refreshTimer: null,
    warningTimer: null,
    warningDisplayed: false,
    warningDialog: null,
    
    /**
     * Initialize the session manager
     */
    init: function() {
        // Only initialize for logged-in users
        if (!this.isUserLoggedIn()) {
            return;
        }
        
        // Create warning dialog
        this.createWarningDialog();
        
        // Start refresh timer
        this.startRefreshTimer();
        
        // Add event listeners for user activity
        this.setupActivityListeners();
        
        console.log('Session Manager initialized');
    },
    
    /**
     * Check if user is logged in
     */
    isUserLoggedIn: function() {
        // This assumes there's a global variable or some DOM element that indicates login status
        // Modify this according to your application's way of determining login status
        return document.body.classList.contains('logged-in') || 
               document.querySelector('.user-profile') !== null ||
               (typeof isLoggedIn !== 'undefined' && isLoggedIn === true);
    },
    
    /**
     * Create the session timeout warning dialog
     */
    createWarningDialog: function() {
        // Create dialog if it doesn't exist
        if (!this.warningDialog) {
            const dialog = document.createElement('div');
            dialog.id = 'session-timeout-warning';
            dialog.className = 'session-dialog';
            dialog.style.display = 'none';
            dialog.innerHTML = `
                <div class="session-dialog-content">
                    <h3>Session Timeout Warning</h3>
                    <p>Your session is about to expire due to inactivity.</p>
                    <p>You will be logged out in <span id="session-countdown">00:00</span>.</p>
                    <div class="session-dialog-buttons">
                        <button id="session-extend-btn" class="btn btn-primary">Stay Logged In</button>
                        <button id="session-logout-btn" class="btn btn-secondary">Logout Now</button>
                    </div>
                </div>
            `;
            
            document.body.appendChild(dialog);
            this.warningDialog = dialog;
            
            // Add event listeners to buttons
            document.getElementById('session-extend-btn').addEventListener('click', () => {
                this.extendSession();
            });
            
            document.getElementById('session-logout-btn').addEventListener('click', () => {
                window.location.href = 'logout.php';
            });
            
            // Add styles
            if (!document.getElementById('session-manager-styles')) {
                const style = document.createElement('style');
                style.id = 'session-manager-styles';
                style.textContent = `
                    .session-dialog {
                        position: fixed;
                        top: 0;
                        left: 0;
                        right: 0;
                        bottom: 0;
                        background-color: rgba(0, 0, 0, 0.5);
                        z-index: 9999;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                    }
                    .session-dialog-content {
                        background-color: white;
                        padding: 20px;
                        border-radius: 5px;
                        max-width: 400px;
                        text-align: center;
                    }
                    .session-dialog-buttons {
                        margin-top: 20px;
                    }
                    .session-dialog-buttons button {
                        margin: 0 10px;
                    }
                `;
                document.head.appendChild(style);
            }
        }
    },
    
    /**
     * Start the session refresh timer
     */
    startRefreshTimer: function() {
        // Clear existing timer if any
        if (this.refreshTimer) {
            clearInterval(this.refreshTimer);
        }
        
        // Set up new timer
        this.refreshTimer = setInterval(() => {
            this.refreshSession();
        }, this.refreshInterval);
        
        // Do an immediate refresh to get current session status
        this.refreshSession();
    },
    
    /**
     * Refresh the session via AJAX call
     */
    refreshSession: function() {
        fetch(this.sessionEndpoint, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Session refreshed. Remaining time:', data.remaining, 'seconds');
                
                // Check if we need to show warning
                if (data.remaining <= this.warningThreshold && !this.warningDisplayed) {
                    this.showTimeoutWarning(data.remaining);
                }
            } else {
                console.log('Session refresh failed:', data.message);
                // Redirect to login page if session is invalid
                if (data.message === 'Not logged in') {
                    window.location.reload();
                }
            }
        })
        .catch(error => {
            console.error('Error refreshing session:', error);
        });
    },
    
    /**
     * Show the session timeout warning dialog
     */
    showTimeoutWarning: function(remainingSeconds) {
        if (this.warningDisplayed) return;
        
        this.warningDisplayed = true;
        this.warningDialog.style.display = 'flex';
        
        // Start countdown
        let countdown = remainingSeconds;
        const countdownElement = document.getElementById('session-countdown');
        
        const updateCountdown = () => {
            const minutes = Math.floor(countdown / 60);
            const seconds = countdown % 60;
            countdownElement.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            countdown--;
            
            if (countdown < 0) {
                clearInterval(this.warningTimer);
                window.location.href = 'logout.php';
            }
        };
        
        updateCountdown();
        this.warningTimer = setInterval(updateCountdown, 1000);
    },
    
    /**
     * Hide the warning dialog and extend the session
     */
    extendSession: function() {
        // Hide warning dialog
        this.warningDialog.style.display = 'none';
        this.warningDisplayed = false;
        
        // Clear warning timer
        if (this.warningTimer) {
            clearInterval(this.warningTimer);
            this.warningTimer = null;
        }
        
        // Refresh session immediately
        this.refreshSession();
    },
    
    /**
     * Set up event listeners for user activity
     */
    setupActivityListeners: function() {
        // List of events that indicate user activity
        const events = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart'];
        
        // Throttled activity handler
        let lastActivityTime = Date.now();
        const activityThreshold = 60000; // 1 minute
        
        const activityHandler = () => {
            const now = Date.now();
            if (now - lastActivityTime > activityThreshold) {
                lastActivityTime = now;
                this.refreshSession();
            }
        };
        
        // Add event listeners
        events.forEach(event => {
            document.addEventListener(event, activityHandler, { passive: true });
        });
    }
};

// Initialize session manager when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    SessionManager.init();
});