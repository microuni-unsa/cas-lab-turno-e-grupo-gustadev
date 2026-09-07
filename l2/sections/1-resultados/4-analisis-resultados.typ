== V. Análisis de Resultados

A fin de validar cuantitativa y cualitativamente el procedimiento experimental desarrollado, se evalúan las tres interrogantes metodológicas de la guía de práctica:

*1. ¿Con qué valores comprobaste que tu práctica estuviera correcta?*
- *Catálogo de Entrada:* Se introdujeron formalmente 5 solicitudes de cambio (RFCs: `CHG-001` a `CHG-005`) derivadas de los requerimientos especificados en el Laboratorio 1.
- *Validación de Estados de Ciclo de Vida:* Se verificó que el sistema admitiera y forzara las transiciones de estado canónicas: _Pendiente_, _En revisión_, _En desarrollo_, _En pruebas_ y _Cerrado_.
- *Consistencia de Metadatos:* Cada registro incluyó de manera estricta identificador unívoco, requerimiento base afectado, motivo justificado de la modificación, nivel de prioridad, responsable asignado y fecha límite de entrega.
- *Ejecución de Servicios Contenerizados y CLI:* Se comprobó que el endpoint `/health` de i-doit reportara estado `ready` (versión 38) y que las consultas de la CLI de GitHub (`gh issue list`) retornaran la totalidad de los ítems con sus respectivas etiquetas y estados.

*2. ¿Qué resultado esperabas obtener para cada valor de entrada?*
- Que cada solicitud de cambio fuera almacenada de forma persistente con integridad referencial hacia el requerimiento base de origen.
- Que las transiciones de ciclo de vida mantuvieran un historial inalterable de auditoría (registrando fecha, usuario responsable y causa del cambio de estado).
- Que las herramientas permitieran clasificar, filtrar y generar reportes ejecutivos consolidados para el Comité de Control de Cambios (CCB), evidenciando cuellos de botella y requerimientos completados.
- Que no se produjeran inconsistencias entre las fechas planificadas y el avance de las tareas en el tablero colaborativo.

*3. ¿Qué valor o comportamiento obtuviste para cada valor de entrada?*
- La totalidad de los 5 requerimientos de cambio se registraron exitosamente sin pérdida de atributos ni inconsistencias tipológicas en las bases de datos de ambas plataformas.
- La simulación de transiciones reflejó una distribución realista del ciclo de desarrollo: 1 cambio cerrado (`CHG-005`), 1 en pruebas (`CHG-003`), 1 en desarrollo activo (`CHG-001`), 1 en evaluación colegiada (`CHG-002`) y 1 en cola de espera (`CHG-004`).
- La integración de GitHub Projects a través de `gh` demostró alta agilidad operativa para desarrolladores, mientras que i-doit proporcionó una sólida visión de impacto sobre infraestructura y servicios de TI.
- Se constató que un proceso formal de control de cambios previene el crecimiento descontrolado del alcance (_scope creep_) y asegura la estabilidad de las líneas base del proyecto @pressman2020.
