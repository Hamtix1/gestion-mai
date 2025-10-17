# 🧪 CHECKLIST DE TESTING MANUAL - GESTIÓN MAI

## ✅ RESULTADO DEL TESTING AUTOMÁTICO (Backend)

**Estado: APROBADO** ✅

### Resumen de Datos:
- ✅ 6 Campañas registradas
- ✅ 7 Esterilizaciones 
- ✅ 7 Pagos procesados
- ✅ 4 Ingresos generados automáticamente
- ✅ 0 Gastos (pendiente de prueba manual)
- ✅ 1 Usuario administrador

### Sincronización Pagos → Ingresos:
- ✅ Esterilización #1: $150 → Ingreso: $150 ✓
- ✅ Esterilización #4: $100 → Ingreso: $100 ✓
- ✅ Esterilización #5: $150 → Ingreso: $150 ✓
- ✅ Esterilización #6: $95,000 → Ingreso: $95,000 ✓

### Integridad de Datos:
- ✅ 0 pagos huérfanos
- ✅ 0 esterilizaciones sin campaña
- ✅ 0 ingresos sin referencia
- ✅ 0 inconsistencias detectadas

---

## 📋 CHECKLIST DE TESTING MANUAL (Frontend)

### 1. AUTENTICACIÓN Y PERFIL
- [ ] Login con credenciales correctas
- [ ] Login con credenciales incorrectas (validar error)
- [ ] Ver perfil de usuario
- [ ] Editar perfil (nombre, email, teléfono)
- [ ] Cambiar contraseña
- [ ] Logout

### 2. DASHBOARD PRINCIPAL
- [ ] Ver estadísticas generales (campañas, esterilizaciones, ingresos, gastos)
- [ ] Verificar que NO aparezcan valores $NaN
- [ ] Ver gráficos de ingresos por fuente
- [ ] Ver gráficos de gastos por categoría
- [ ] Ver balance total calculado correctamente

### 3. GESTIÓN DE CAMPAÑAS
**3.1 Listado**
- [ ] Ver lista de campañas con paginación
- [ ] Usar filtros (búsqueda, estado, fecha)
- [ ] Ver estadísticas de campañas (total, activas, completadas, canceladas)
- [ ] Verificar formato de fechas (dd/mm/yyyy)

**3.2 Crear Nueva Campaña**
- [ ] Abrir formulario de nueva campaña
- [ ] Validar campos requeridos (nombre, fecha inicio, ubicación)
- [ ] Crear campaña con todos los campos
- [ ] Verificar que aparezca en el listado

**3.3 Editar Campaña**
- [ ] Abrir campaña existente
- [ ] Modificar datos
- [ ] Guardar cambios
- [ ] Verificar actualización en listado

**3.4 Detalle de Campaña**
- [ ] Ver detalle de campaña
- [ ] Ver estadísticas (capacidad, disponibles, confirmadas)
- [ ] Ver lista de esterilizaciones asociadas
- [ ] Ver monto total recaudado

**3.5 Eliminar Campaña**
- [ ] Intentar eliminar campaña
- [ ] Confirmar modal de eliminación
- [ ] Verificar que se elimine del listado

### 4. GESTIÓN DE ESTERILIZACIONES
**4.1 Listado**
- [ ] Ver lista de esterilizaciones con paginación
- [ ] Usar filtros (búsqueda, campaña, estado, estado de pago)
- [ ] Ver estadísticas (total, completadas, pendientes, canceladas)
- [ ] Ver estado de pago con badges de color

**4.2 Crear Nueva Esterilización**
- [ ] Abrir formulario
- [ ] Llenar datos del dueño (nombre, documento, teléfono, dirección)
- [ ] Llenar datos de la mascota (nombre, especie, raza, edad, peso)
- [ ] Seleccionar campaña
- [ ] Establecer costo
- [ ] Crear esterilización
- [ ] Verificar payment_status = 'pending'

**4.3 Detalle de Esterilización**
- [ ] Ver todos los datos de la esterilización
- [ ] Ver historial de pagos
- [ ] Ver saldo pendiente calculado correctamente
- [ ] Registrar un pago parcial
- [ ] Verificar que payment_status cambie a 'partial'
- [ ] Completar el pago total
- [ ] Verificar que payment_status cambie a 'completed'
- [ ] **IMPORTANTE:** Verificar que se cree automáticamente un ingreso

**4.4 Editar y Eliminar**
- [ ] Editar esterilización
- [ ] Guardar cambios
- [ ] Eliminar esterilización (si no tiene pagos)

### 5. GESTIÓN DE PAGOS
**5.1 Listado**
- [ ] Ver lista de pagos con paginación
- [ ] Usar filtros (búsqueda, campaña, método, rango de fechas)
- [ ] Ver estadísticas (total, efectivo, transferencia/tarjeta)
- [ ] Verificar que NO aparezcan valores $NaN

**5.2 Crear Pago desde Esterilización**
- [ ] Abrir detalle de esterilización con saldo pendiente
- [ ] Registrar pago parcial
- [ ] Verificar referencia auto-generada (PAY-YYYYMMDD-XXXX)
- [ ] Ver pago en historial

**5.3 Sincronización Automática**
- [ ] Registrar el último pago que complete una esterilización
- [ ] Ir al módulo de Ingresos
- [ ] **VERIFICAR:** Debe aparecer un ingreso automático con:
  - Concepto: "Esterilización #X - [Nombre Mascota]"
  - Fuente: "sterilization"
  - Referencia: "STER-X"
  - Monto: igual al costo total de la esterilización

### 6. MÓDULO FINANCIERO
**6.1 Dashboard Financiero**
- [ ] Ver resumen financiero (ingresos, gastos, pagos, balance)
- [ ] Verificar que NO aparezcan valores $NaN
- [ ] Ver ingresos por fuente (esterilizaciones, donaciones, recaudaciones, otros)
- [ ] Ver gastos por categoría (6 categorías)
- [ ] Ver movimientos recientes

**6.2 Gestión de Ingresos - Listado**
- [ ] Ver lista de ingresos con paginación (15 items/página)
- [ ] Ver estadísticas (total, por esterilizaciones, donaciones, otros)
- [ ] Usar filtros (búsqueda, campaña, fuente, rango fechas)
- [ ] Verificar ingresos automáticos de esterilizaciones pagadas
- [ ] Verificar referencia "STER-X" en ingresos automáticos

**6.3 Crear Ingreso Manual**
- [ ] Abrir formulario de nuevo ingreso
- [ ] Llenar campos:
  - Concepto (requerido)
  - Descripción (opcional)
  - Monto (requerido, con prefijo $)
  - Fuente: seleccionar "donation" o "fundraising"
  - Fecha (por defecto hoy)
  - Campaña (opcional)
  - Número de referencia (opcional)
- [ ] Guardar ingreso
- [ ] Verificar en listado
- [ ] Verificar que se actualice el total en dashboard

**6.4 Editar y Eliminar Ingresos**
- [ ] Editar ingreso manual (NO los automáticos de STER-X)
- [ ] Guardar cambios
- [ ] Eliminar ingreso manual
- [ ] Confirmar eliminación en modal

**6.5 Gestión de Gastos - Listado**
- [ ] Ver lista de gastos (debe estar vacía inicialmente)
- [ ] Ver estadísticas (total, por médicos, suministros, otros)
- [ ] Usar filtros (búsqueda, campaña, categoría, rango fechas)

**6.6 Crear Nuevo Gasto**
- [ ] Abrir formulario de nuevo gasto
- [ ] Llenar campos:
  - Concepto (requerido)
  - Descripción (opcional)
  - Monto (requerido, con prefijo $)
  - Categoría: seleccionar (medical/transportation/supplies/marketing/administrative/other)
  - Fecha (por defecto hoy)
  - Campaña (opcional)
  - Número de factura (opcional)
  - Proveedor (opcional)
- [ ] Guardar gasto
- [ ] Verificar en listado
- [ ] Verificar que se actualice el total en dashboard
- [ ] Verificar que el balance disminuya

**6.7 Editar y Eliminar Gastos**
- [ ] Editar gasto
- [ ] Guardar cambios
- [ ] Eliminar gasto
- [ ] Confirmar eliminación

**6.8 Verificación de Cálculos**
- [ ] Ir al Dashboard Financiero
- [ ] Verificar: Balance = Total Ingresos - Total Gastos
- [ ] Crear un gasto de $100
- [ ] Verificar que el balance disminuya en $100
- [ ] Crear un ingreso manual de $50
- [ ] Verificar que el balance aumente en $50
- [ ] **IMPORTANTE:** Verificar que NO aparezcan valores $NaN en ningún lugar

### 7. GESTIÓN DE USUARIOS
**7.1 Listado**
- [ ] Ver lista de usuarios
- [ ] Ver estadísticas (total, admins, vendedores, activos)
- [ ] Usar filtros (búsqueda, rol, estado)
- [ ] Ver badges de rol con colores

**7.2 Crear Nuevo Usuario**
- [ ] Abrir formulario
- [ ] Llenar campos:
  - Nombre completo (requerido)
  - Email (requerido, validar formato)
  - Teléfono (opcional)
  - Contraseña (requerido, mínimo 8 caracteres)
  - Confirmar contraseña (debe coincidir)
  - Rol: seleccionar (Admin/Vendedor/Usuario)
  - Usuario Activo (checkbox)
- [ ] Guardar usuario
- [ ] Verificar en listado

**7.3 Editar Usuario**
- [ ] Abrir usuario existente
- [ ] Modificar datos
- [ ] **NOTA:** Contraseña es opcional en edición
- [ ] Dejar contraseña en blanco (debe mantener la actual)
- [ ] Guardar cambios

**7.4 Toggle Estado Usuario**
- [ ] Hacer clic en botón de estado (Activo/Inactivo)
- [ ] Verificar cambio de estado
- [ ] Intentar cambiar tu propio estado (debe estar bloqueado)

**7.5 Eliminar Usuario**
- [ ] Intentar eliminar usuario (no puedes eliminarte a ti mismo)
- [ ] Crear un usuario de prueba
- [ ] Eliminar usuario de prueba
- [ ] Confirmar en modal

### 8. FLUJO COMPLETO DE PRUEBA
**8.1 Crear Flujo desde Cero**
1. [ ] Crear nueva campaña "Testing Octubre 2025"
2. [ ] Crear esterilización asociada a esa campaña (costo: $120)
3. [ ] Registrar pago parcial de $50 desde el detalle
   - [ ] Verificar payment_status = 'partial'
4. [ ] Registrar pago de $70 (completar)
   - [ ] Verificar payment_status = 'completed'
5. [ ] Ir a Ingresos
   - [ ] **VERIFICAR:** Debe existir ingreso automático de $120 con referencia STER-[id]
6. [ ] Crear gasto manual de $30 para medicamentos
7. [ ] Ir al Dashboard Financiero
   - [ ] Verificar Total Ingresos incluye los $120
   - [ ] Verificar Total Gastos incluye los $30
   - [ ] Verificar Balance = Ingresos - Gastos
   - [ ] **VERIFICAR:** NO hay valores $NaN

**8.2 Verificar Responsividad**
- [ ] Probar todas las vistas en móvil (< 768px)
- [ ] Verificar que las tablas tengan scroll horizontal
- [ ] Verificar que los filtros se apilen verticalmente
- [ ] Verificar que los botones sean full-width

### 9. VERIFICACIÓN DE ERRORES
**9.1 Validaciones Frontend**
- [ ] Intentar enviar formularios vacíos (deben mostrar errores)
- [ ] Intentar email inválido (validar formato)
- [ ] Intentar contraseña < 8 caracteres
- [ ] Intentar montos negativos o cero

**9.2 Errores del Servidor**
- [ ] Intentar crear email duplicado
- [ ] Verificar que se muestren errores del servidor
- [ ] Verificar que los errores se muestren por campo

**9.3 Estados de Carga**
- [ ] Verificar spinners durante carga de datos
- [ ] Verificar estados "Guardando..." en botones de submit
- [ ] Verificar mensajes "Cargando..." en tablas

### 10. NAVEGACIÓN Y RUTAS
- [ ] Verificar que todos los enlaces del menú funcionen
- [ ] Verificar breadcrumbs en vistas de detalle
- [ ] Verificar botones "Volver al listado"
- [ ] Verificar que las rutas protegidas redirijan al login
- [ ] Verificar que 404 muestre página de error

---

## 📊 RESULTADOS ESPERADOS

### ✅ BACKEND (Completado)
- Total de rutas API: **47 rutas**
- Sincronización pagos→ingresos: **100% funcional**
- Integridad de datos: **Sin inconsistencias**
- Balance actual: **$95,400.00**

### ⏳ FRONTEND (Pendiente de testing manual)
- [ ] Todas las vistas cargan sin errores
- [ ] No hay valores $NaN en ninguna vista
- [ ] Todas las operaciones CRUD funcionan
- [ ] Sincronización automática funciona desde UI
- [ ] Responsive en todos los dispositivos

---

## 🎯 CRITERIOS DE ACEPTACIÓN

1. ✅ Backend API completo y funcional
2. ⏳ Frontend sin errores de TypeScript
3. ⏳ Sincronización automática visible en UI
4. ⏳ Todos los cálculos sin $NaN
5. ⏳ CRUD completo en todos los módulos
6. ⏳ Validaciones frontend y backend
7. ⏳ Responsive design
8. ⏳ Manejo de errores apropiado

---

## 📝 NOTAS IMPORTANTES

### Credenciales de Prueba:
- **Email:** admin@mai.com
- **Contraseña:** admin123

### Roles Disponibles:
1. **Administrador** (id: 1) - Acceso completo
2. **Vendedor** (id: 2) - Registra esterilizaciones y pagos
3. **Usuario** (id: 3) - Acceso limitado

### URLs:
- **Backend:** http://gestion-mai.test/
- **Frontend:** http://localhost:5173/
- **API Docs:** Ver API_DOCUMENTATION.md

### Puntos Críticos a Verificar:
1. **Sincronización automática:** Al completar pago de esterilización, debe crear ingreso automáticamente
2. **Valores $NaN:** NO deben aparecer en ninguna vista (ya corregido con Number())
3. **Referencias automáticas:** 
   - Pagos: PAY-YYYYMMDD-XXXX
   - Ingresos de esterilizaciones: STER-[id]
4. **Balance financiero:** Debe calcular correctamente (Ingresos - Gastos)

---

**Fecha de Testing:** 16 de Octubre de 2025
**Sistema:** Gestión MAI - Sistema de Gestión de Campañas de Esterilización Animal
**Versión:** 1.0.0
