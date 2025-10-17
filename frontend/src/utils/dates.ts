/**
 * Utilidades para manejo de fechas
 * 
 * Funciones helper para formatear y manipular fechas en la aplicación
 */

/**
 * Convierte una fecha ISO 8601 completa a formato yyyy-MM-dd
 * para ser usada en inputs de tipo date
 * 
 * @param isoDate - Fecha en formato ISO 8601 (ej: "2025-10-16T00:00:00.000000Z")
 * @returns Fecha en formato yyyy-MM-dd (ej: "2025-10-16") o cadena vacía si es inválida
 * 
 * @example
 * formatDateForInput("2025-10-16T00:00:00.000000Z") // "2025-10-16"
 * formatDateForInput("2025-11-30") // "2025-11-30"
 */
export function formatDateForInput(isoDate: string | null | undefined): string {
  if (!isoDate) return ''
  
  try {
    // Si ya está en formato yyyy-MM-dd, retornarlo directamente
    if (/^\d{4}-\d{2}-\d{2}$/.test(isoDate)) {
      return isoDate
    }
    
    // Si es formato ISO completo, extraer solo la parte de la fecha
    const date = new Date(isoDate)
    if (isNaN(date.getTime())) {
      return ''
    }
    
    const year = date.getFullYear()
    const month = String(date.getMonth() + 1).padStart(2, '0')
    const day = String(date.getDate()).padStart(2, '0')
    
    return `${year}-${month}-${day}`
  } catch {
    return ''
  }
}

/**
 * Convierte una fecha en formato yyyy-MM-dd a Date
 * 
 * @param dateString - Fecha en formato yyyy-MM-dd
 * @returns Objeto Date o null si es inválida
 */
export function parseInputDate(dateString: string): Date | null {
  if (!dateString) return null
  
  try {
    const date = new Date(dateString)
    return isNaN(date.getTime()) ? null : date
  } catch {
    return null
  }
}

/**
 * Formatea una fecha para mostrar en formato legible
 * 
 * @param date - Fecha en cualquier formato válido
 * @param locale - Locale para el formato (default: 'es-ES')
 * @returns Fecha formateada (ej: "16 de octubre de 2025")
 */
export function formatDateDisplay(date: string | Date, locale: string = 'es-ES'): string {
  if (!date) return ''
  
  try {
    const dateObj = typeof date === 'string' ? new Date(date) : date
    if (isNaN(dateObj.getTime())) return ''
    
    return dateObj.toLocaleDateString(locale, {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    })
  } catch {
    return ''
  }
}

/**
 * Formatea una fecha y hora para mostrar
 * 
 * @param date - Fecha en cualquier formato válido
 * @param locale - Locale para el formato (default: 'es-ES')
 * @returns Fecha y hora formateadas
 */
export function formatDateTimeDisplay(date: string | Date, locale: string = 'es-ES'): string {
  if (!date) return ''
  
  try {
    const dateObj = typeof date === 'string' ? new Date(date) : date
    if (isNaN(dateObj.getTime())) return ''
    
    return dateObj.toLocaleString(locale, {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    })
  } catch {
    return ''
  }
}
