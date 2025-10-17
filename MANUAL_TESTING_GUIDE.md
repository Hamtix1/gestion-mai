# 🎯 GUÍA RÁPIDA - TESTING MANUAL

## 🚀 PASO 1: INICIAR SERVIDORES

### Backend (Laravel)
1. Abrir terminal en `c:\laragon\www\gestion-mai\backend`
2. Ejecutar: `C:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php.exe artisan serve`
3. Verificar que responda en: http://127.0.0.1:8000

### Frontend (Vue)
1. Abrir terminal en `c:\laragon\www\gestion-mai\frontend`
2. Ejecutar: `npm run dev`
3. Abrir navegador en: http://localhost:5173

---

## 🔐 PASO 2: LOGIN

1. Ir a: http://localhost:5173/login
2. Usar credenciales:
   - **Email:** admin@mai.com
   - **Password:** admin123
3. Verificar que redirija al dashboard

---

## 📊 PASO 3: VERIFICAR DASHBOARD FINANCIERO

1. Ir a: `/admin/financial`
2. **VERIFICAR:**
   - [ ] Total Ingresos: $95,400.00
   - [ ] Total Gastos: $0.00
   - [ ] Balance: $95,400.00
   - [ ] **NO hay valores $NaN**
   - [ ] Gráfico de ingresos por fuente muestra "sterilization: $95,400"

---

## 🧪 PASO 4: PROBAR FLUJO COMPLETO

### A. Crear Nueva Campaña
1. Ir a: `/admin/campaigns`
2. Click "Nueva Campaña"
3. Llenar:
   - Nombre: "Testing Manual Octubre"
   - Fecha inicio: 2025-10-20
   - Fecha fin: 2025-10-27
   - Ubicación: "Centro de Pruebas"
   - Capacidad: 20
   - Costo: $100
4. Guardar
5. **VERIFICAR:** Aparece en el listado

### B. Crear Esterilización
1. Ir a: `/admin/sterilizations`
2. Click "Nueva Esterilización"
3. Llenar datos:
   - **Dueño:**
     - Nombre: Juan Testing
     - Documento: 123456789
     - Teléfono: 300123456
     - Dirección: Calle Test 123
   - **Mascota:**
     - Nombre: Firulais Test
     - Especie: Perro
     - Raza: Mestizo
     - Edad: 2
     - Peso: 10
   - **Campaña:** Seleccionar "Testing Manual Octubre"
   - **Costo:** $100
4. Guardar
5. **VERIFICAR:** 
   - Aparece en listado
   - payment_status = "pending" (badge rojo)

### C. Registrar Pago Parcial
1. Click en la esterilización recién creada
2. En "Registrar Pago" llenar:
   - Monto: $50
   - Método: Efectivo
   - Fecha: Hoy
3. Registrar
4. **VERIFICAR:**
   - Saldo pendiente: $50.00
   - payment_status = "partial" (badge amarillo)
   - Pago aparece en historial

### D. Completar Pago
1. En el mismo detalle, registrar otro pago:
   - Monto: $50
   - Método: Transferencia
2. Registrar
3. **VERIFICAR:**
   - Saldo pendiente: $0.00
   - payment_status = "completed" (badge verde)

### E. VERIFICAR SINCRONIZACIÓN AUTOMÁTICA
1. Ir a: `/admin/financial/incomes`
2. **VERIFICAR:**
   - Debe aparecer un nuevo ingreso automático
   - Concepto: "Esterilización #[ID] - Firulais Test"
   - Fuente: "sterilization"
   - Monto: $100.00
   - Referencia: "STER-[ID]"
3. Ir a: `/admin/financial`
4. **VERIFICAR:**
   - Total Ingresos aumentó en $100 → **$95,500.00**
   - Balance aumentó en $100 → **$95,500.00**
   - **NO hay valores $NaN**

### F. Crear Gasto Manual
1. Ir a: `/admin/financial/expenses`
2. Click "Nuevo Gasto"
3. Llenar:
   - Concepto: "Medicamentos de prueba"
   - Descripción: "Testing del sistema"
   - Monto: $30
   - Categoría: "medical"
   - Fecha: Hoy
   - Campaña: "Testing Manual Octubre"
   - Factura: FAC-TEST-001
   - Proveedor: Farmacia Test
4. Guardar
5. **VERIFICAR:**
   - Aparece en listado de gastos
   - Estadísticas actualizadas

### G. VERIFICAR BALANCE FINAL
1. Ir a: `/admin/financial`
2. **VERIFICAR:**
   - Total Ingresos: **$95,500.00**
   - Total Gastos: **$30.00**
   - Balance: **$95,470.00** (95,500 - 30)
   - **NO hay valores $NaN en ningún lugar**

---

## 👥 PASO 5: PROBAR GESTIÓN DE USUARIOS

### A. Crear Usuario
1. Ir a: `/admin/users`
2. Click "Nuevo Usuario"
3. Llenar:
   - Nombre: Usuario Testing
   - Email: testing@mai.com
   - Teléfono: 300999999
   - Contraseña: testing123
   - Confirmar: testing123
   - Rol: Vendedor
   - Usuario Activo: Sí
4. Guardar
5. **VERIFICAR:** Aparece en listado

### B. Toggle Estado
1. Click en botón de estado del usuario recién creado
2. **VERIFICAR:** Estado cambia a "Inactivo"
3. Click nuevamente
4. **VERIFICAR:** Estado vuelve a "Activo"

### C. Editar Usuario
1. Click en botón editar
2. Modificar teléfono: 300888888
3. Dejar contraseña en blanco
4. Guardar
5. **VERIFICAR:** Cambios guardados

### D. Protección
1. Intentar cambiar tu propio estado
2. **VERIFICAR:** Botón debe estar deshabilitado

---

## ✅ CHECKLIST DE VERIFICACIÓN RÁPIDA

### Dashboard
- [ ] Sin errores de carga
- [ ] Estadísticas muestran números correctos
- [ ] Balance calculado correctamente
- [ ] **NO hay valores $NaN**

### Campañas
- [ ] Listado carga correctamente
- [ ] Crear funciona
- [ ] Editar funciona
- [ ] Eliminar funciona
- [ ] Detalle muestra estadísticas

### Esterilizaciones
- [ ] Listado carga correctamente
- [ ] Crear funciona
- [ ] Estados de pago se actualizan
- [ ] Detalle muestra historial de pagos

### Pagos
- [ ] Se registran correctamente
- [ ] Referencias auto-generadas (PAY-YYYYMMDD-XXXX)
- [ ] Actualizan payment_status

### Sincronización Automática ⚡
- [ ] Al completar pago → se crea ingreso automático
- [ ] Ingreso tiene referencia STER-[ID]
- [ ] Ingreso tiene fuente "sterilization"
- [ ] Monto coincide con costo de esterilización

### Módulo Financiero
- [ ] Dashboard sin $NaN
- [ ] Ingresos se listan correctamente
- [ ] Gastos se listan correctamente
- [ ] Balance = Ingresos - Gastos
- [ ] Crear/Editar/Eliminar funciona

### Usuarios
- [ ] Listado carga correctamente
- [ ] Crear funciona
- [ ] Toggle estado funciona
- [ ] Editar funciona
- [ ] No puedes desactivarte a ti mismo

---

## 🎯 RESULTADO ESPERADO

Al completar todas las pruebas:

```
✅ 6 Campañas (+ 1 nueva)
✅ 8 Esterilizaciones (+ 1 nueva)
✅ 9 Pagos (+ 2 nuevos)
✅ 5 Ingresos (+ 1 automático)
✅ 1 Gasto (+ 1 nuevo)
✅ 2 Usuarios (+ 1 nuevo)

💰 Balance Final: $95,470.00
   (95,500 ingresos - 30 gastos)

🎉 TODO FUNCIONAL - SIN ERRORES
```

---

## 📝 REPORTAR PROBLEMAS

Si encuentras algún problema, anota:
1. Vista donde ocurrió
2. Acción realizada
3. Error mostrado (si hay)
4. Screenshot (si es posible)

---

**Tiempo estimado de testing:** 15-20 minutos

**¡Éxito en las pruebas!** 🚀
