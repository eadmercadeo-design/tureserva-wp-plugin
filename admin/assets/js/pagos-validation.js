/**
 * ==========================================================
 * TuReserva — Validación + Formateo de campos de pago
 * ==========================================================
 * - Valida campos obligatorios (Pasarela, Cantidad, Moneda)
 * - Formatea automáticamente la cantidad mientras se escribe
 * - Muestra avisos nativos de WordPress (sin alert)
 * ==========================================================
 */

document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('form#post');
    if (!form) return;

    // Crear contenedor de avisos
    let noticeArea = document.createElement('div');
    noticeArea.id = 'tureserva-notices';
    noticeArea.style.marginBottom = '12px';
    form.parentElement.insertBefore(noticeArea, form);

    const mostrarAviso = (mensaje, tipo = 'error') => {
        const notice = document.createElement('div');
        notice.className = `notice notice-${tipo} is-dismissible`;
        notice.innerHTML = `<p><strong>${mensaje}</strong></p>`;
        noticeArea.appendChild(notice);

        // Eliminar aviso con click o timeout
        notice.querySelector('.notice-dismiss')?.addEventListener('click', () => notice.remove());
        setTimeout(() => {
            notice.style.transition = 'opacity 0.4s';
            notice.style.opacity = '0';
            setTimeout(() => notice.remove(), 400);
        }, 4000);
    };

    // =======================================================
    // 🧮 FORMATEO AUTOMÁTICO DE CAMPO "CANTIDAD"
    // =======================================================
    const campoCantidad = document.querySelector('[name="_tureserva_pago_monto"]');
    if (campoCantidad) {
        campoCantidad.addEventListener('input', (e) => {
            // Quitar todo lo que no sea número o punto
            let valor = e.target.value.replace(/[^0-9.,]/g, '').replace(',', '.');

            // Convertir a número y formatear
            if (valor !== '' && !isNaN(valor)) {
                const numero = parseFloat(valor);
                e.target.value = numero.toLocaleString('es-CO', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }
        });

        // Guardar el valor limpio antes del submit (para BD)
        form.addEventListener('submit', () => {
            if (campoCantidad.value) {
                campoCantidad.value = campoCantidad.value
                    .replace(/\./g, '')     // quita separadores de miles
                    .replace(',', '.')      // usa punto decimal
                    .trim();
            }
        });
    }

    // =======================================================
    // ✅ VALIDACIÓN GENERAL ANTES DE GUARDAR
    // =======================================================
    form.addEventListener('submit', function (e) {
        noticeArea.innerHTML = '';
        let valido = true;

        const campos = [
            { name: '_tureserva_pasarela', label: 'Pasarela' },
            { name: '_tureserva_pago_monto', label: 'Cantidad' },
            { name: '_tureserva_pago_moneda', label: 'Moneda' }
        ];

        campos.forEach(campo => {
            const el = document.querySelector(`[name="${campo.name}"]`);
            if (el && (!el.value || el.value.trim() === '' || el.value === '— Select —')) {
                valido = false;
                el.style.border = '2px solid #dc3232';
                mostrarAviso(`⚠️ El campo "${campo.label}" es obligatorio.`);
                el.scrollIntoView({ behavior: 'smooth', block: 'center' });
            } else if (el) {
                el.style.border = '';
            }
        });

        if (!valido) {
            e.preventDefault();
            mostrarAviso('❌ No se puede guardar el pago hasta completar todos los campos obligatorios.', 'error');
        } else {
            mostrarAviso('✅ Validación exitosa. Guardando pago...', 'success');
        }
    });
});
