        document.getElementById('registrationForm').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            const recaptchaResponse = grecaptcha.getResponse();

            if (!recaptchaResponse) {
                e.preventDefault();
                alert('Please complete the reCAPTCHA verification.');
                return false;
            }

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Creating Account...';
        });

        function openTermsModal() {
            const modal = new bootstrap.Modal(document.getElementById('termsModal'));
            modal.show();
            return false;
        }

        function openPrivacyModal() {
            const modal = new bootstrap.Modal(document.getElementById('privacyModal'));
            modal.show();
            return false;
        }

        const termsModal = document.getElementById('termsModal');
        const termsCheckbox = document.getElementById('termsCheckbox');
        const modalTermsCheckbox = document.getElementById('modalTermsCheckbox');
        const acceptTermsBtn = document.getElementById('acceptTermsBtn');
        const termsCloseBtn = termsModal.querySelector('[data-bs-dismiss="modal"]');
        const termsScrollIndicator = document.getElementById('termsScrollIndicator');

        const privacyModal = document.getElementById('privacyModal');
        const privacyCheckbox = document.getElementById('privacyCheckbox');
        const modalPrivacyCheckbox = document.getElementById('modalPrivacyCheckbox');
        const acceptPrivacyBtn = document.getElementById('acceptPrivacyBtn');
        const privacyCloseBtn = privacyModal.querySelector('[data-bs-dismiss="modal"]');
        const privacyScrollIndicator = document.getElementById('privacyScrollIndicator');

        function setupModalScrollCheck(modal, checkbox, acceptBtn, closeBtn, scrollIndicator) {
            const modalBody = modal.querySelector('.modal-body');
            
            modal.addEventListener('show.bs.modal', function() {
                scrollIndicator.style.display = 'block';
                checkbox.checked = false;
                checkbox.disabled = true;
                acceptBtn.disabled = true;
                closeBtn.disabled = true;
            });
            
            modalBody.addEventListener('scroll', function() {
                const isAtBottom = this.scrollHeight - this.scrollTop <= this.clientHeight + 10;
                
                if (isAtBottom) {
                    checkbox.disabled = false;
                    closeBtn.disabled = false;
                    scrollIndicator.style.display = 'none';
                } else {
                    checkbox.disabled = true;
                    closeBtn.disabled = true;
                    acceptBtn.disabled = true;
                    checkbox.checked = false;
                    scrollIndicator.style.display = 'block';
                }
            });

            checkbox.addEventListener('change', function() {
                acceptBtn.disabled = !this.checked;
            });
        }

        setupModalScrollCheck(
            termsModal, 
            modalTermsCheckbox, 
            acceptTermsBtn, 
            termsCloseBtn,
            termsScrollIndicator
        );
        
        setupModalScrollCheck(
            privacyModal, 
            modalPrivacyCheckbox, 
            acceptPrivacyBtn, 
            privacyCloseBtn,
            privacyScrollIndicator
        );

        acceptTermsBtn.addEventListener('click', function() {
            termsCheckbox.checked = true;
            termsCheckbox.readOnly = false;
            const modal = bootstrap.Modal.getInstance(termsModal);
            modal.hide();
        });

        acceptPrivacyBtn.addEventListener('click', function() {
            privacyCheckbox.checked = true;
            privacyCheckbox.readOnly = false;
            const modal = bootstrap.Modal.getInstance(privacyModal);
            modal.hide();
        });

        document.addEventListener('DOMContentLoaded', function() {
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-user-plus me-2"></i>Create Account';
        });