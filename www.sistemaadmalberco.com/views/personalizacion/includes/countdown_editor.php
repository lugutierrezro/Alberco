<!-- Editor Visual de Countdown -->
<div class="col-md-12 mt-4">
    <div class="eventos-editor-container">
        <h4 class="mb-3"><i class="fas fa-palette"></i> Personalización Visual del Countdown</h4>
        
        <!-- Tabs para controles -->
        <ul class="nav nav-tabs evento-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-toggle="tab" href="#countdown-colores">🎨 Colores</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#countdown-tipografia">✍️ Tipografía</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#countdown-estilo">🎯 Estilo</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#countdown-efectos">✨ Efectos</a>
            </li>
        </ul>
        
        <div class="tab-content border border-top-0 p-3 bg-white">
            <!-- Tab: Colores -->
            <div id="countdown-colores" class="tab-pane active">
                <div class="row">
                    <div class="col-md-3">
                        <label class="evento-control-label">Fondo Countdown</label>
                        <input type="color" class="countdown-color-picker countdown-control" id="countdownBg" value="#000000">
                    </div>
                    <div class="col-md-3">
                        <label class="evento-control-label">Color Números</label>
                        <input type="color" class="countdown-color-picker countdown-control" id="countdownNumberColor" value="#ffeb3b">
                    </div>
                    <div class="col-md-3">
                        <label class="evento-control-label">Color Etiquetas</label>
                        <input type="color" class="countdown-color-picker countdown-control" id="countdownLabelColor" value="#ffffff">
                    </div>
                    <div class="col-md-3">
                        <label class="evento-control-label">Fondo Cajas</label>
                        <input type="color" class="countdown-color-picker countdown-control" id="countdownBoxBg" value="#333333">
                    </div>
                </div>
            </div>
            
            <!-- Tab: Tipografía -->
            <div id="countdown-tipografia" class="tab-pane fade">
                <div class="row">
                    <div class="col-md-6">
                        <label class="evento-control-label">Tamaño Números</label>
                        <input type="range" class="form-control-range countdown-control" id="numberSize" min="20" max="80" value="40">
                        <small class="text-muted" id="numberSizeValue">40px</small>
                    </div>
                    <div class="col-md-6">
                        <label class="evento-control-label">Tamaño Etiquetas</label>
                        <input type="range" class="form-control-range countdown-control" id="labelSize" min="8" max="24" value="14">
                        <small class="text-muted" id="labelSizeValue">14px</small>
                    </div>
                    <div class="col-md-6">
                        <label class="evento-control-label">Grosor Números</label>
                        <select class="form-control countdown-control" id="numberWeight">
                            <option value="normal">Normal</option>
                            <option value="bold" selected>Negrita</option>
                            <option value="800">Extra Negrita</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="evento-control-label">Fuente</label>
                        <select class="form-control countdown-control" id="fontFamily">
                            <option value="Arial, sans-serif" selected>Arial</option>
                            <option value="'Courier New', monospace">Courier</option>
                            <option value="Georgia, serif">Georgia</option>
                            <option value="'Times New Roman', serif">Times</option>
                            <option value="Verdana, sans-serif">Verdana</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Tab: Estilo -->
            <div id="countdown-estilo" class="tab-pane fade">
                <div class="row">
                    <div class="col-md-4">
                        <label class="evento-control-label">Redondeo Cajas (px)</label>
                        <input type="number" class="form-control countdown-control" id="boxBorderRadius" min="0" max="50" value="8">
                    </div>
                    <div class="col-md-4">
                        <label class="evento-control-label">Padding Cajas (px)</label>
                        <input type="number" class="form-control countdown-control" id="boxPadding" min="5" max="50" value="15">
                    </div>
                    <div class="col-md-4">
                        <label class="evento-control-label">Separación (px)</label>
                        <input type="number" class="form-control countdown-control" id="boxGap" min="0" max="50" value="20">
                    </div>
                </div>
            </div>
            
            <!-- Tab: Efectos -->
            <div id="countdown-efectos" class="tab-pane fade">
                <div class="row">
                    <div class="col-md-4">
                        <label class="evento-control-label">Sombra X (px)</label>
                        <input type="number" class="form-control countdown-control" id="shadowX" min="-20" max="20" value="0">
                    </div>
                    <div class="col-md-4">
                        <label class="evento-control-label">Sombra Y (px)</label>
                        <input type="number" class="form-control countdown-control" id="shadowY" min="-20" max="20" value="4">
                    </div>
                    <div class="col-md-4">
                        <label class="evento-control-label">Difuminado (px)</label>
                        <input type="number" class="form-control countdown-control" id="shadowBlur" min="0" max="30" value="8">
                    </div>
                    <div class="col-md-6">
                        <label class="evento-control-label">Color Sombra</label>
                        <input type="color" class="countdown-color-picker countdown-control" id="shadowColor" value="#000000">
                    </div>
                    <div class="col-md-6">
                        <label class="evento-control-label">Animación</label>
                        <select class="form-control countdown-control" id="countdownAnimation">
                            <option value="none" selected>Sin animación</option>
                            <option value="pulse">Pulso</option>
                            <option value="flip">Flip</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Vista Previa del Countdown -->
<div class="col-md-12 mt-4">
    <div class="countdown-preview-container">
        <div class="countdown-preview-header">
            <h5 class="mb-0"><i class="fas fa-eye"></i> Vista Previa del Countdown</h5>
        </div>
        
        <div id="countdownPreview" class="countdown-display">
            <div class="countdown-box">
                <span class="countdown-number">10</span>
                <span class="countdown-label">Días</span>
            </div>
            <div class="countdown-box">
                <span class="countdown-number">05</span>
                <span class="countdown-label">Horas</span>
            </div>
            <div class="countdown-box">
                <span class="countdown-number">30</span>
                <span class="countdown-label">Minutos</span>
            </div>
            <div class="countdown-box">
                <span class="countdown-number">45</span>
                <span class="countdown-label">Segundos</span>
            </div>
        </div>
        
        <div class="evento-mensaje" id="eventoMensaje">
            <strong>¡Gran Evento Próximamente!</strong>
        </div>
        
        <div class="alert alert-info mt-3">
            <i class="fas fa-info-circle"></i>
            <strong>Tip:</strong> Los cambios se aplican en tiempo real. Ajusta los controles para ver el resultado.
        </div>
    </div>
</div>
