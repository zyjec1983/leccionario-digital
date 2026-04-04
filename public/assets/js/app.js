/* ********** App JavaScript ********** */

const App = {
    currentTheme: 'litera',

    init: function() {
        this.loadTheme();
        this.bindEvents();
    },

    loadTheme: function() {
        const savedTheme = localStorage.getItem('theme') || 'litera';
        this.setTheme(savedTheme, false);
    },

    setTheme: function(theme, save = true) {
        const literaCss = document.getElementById('theme-litera');
        const slateCss = document.getElementById('theme-slate');
        const body = document.body;

        if (theme === 'slate') {
            literaCss.disabled = true;
            slateCss.disabled = false;
            body.classList.add('theme-slate');
        } else {
            literaCss.disabled = false;
            slateCss.disabled = true;
            body.classList.remove('theme-slate');
        }

        this.currentTheme = theme;

        if (save) {
            localStorage.setItem('theme', theme);
        }

        this.updateToggleIcon();
    },

    toggleTheme: function() {
        const newTheme = this.currentTheme === 'litera' ? 'slate' : 'litera';
        this.setTheme(newTheme);
    },

    updateToggleIcon: function() {
        const btn = document.getElementById('theme-toggle-btn');
        if (btn) {
            const icon = btn.querySelector('i, svg, span');
            if (icon) {
                if (this.currentTheme === 'slate') {
                    icon.className = 'fas fa-sun';
                    btn.title = 'Cambiar a tema claro';
                } else {
                    icon.className = 'fas fa-moon';
                    btn.title = 'Cambiar a tema oscuro';
                }
            }
        }
    },

    bindEvents: function() {
        const self = this;
        document.addEventListener('click', function(e) {
            const toggleBtn = e.target.closest('#theme-toggle-btn');
            if (toggleBtn) {
                e.preventDefault();
                self.toggleTheme();
            }
        });
    },

    showToast: function(type, message) {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });

        Toast.fire({
            icon: type,
            title: message
        });
    },

    confirmDelete: function(callback) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: 'Esta acción no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                callback();
            }
        });
    },

    ajax: function(url, options = {}) {
        const defaults = {
            method: 'POST',
            dataType: 'json',
            showLoading: true,
            loadingText: 'Cargando...'
        };

        const settings = { ...defaults, ...options };

        return new Promise((resolve, reject) => {
            if (settings.showLoading) {
                Swal.fire({
                    title: settings.loadingText,
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });
            }

            fetch(url, {
                method: settings.method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: settings.data ? JSON.stringify(settings.data) : null
            })
            .then(response => response.json())
            .then(data => {
                Swal.close();
                if (data.success) {
                    resolve(data);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Ocurrió un error'
                    });
                    reject(data);
                }
            })
            .catch(error => {
                Swal.close();
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error de conexión'
                });
                reject(error);
            });
        });
    },

    formatDate: function(date) {
        const d = new Date(date);
        const day = String(d.getDate()).padStart(2, '0');
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const year = d.getFullYear();
        return `${day}/${month}/${year}`;
    },

    formatTime: function(date) {
        const d = new Date(date);
        const hours = String(d.getHours()).padStart(2, '0');
        const minutes = String(d.getMinutes()).padStart(2, '0');
        return `${hours}:${minutes}`;
    },

    getWeekDates: function(date = new Date()) {
        const week = [];
        const d = new Date(date);
        const day = d.getDay();
        const diff = d.getDate() - day + (day === 0 ? -6 : 1);

        for (let i = 0; i < 5; i++) {
            const dayDate = new Date(d.setDate(diff + i));
            week.push({
                date: dayDate,
                dateStr: dayDate.toISOString().split('T')[0],
                dayName: dayDate.toLocaleDateString('es-ES', { weekday: 'short' }),
                dayNumber: dayDate.getDate(),
                monthName: dayDate.toLocaleDateString('es-ES', { month: 'short' })
            });
        }

        return week;
    }
};

document.addEventListener('DOMContentLoaded', function() {
    App.init();
});
