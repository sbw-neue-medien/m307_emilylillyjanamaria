/**
 * ProjektAdmin - Form Validation
 * Client-side Validierung - BBK Anforderung
 */

class FormValidator {
    constructor(formId) {
        this.form = document.getElementById(formId);
        this.errors = {};
        if (this.form) this.init();
    }

    init() {
        // Echtzeit-Validierung
        this.form.querySelectorAll('input, select, textarea').forEach(field => {
            field.addEventListener('blur', () => this.validateField(field));
            field.addEventListener('change', () => this.validateField(field));
        });

        // Form Submit
        this.form.addEventListener('submit', (e) => this.handleSubmit(e));
    }

    validateField(field) {
        const name = field.name;
        const value = field.value.trim();
        const type = field.type;
        const formGroup = field.closest('.form-group');
        
        this.errors[name] = [];

        // Erforderlich
        if (field.hasAttribute('required') && !value) {
            this.errors[name].push(`${this.getFieldLabel(field)} ist erforderlich`);
        }

        // Typ-spezifisch
        if (value) {
            if (type === 'email' && !this.isValidEmail(value)) {
                this.errors[name].push('Ungültige E-Mail-Adresse');
            }
            
            if (type === 'tel' && !this.isValidPhone(value)) {
                this.errors[name].push('Ungültige Telefonnummer');
            }
            
            if (type === 'number') {
                if (field.hasAttribute('min') && parseFloat(value) < parseFloat(field.getAttribute('min'))) {
                    this.errors[name].push(`Minimum: ${field.getAttribute('min')}`);
                }
                if (field.hasAttribute('max') && parseFloat(value) > parseFloat(field.getAttribute('max'))) {
                    this.errors[name].push(`Maximum: ${field.getAttribute('max')}`);
                }
            }
            
            if (type === 'text' || field.tagName === 'TEXTAREA') {
                const minLength = field.hasAttribute('minlength') ? parseInt(field.getAttribute('minlength')) : 0;
                if (value.length < minLength) {
                    this.errors[name].push(`Mindestens ${minLength} Zeichen`);
                }
            }

            // Pattern Validierung
            if (field.hasAttribute('pattern')) {
                const pattern = new RegExp(field.getAttribute('pattern'));
                if (!pattern.test(value)) {
                    this.errors[name].push('Ungültiges Format');
                }
            }
        }

        this.updateFieldUI(field, formGroup);
        return this.errors[name].length === 0;
    }

    updateFieldUI(field, formGroup) {
        if (!formGroup) return;

        if (this.errors[field.name].length > 0) {
            formGroup.classList.add('is-invalid');
            formGroup.classList.remove('is-valid');
            const feedback = formGroup.querySelector('.invalid-feedback');
            if (feedback) {
                feedback.textContent = this.errors[field.name][0];
            }
        } else {
            formGroup.classList.remove('is-invalid');
            if (field.value.trim()) {
                formGroup.classList.add('is-valid');
            }
        }
    }

    handleSubmit(e) {
        e.preventDefault();
        
        let isValid = true;
        this.form.querySelectorAll('input, select, textarea').forEach(field => {
            if (!this.validateField(field)) {
                isValid = false;
            }
        });

        if (isValid) {
            this.form.submit();
        } else {
            this.showError('Bitte füllen Sie alle erforderlichen Felder korrekt aus');
        }
    }

    isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    isValidPhone(phone) {
        return /^[\d\s\-\+\(\)]{7,20}$/.test(phone);
    }

    getFieldLabel(field) {
        const label = field.closest('.form-group')?.querySelector('label');
        return label ? label.textContent.replace(' *', '').trim() : field.name;
    }

    showError(message) {
        const alert = document.createElement('div');
        alert.className = 'alert alert-danger';
        alert.innerHTML = `
            <span>❌</span>
            <span>${message}</span>
            <span class="alert-close" onclick="this.parentElement.remove()">×</span>
        `;
        this.form.insertBefore(alert, this.form.firstChild);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('form.needs-validation').forEach(form => {
        new FormValidator(form.id || 'form-' + Math.random().toString(36).substr(2, 9));
    });

    // Alert Schließen
    document.querySelectorAll('.alert-close').forEach(btn => {
        btn.addEventListener('click', function() {
            this.parentElement.remove();
        });
    });

    // Tabellensuche
    document.querySelectorAll('[data-table-search]').forEach(input => {
        input.addEventListener('keyup', function() {
            const tableId = this.getAttribute('data-table-search');
            const table = document.getElementById(tableId);
            const searchTerm = this.value.toLowerCase();
            
            table.querySelectorAll('tbody tr').forEach(row => {
                row.style.display = row.textContent.toLowerCase().includes(searchTerm) ? '' : 'none';
            });
        });
    });
});