<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dar de Baja - <?php echo $empleado['nombre'] . ' ' . $empleado['apellido']; ?></title>
    <link rel="stylesheet" href="css/estilo.css">
    <style>
        .liquidacion-container {
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .liquidacion-total {
            background: #e8f4fd;
            padding: 15px;
            border-radius: 8px;
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            color: #27ae60;
        }
        .resumen-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .tipo-renuncia {
            background: #fff3cd;
            color: #856404;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
        }
        .tipo-despido {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
        }
        .info-empleado {
            background: #2c3e50;
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        select, input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
        }
        .btn-danger {
            background: #e74c3c;
            color: white;
        }
        .btn-cancel {
            background: #95a5a6;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📄 Dar de Baja - Liquidación de Prestaciones</h1>
        
        <!-- Información del empleado -->
        <div class="info-empleado">
            <h2><?php echo $empleado['nombre'] . ' ' . $empleado['apellido']; ?></h2>
            <p><strong>Código:</strong> <?php echo $empleado['codigo']; ?> | 
               <strong>Cédula:</strong> <?php echo $empleado['cedula']; ?> | 
               <strong>Cargo:</strong> <?php echo $empleado['cargo']; ?></p>
            <p><strong>Fecha ingreso:</strong> <?php echo date('d/m/Y', strtotime($empleado['fecha_ingreso'])); ?> | 
               <strong>Salario base:</strong> Q<?php echo number_format($empleado['salario_base'], 2); ?></p>
            <p><strong>Antigüedad:</strong> <?php echo $empleado['antiguedad_anos']; ?> años, 
               <?php echo $empleado['antiguedad_meses']; ?> meses, 
               <?php echo $empleado['antiguedad_dias']; ?> días</p>
        </div>
        
        <form action="index.php?controller=empleado&action=baja&id=<?php echo $empleado['id']; ?>" method="POST">
            <div class="form-group">
                <label>Tipo de baja:</label>
                <select name="tipo_baja" id="tipo_baja" required onchange="actualizarLiquidacion()">
                    <option value="renuncia" <?php echo (isset($tipo_baja) && $tipo_baja == 'renuncia') ? 'selected' : 'selected'; ?>>Renuncia voluntaria</option>
                    <option value="despido" <?php echo (isset($tipo_baja) && $tipo_baja == 'despido') ? 'selected' : ''; ?>>Despido</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Fecha de retiro:</label>
                <input type="date" name="fecha_retiro" id="fecha_retiro" value="<?php echo date('Y-m-d'); ?>" required onchange="actualizarLiquidacion()">
            </div>
            
            <!-- CÁLCULO DE LIQUIDACIÓN -->
            <div class="liquidacion-container">
                <h3>💰 Cálculo de Liquidación</h3>
                
                <div id="liquidacion-dinamica">
                    <!-- Se llenará con JavaScript -->
                </div>
            </div>
            
            <div style="text-align: center; margin-top: 20px;">
                <button type="submit" class="btn btn-danger" onclick="return confirm('¿Confirmar la baja y liquidación?')">Confirmar Baja y Liquidación</button>
                <a href="index.php?controller=empleado&action=index" class="btn btn-cancel">Cancelar</a>
            </div>
        </form>
    </div>
    
    <script>
        // Datos del empleado desde PHP
        const empleado = <?php echo json_encode($empleado); ?>;
        const liquidacionInicial = <?php echo json_encode($liquidacion); ?>;
        
        function actualizarLiquidacion() {
            const tipoBaja = document.getElementById('tipo_baja').value;
            const fechaRetiro = document.getElementById('fecha_retiro').value;
            
            // Llamar al servidor para recalcular
            fetch(`index.php?controller=empleado&action=calcular_liquidacion&id=<?php echo $empleado['id']; ?>&fecha=${fechaRetiro}&tipo=${tipoBaja}`)
                .then(response => response.json())
                .then(data => {
                    mostrarLiquidacion(data);
                })
                .catch(error => {
                    console.error('Error:', error);
                    // Usar datos iniciales si falla
                    mostrarLiquidacion(liquidacionInicial);
                });
        }
        
        function mostrarLiquidacion(l) {
            const container = document.getElementById('liquidacion-dinamica');
            const tipoBaja = document.getElementById('tipo_baja').value;
            
            let html = `
                <div class="${tipoBaja === 'renuncia' ? 'tipo-renuncia' : 'tipo-despido'}">
                    <strong>${tipoBaja === 'renuncia' ? '📝 RENUNCIA VOLUNTARIA' : '⚖️ DESPIDO'}</strong>
                    <p>${tipoBaja === 'renuncia' ? 
                        'El empleado renuncia voluntariamente. Se pagan prestaciones proporcionales.' : 
                        'Despido injustificado. Se pagan prestaciones completas + indemnización.'}
                    </p>
                </div>
                
                <div class="resumen-item">
                    <span>📅 Días trabajados en el mes:</span>
                    <strong>${l.salario_dias_trabajados?.dias_trabajados || 0} días</strong>
                </div>
                <div class="resumen-item">
                    <span>💰 Salario por días trabajados:</span>
                    <strong>Q${Number(l.salario_dias_trabajados?.monto || 0).toFixed(2)}</strong>
                </div>
                
                <div class="resumen-item">
                    <span>🎄 Aguinaldo proporcional:</span>
                    <strong>Q${Number(l.aguinaldo?.monto || 0).toFixed(2)}</strong>
                </div>
                <div class="resumen-item" style="font-size:12px; color:#666; padding-left:20px;">
                    <span>Período: ${l.aguinaldo?.periodo_inicio || ''} al ${l.aguinaldo?.periodo_fin || ''}</span>
                    <span>${l.aguinaldo?.dias_trabajados || 0} días trabajados</span>
                </div>
                
                <div class="resumen-item">
                    <span>📅 Bono 14 proporcional:</span>
                    <strong>Q${Number(l.bono14?.monto || 0).toFixed(2)}</strong>
                </div>
                <div class="resumen-item" style="font-size:12px; color:#666; padding-left:20px;">
                    <span>Período: ${l.bono14?.periodo_inicio || ''} al ${l.bono14?.periodo_fin || ''}</span>
                    <span>${l.bono14?.dias_trabajados || 0} días trabajados</span>
                </div>
                
                <div class="resumen-item">
                    <span>🏖️ Vacaciones proporcionales:</span>
                    <strong>${l.vacaciones?.dias?.toFixed(1) || 0} días (Q${Number(l.vacaciones?.monto || 0).toFixed(2)})</strong>
                </div>`;
            
            if(tipoBaja === 'despido') {
                html += `
                <div class="resumen-item">
                    <span>⚖️ Indemnización (${l.anos_trabajados || 0} años):</span>
                    <strong>Q${Number(l.indemnizacion || 0).toFixed(2)}</strong>
                </div>`;
            }
            
            html += `
                <div class="liquidacion-total">
                    TOTAL LIQUIDACIÓN: Q${Number(l.total_liquidacion || 0).toFixed(2)}
                </div>
                
                <div style="margin-top: 15px; padding: 10px; background: #e9ecef; border-radius: 4px; font-size: 12px;">
                    <strong>Nota:</strong> La liquidación incluye:
                    <ul>
                        <li>Salario de días trabajados del mes</li>
                        <li>Aguinaldo proporcional (desde diciembre)</li>
                        <li>Bono 14 proporcional (desde julio)</li>
                        <li>Vacaciones proporcionales (15 días por año)</li>
                        ${tipoBaja === 'despido' ? '<li>Indemnización: 1 mes por año trabajado (mínimo 3 meses)</li>' : ''}
                    </ul>
                </div>
            `;
            
            container.innerHTML = html;
        }
        
        // Cargar liquidación inicial
        document.addEventListener('DOMContentLoaded', function() {
            actualizarLiquidacion();
        });
    </script>
</body>
</html>