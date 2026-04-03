(function() {
    const SESSION_TIMEOUT = 15 * 60 * 1000;
    const WARNING_BEFORE = 60 * 1000;
    const HEARTBEAT_INTERVAL = 30 * 1000;
    
    let timeoutTimer = null;
    let warningTimer = null;
    let heartbeatTimer = null;
    let lastActivity = Date.now();
    
    const modalHTML = `
    <div class="modal fade" id="sessionTimeoutModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title text-dark">
                        <i class="fas fa-clock me-2"></i>Sesión por expirar
                    </h5>
                </div>
                <div class="modal-body text-center">
                    <p>Tu sesión expirará en <strong id="timeoutCountdown">60</strong> segundos.</p>
                    <p class="text-muted small">¿Deseas continuar trabajando?</p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-primary" id="extendSession">
                        <i class="fas fa-check me-1"></i>Continuar
                    </button>
                    <button type="button" class="btn btn-secondary" id="logoutNow">
                        <i class="fas fa-sign-out-alt me-1"></i>Cerrar sesión
                    </button>
                </div>
            </div>
        </div>
    </div>
    `;
    
    function initSessionTimeout() {
        if (typeof BASE_URL === 'undefined') {
            return;
        }
        
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        
        const modal = document.getElementById('sessionTimeoutModal');
        const modalInstance = new bootstrap.Modal(modal);
        
        document.getElementById('extendSession').addEventListener('click', function() {
            extendSession();
            modalInstance.hide();
        });
        
        document.getElementById('logoutNow').addEventListener('click', function() {
            logoutNow();
        });
        
        resetTimers();
        
        ['mousemove', 'keydown', 'scroll', 'click', 'touchstart'].forEach(function(event) {
            document.addEventListener(event, function() {
                lastActivity = Date.now();
            }, { passive: true });
        });
        
        startHeartbeat();
    }
    
    function resetTimers() {
        if (timeoutTimer) clearTimeout(timeoutTimer);
        if (warningTimer) clearTimeout(warningTimer);
        
        warningTimer = setTimeout(function() {
            showWarning();
        }, SESSION_TIMEOUT - WARNING_BEFORE);
        
        timeoutTimer = setTimeout(function() {
            logoutNow();
        }, SESSION_TIMEOUT);
    }
    
    function showWarning() {
        const modal = document.getElementById('sessionTimeoutModal');
        const countdown = document.getElementById('timeoutCountdown');
        let seconds = 60;
        
        countdown.textContent = seconds;
        
        const modalInstance = new bootstrap.Modal(modal);
        modalInstance.show();
        
        const countdownTimer = setInterval(function() {
            seconds--;
            countdown.textContent = seconds;
            if (seconds <= 0) {
                clearInterval(countdownTimer);
            }
        }, 1000);
        
        modal.addEventListener('hidden.bs.modal', function() {
            clearInterval(countdownTimer);
            resetTimers();
        }, { once: true });
    }
    
    function extendSession() {
        const xhr = new XMLHttpRequest();
        xhr.open('GET', BASE_URL + '/auth/extend-session', true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                resetTimers();
            }
        };
        xhr.send();
    }
    
    function logoutNow() {
        window.location.href = BASE_URL + '/auth/logout';
    }
    
    function startHeartbeat() {
        heartbeatTimer = setInterval(function() {
            if (Date.now() - lastActivity < SESSION_TIMEOUT) {
                resetTimers();
            }
        }, HEARTBEAT_INTERVAL);
    }
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSessionTimeout);
    } else {
        initSessionTimeout();
    }
})();
