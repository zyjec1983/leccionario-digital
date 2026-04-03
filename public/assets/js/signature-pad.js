(function() {
    let signaturePad = null;
    let firmaDataUrl = null;

    window.initSignaturePad = function() {
        const canvas = document.getElementById('signatureCanvas');
        if (!canvas) return;

        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        canvas.width = canvas.offsetWidth * ratio;
        canvas.height = canvas.offsetHeight * ratio;
        
        const ctx = canvas.getContext('2d');
        ctx.scale(ratio, ratio);

        signaturePad = new SignaturePad(canvas, {
            penColor: '#000080',
            minWidth: 0.5,
            maxWidth: 2.5,
            throttle: 16,
            velocityFilterWeight: 0.7
        });
    };

    window.clearSignaturePad = function() {
        if (signaturePad) {
            signaturePad.clear();
            firmaDataUrl = null;
            document.getElementById('firma_hidden').value = '';
            sessionStorage.removeItem('firma_temp');
            updateFirmaPreview();
        }
    };

    window.updateFirmaPreview = function() {
        const preview = document.getElementById('firmaPreview');
        const previewInForm = document.getElementById('firmaPreviewForm');
        const dataToShow = firmaDataUrl || sessionStorage.getItem('firma_temp');
        
        if (preview && dataToShow) {
            preview.innerHTML = '<img src="' + dataToShow + '" style="max-height: 80px; border: 1px solid #ddd; padding: 5px; background: white;">';
        }
        if (previewInForm && dataToShow) {
            previewInForm.innerHTML = '<img src="' + dataToShow + '" style="max-height: 60px; border: 1px solid #ddd; padding: 5px;">';
        }
    };

    window.confirmFirma = function() {
        if (!signaturePad || signaturePad.isEmpty()) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Debe firmar en el canvas antes de confirmar'
            });
            return false;
        }
        
        firmaDataUrl = signaturePad.toDataURL('image/png');
        document.getElementById('firma_hidden').value = firmaDataUrl;
        sessionStorage.setItem('firma_temp', firmaDataUrl);
        updateFirmaPreview();
        
        const previewInForm = document.getElementById('firmaPreviewForm');
        if (previewInForm) {
            previewInForm.innerHTML = '<img src="' + firmaDataUrl + '" style="max-height: 60px; border: 1px solid #ddd; padding: 5px;">';
        }
        
        const modal = bootstrap.Modal.getInstance(document.getElementById('modalFirma'));
        if (modal) {
            modal.hide();
        }
        return true;
    };

    window.clearAllFirma = function() {
        firmaDataUrl = null;
        sessionStorage.removeItem('firma_temp');
        document.getElementById('firma_hidden').value = '';
        const previewInForm = document.getElementById('firmaPreviewForm');
        if (previewInForm) previewInForm.innerHTML = '';
        const preview = document.getElementById('firmaPreview');
        if (preview) preview.innerHTML = '';
        if (signaturePad) signaturePad.clear();
    };

    document.addEventListener('DOMContentLoaded', function() {
        const firmaModal = document.getElementById('modalFirma');
        if (firmaModal) {
            firmaModal.addEventListener('shown.bs.modal', function() {
                initSignaturePad();
            });
            
            firmaModal.addEventListener('hidden.bs.modal', function() {
                signaturePad = null;
            });
        }
    });
})();
