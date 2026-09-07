== IV. Análisis y Discusión de Resultados

La evaluación del proceso experimental se articula mediante el análisis de tres interrogantes metodológicas clave:

*1. ¿Con qué valores o parámetros empíricos se comprobó la validez de la práctica?*
- *Vector de Entradas Formalizado:* Diez Solicitudes de Cambio (`CHG-001` a `CHG-010`) derivadas de los requerimientos funcionales y no funcionales del Laboratorio 1, cubriendo modificaciones de alcance, seguridad, arquitectura e interoperabilidad.
- *Atributos de Configuración Obligatorios:* Cada RFC incorporó de forma mandatoria: identificador unívoco, requerimiento de origen, justificación técnica, nivel de criticidad (Baja, Media, Alta, Crítica), responsable técnico y fecha límite improrrogable.
- *Parametrización de Máquina de Estados:* Cobertura de las cinco transiciones formales del ciclo de vida (_Pendiente_, _En revisión_, _En desarrollo_, _En pruebas_ y _Cerrado_).
- *Registro Dual en Plataformas:* Corroboración de coherencia entre los 5 módulos de software catalogados como CIs en la CMDB de i-doit y las 10 tarjetas sincronizadas en el proyecto de GitHub Projects.

*2. ¿Qué comportamiento y resultados se esperaban obtener a partir de los valores de entrada?*
- *Contención del Scope Creep:* Que la exigencia de una ficha de RFC y el filtro previo del CCB eliminaran incorporaciones arbitrarias de código no planificado @pressman2020.
- *Eliminación de Ambigüedad en la Asignación:* Que cada requerimiento contara con un único responsable directo, evitando dispersión operativa y dilución de compromisos.
- *Trazabilidad de Auditoría Ininterrumpida:* Que cualquier alteración en los componentes generara un evento inmutable con autor y fecha tanto en el Logbook de i-doit como en el historial de confirmaciones de Git.
- *Visibilidad Operativa en Tiempo Real:* Que los líderes técnicos pudieran diagnosticar cuellos de botella analizando la acumulación de tarjetas en columnas específicas del tablero Kanban.

*3. ¿Qué valores, métricas y comportamientos se obtuvieron empíricamente?*
- *Distribución Equilibrada del Flujo de Trabajo:* Se obtuvo una distribución controlada del esfuerzo: 2 tareas concluidas y fusionadas (`CHG-005`, `CHG-007`), 2 en verificación de aseguramiento de la calidad QA (`CHG-003`, `CHG-008`), 2 en codificación activa (`CHG-001`, `CHG-006`), 2 en evaluación de viabilidad por el CCB (`CHG-002`, `CHG-009`) y 2 en el backlog priorizado (`CHG-004`, `CHG-010`).
- *Convergencia de Paradigmas:* Se constató que *GitHub Projects* reduce la latencia de comunicación del equipo de desarrollo al unificar tareas con commits y PRs en una sola interfaz, mientras que *i-doit* provee el rigor de gobernanza ITIL necesario para la auditoría formal y el inventario de servicios ante certificaciones de calidad @itil4.
- *Eficacia de la Trazabilidad:* No se registraron requerimientos huérfanos ni tareas sin responsable; el 100% de los commits en el repositorio remoto quedaron vinculados a una RFC formalmente aprobada @torres2002.
