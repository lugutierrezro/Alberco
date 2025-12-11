<?php
/**
 * Countdown Component
 * Componente reutilizable para mostrar eventos con cuenta regresiva
 */

// Obtener evento activo
$eventoActivo = null;
if (isset($configService)) {
    $eventoActivo = $configService->getEventoActivo();
}

if ($eventoActivo && !empty($siteConfig['mostrar_temporizador'])):
    $estilos = json_decode($eventoActivo['estilo_json'] ?? '{}', true);
    $backgroundColor = $estilos['backgroundColor'] ?? '#FF3D00';
    $textColor = $estilos['textColor'] ?? '#FFFFFF';
    $fontSize = $estilos['fontSize'] ?? '1.5rem';
?>

<!-- Evento con Countdown -->
<div class="event-countdown-container" 
     data-aos="fade-down" 
     data-aos-duration="1000"
     style="background: <?= $backgroundColor ?>; 
            color: <?= $textColor ?>; 
            padding: 1.5rem; 
            text-align: center; 
            position: relative; 
            z-index: 9997;">
    <div class="container-modern">
        <div class="row align-items-center">
            <div class="col-md-4">
                <h3 style="margin: 0; font-size: <?= $fontSize ?>; font-weight: 800;">
                    <i class="fas fa-calendar-alt me-2"></i>
                    <?= htmlspecialchars($eventoActivo['nombre_evento']) ?>
                </h3>
            </div>
            <div class="col-md-4">
                <p style="margin: 0; font-size: 0.9rem; opacity: 0.9;">
                    <?= htmlspecialchars($eventoActivo['mensaje_antes']) ?>
                </p>
            </div>
            <div class="col-md-4">
                <div id="countdown-<?= $eventoActivo['id_evento'] ?>" 
                     class="countdown-display" 
                     style="font-size: 1.75rem; font-weight: 900; letter-spacing: 2px;">
                    --d --h --m --s
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    const eventDate = new Date('<?= $eventoActivo['fecha_evento'] ?>').getTime();
    const countdownEl = document.getElementById('countdown-<?= $eventoActivo['id_evento'] ?>');
    
    function updateCountdown() {
        const now = new Date().getTime();
        const distance = eventDate - now;
        
        if (distance > 0) {
            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
            countdownEl.innerHTML = 
                `<span class="countdown-part">${days}<small>d</small></span> ` +
                `<span class="countdown-part">${hours}<small>h</small></span> ` +
                `<span class="countdown-part">${minutes}<small>m</small></span> ` +
                `<span class="countdown-part">${seconds}<small>s</small></span>`;
        } else {
            countdownEl.innerHTML = '<?= htmlspecialchars($eventoActivo['mensaje_durante']) ?>';
            countdownEl.style.animation = 'pulse 2s infinite';
        }
    }
    
    updateCountdown();
    setInterval(updateCountdown, 1000);
})();
</script>

<style>
.countdown-part {
    display: inline-block;
    margin: 0 0.5rem;
}

.countdown-part small {
    font-size: 0.6em;
    opacity: 0.8;
    margin-left: 2px;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}

@media (max-width: 768px) {
    .event-countdown-container .row > div {
        margin-bottom: 0.5rem;
    }
    
    .countdown-display {
        font-size: 1.25rem !important;
    }
}
</style>

<?php endif; ?>
