RESULTADOS Y PRUEBAS
I. OBJETIVO:
Proceso de requerimientos de software
Al igual que en el caso de los requisitos, existen muchas definiciones de lo que es la Administración de los
Requerimientos. Entre ellas podemos citar: “… un enfoque sistemático para obtener, organizar y documentar
los requisitos del sistema, así como mantener un acuerdo entre el cliente y el equipo del proyecto en los
cambios a los requisitos. Debemos considerar que la clave para una efectiva administración de requisitos es:
Mantener un enunciado claro de los requisitos, junto con atributos para cada tipo de requisitos y su
seguimiento con otros requisitos o elementos del proyecto.” [TORRES02] “… una forma sistemática de
obtener, organizar y documentar los requisitos de un sistema, y un proceso que establece y mantiene el
acuerdo entre el cliente y el equipo de proyecto sobre los requisitos cambiantes.” [BAUFEST03]. “…RUP
describe cómo: obtener los requisitos, organizarlos, documentar requisitos de funcionalidad y restricciones,
rastrear y documentar decisiones, captar y comunicar requisitos del negocio” [GUERRERO03]
• Gestion de cambios
El cambio es un hecho vital en el desarrollo del software:
• Los clientes desean modificar los requerimientos.
• El equipo de desarrollo desea modificar el enfoque técnico.
• Los gestores desean modificar el enfoque del proyecto.
La causa de todas estas modificaciones se debe a que, a medida que pasa el tiempo, todo el mundo
sabe más (sabe lo que necesita, cómo aproximarse mejor al problema y cómo hacerlo ganando más
dinero). Este conocimiento adicional es la fuerza motriz de la mayoría de los cambios.
El cambio se puede producir en cualquier momento y por cualquier razón. Por ejemplo, se generan
cambios en las revisiones, que nos llevan a la modificación de los elementos de la configuración
(ECSs); durante la fase de desarrollo, se pueden realizar adiciones en los documentos ya
producidos; las pruebas a menudo nos llevan a cambios que se propagan a través de la mayoría de
los ECSs.
II. DESCRIPCION DEL PROCEDIMIENTO REALIZADO
Instalar herramienta para gestión de cambios:
Scarab:
https://github.com/kelvinyelyen/scarab
Idoit:
https://sourceforge.net/projects/i-doit/

|                                                                             |     |                        |     |            |
| --------------------------------------------------------------------------- | --- | ---------------------- | --- | ---------- |
| UNIVERSIDAD NACIONAL DE SAN AGUSTIN                                         |
| FACULTAD DE INGENIERÍA DE PRODUCCIÓN Y SERVICIOS                            |
| ESCUELA PROFESIONAL DE INGENIERÍA DE SISTEMA                                |
| Formato: Guía de Práctica de Laboratorio / Talleres / Centros de Simulación |
| Aprobación: 2022/03/01                                                      |     | Código: GUIA-PRLE-001  |     | Página: 3  |
| -----------------------                                                     | --- | ---------------------- | --- | ---------- |

3. Generar al menos 5 requerimientos del Laboratorio 1 en el ciclo de gestión de
   cambios utilizando la herramienta elegida.
   | ID | Requerimiento | Motivo del Cambio | Estado | Responsable |
   | --- | -------------- | ------------------ | ------- | ------------ |
   Cambio
   CHG- Aumentar límite de sesiones Pruebas mostraron En revisión Equipo
   | 001 | concurrentes a 500 usuarios | caídas con >200 | | técnico |
   | ---- | ---------------------------- | ---------------- | --- | -------- |
   usuarios
   CHG- Implementar autenticación Requisito de seguridad Aprobado Seguridad
   | 002 | 2FA | nuevo del cliente | | |
   | ---- | ---- | ------------------ | --- | --- |
   CHG- Cambiar motor de base de Mejor rendimiento en Aprobado DBA
   | 003 | datos de SQLite a MySQL | producción | | |
   | ---- | ------------------------ | ----------- | --- | --- |
   CHG- Rediseñar interfaz de carrito Usuarios reportaron Pendiente UX/UI
   | 004 | de compras | mala usabilidad | | |
   | ---- | ----------- | ---------------- | --- | --- |
   CHG- Agregar logs distribuidos con Necesidad de monitoreo Implementado DevOps
   | 005 | Graylog | en producción | | |
   | ---- | -------- | -------------- | --- | --- |

4. Generar una tabla de comparación básica entre herramientas
   Criterio Scarab i-doit
   Enfoque Gestión de cambios, incidencias y Gestión de configuración IT, CMDB
   requerimientos
   Lenguaje Java (requiere Tomcat/servlet container) PHP (requiere Apache/Nginx)
   Base de MySQL, PostgreSQL MySQL
   datos
   Usabilidad Interfaz clásica, menos moderna Más intuitiva y con enfoque ITIL
   Escalabilidad Alta, pero requiere tuning Media, orientado a documentación
   Mejor para Desarrollo de software, cambios de requisitos Gestión de infraestructura, activos de
   TI
   Reporte de Seguimiento y Resultados
   Objetivo
   Efectuar un seguimiento a los requerimientos gestionados en la herramienta elegida (ejemplo: Scarab o i-
   doit), verificando el ciclo de vida de los cambios y generando reportes que documenten el estado actual de
   los mismos.
   Procedimiento realizado
   • Se ingresaron 5 requerimientos del Laboratorio 1 dentro de la herramienta de gestión de cambios.
   • A cada requerimiento se le asignó un ciclo de vida: pendiente, en revisión, en desarrollo, en pruebas,
   cerrado.
   • Se configuraron los responsables y fechas límite para cada requerimiento.
   • Se realizaron modificaciones de estado conforme al avance (ejemplo: de “Pendiente” → “En revisión”
   → “En desarrollo”).
   • Se generaron reportes desde la herramienta para mostrar el estado actual de los requerimientos.
   III. Resultados obtenidos
   ID Descripción Estado Responsable Fecha Observaciones
   Req breve actual límite
   RQ- Registro de En Alumno 1 28/09/2025 Se completó la
   01 usuarios desarrollo validación inicial.
   RQ- Autenticación En Alumno 2 29/09/2025 Prueba de login
   02 con contraseña pruebas con casos válidos.
   RQ- Gestión de Pendiente Alumno 3 30/09/2025 A la espera de
   03 productos aprobación del
   cliente.
   RQ- Reporte de En Alumno 4 02/10/2025 Se ajusta formato
   04 inventario revisión del PDF.
   RQ- Notificación de Cerrado Alumno 1 25/09/2025 Implementado y
   05 cambios vía mail validado sin
   errores.
   Análisis de resultados
   • El sistema de gestión de cambios permitió dar trazabilidad a cada requerimiento desde su creación
   hasta su cierre.
   • Se evidenció que el control de estados y responsables ayuda a mantener claridad sobre avances y
   bloqueos.
   • El reporte generado refleja que algunos requerimientos ya están cerrados, mientras que otros aún
   esperan revisión o desarrollo.
   • La herramienta facilita la detección temprana de retrasos, ya que se pueden contrastar las fechas
   límites con el progreso real.
   Conclusiones
   • La gestión de cambios con herramientas como Scarab o i-doit es fundamental para mantener control
   en proyectos distribuidos.
   • El seguimiento periódico permite detectar desviaciones respecto al cronograma y tomar acciones
   preventivas.
   • Los reportes generados ofrecen una visión global del estado de los requerimientos, mejorando la
   comunicación entre equipo y cliente.
   • La trazabilidad obtenida con este proceso asegura que ningún requerimiento quede olvidado y que
   se cumplan los objetivos del laboratorio.
   III. RESULTADOS OBTENIDOS
   ¿Con qué valores comprobaste que tu práctica estuviera correcta?
   • Se ingresaron 5 requerimientos del Laboratorio 1 en la herramienta de gestión de cambios.
   • Se asignaron estados de ciclo de vida: Pendiente, En revisión, En desarrollo, En pruebas y Cerrado.
   • Se definieron responsables y fechas límite para cada requerimiento.
   • Se ejecutaron cambios de estado en función del avance real de cada requerimiento.
   ¿Qué resultado esperabas obtener para cada valor de entrada?
   • Que cada requerimiento ingresado apareciera correctamente en el sistema con sus atributos
   completos (ID, descripción, estado, responsable, fecha).
   • Que la herramienta registrara las transiciones de estado sin pérdida de información.
   • Que fuera posible generar un reporte de seguimiento que muestre el estado actualizado de todos los
   requerimientos.
   ¿Qué valor o comportamiento obtuviste para cada valor de entrada?
   • Todos los requerimientos fueron registrados sin errores en la base de datos de la herramienta.
   • El cambio de estados funcionó de acuerdo con lo esperado (ejemplo: Pendiente → En revisión → En
   desarrollo).
   • Los reportes exportados reflejaron de manera clara el avance, cumpliendo con la trazabilidad
   solicitada.
   • No se detectaron inconsistencias en la asignación de responsables ni en el seguimiento de fechas.
   IV. ANALISIS DE RESULTADOS
   ¿Con qué valores comprobaste que tu práctica estuviera correcta?
   • Con la correcta creación de 5 requerimientos del Laboratorio 1.
   • Con la validación de los estados de ciclo de vida (Pendiente, Revisión, Desarrollo, Pruebas, Cerrado).
   • Con la comparación entre fechas límite definidas y fechas de cierre real.
   • Con la verificación de la generación de reportes automáticos dentro de la herramienta.
   ¿Qué resultado esperabas obtener para cada valor de entrada?
   • Que el sistema permitiera llevar un seguimiento continuo de cada requerimiento.
   • Que las transiciones de estado reflejaran fielmente el progreso de las tareas.
   • Que los reportes sirvieran como evidencia documental del proceso de gestión de cambios.
   ¿Qué valor o comportamiento obtuviste para cada valor de entrada?
   • Se comprobó que la herramienta gestiona los cambios en tiempo real y permite actualizaciones
   dinámicas.
   • Los requerimientos avanzaron conforme al plan trazado, evidenciando un flujo controlado de gestión
   de cambios.
   • El sistema generó reportes que mostraron estado actual, responsable y observaciones, cumpliendo
   con el objetivo de trazabilidad.
   • El resultado general fue exitoso: se pudo comprobar que la práctica asegura control sobre
   modificaciones y facilita la comunicación dentro del equipo.
   V. CUESTIONARIO:
5. Actualmente, ¿qué actividades utiliza con frecuencia en la gestión de requerimientos y cuáles considera
   que son esenciales?
   • Con frecuencia se utilizan actividades como:
   • Levantamiento de requerimientos mediante entrevistas o encuestas.
   • Documentación estructurada de requisitos funcionales y no funcionales.
   • Priorización y clasificación de requerimientos en función de su criticidad.
   • Gestión de cambios para actualizar requisitos a medida que evoluciona el proyecto.
   • Trazabilidad para verificar que cada requerimiento se cumple en diseño, desarrollo y pruebas.
   • Las esenciales son: definición clara de requerimientos, gestión de cambios y trazabilidad, ya que
   aseguran que el producto final cumpla con lo que el cliente espera y reducen riesgos de desviación.
6. ¿Qué herramienta considera de mejor utilidad para la gestión de requerimientos?
   • Depende del tipo de proyecto, pero entre las más útiles están:
   • Jira/Confluence → para proyectos ágiles con alto dinamismo.
   • Scarab o i-doit → cuando se requiere un enfoque académico o de trazabilidad detallada en cambios.
   • IBM DOORS → para proyectos de gran escala y críticos (ejemplo: aeroespacial, automotriz).
   • En un entorno académico o de prácticas, Scarab es de mejor utilidad por ser open source, ligera y con
   soporte de flujo de gestión de cambios básico. En proyectos profesionales, Jira destaca por su
   flexibilidad e integración con otros procesos de desarrollo.
7. ¿Qué % de recursos del proyecto considera aceptable para gestión de cambios?
   • De acuerdo con buenas prácticas de gestión de proyectos de software:
   • Se recomienda destinar entre 10% y 15% de los recursos totales a la gestión de cambios (tiempo,
   personal y presupuesto).
   • Este porcentaje permite controlar adecuadamente las modificaciones sin afectar de forma crítica la
   entrega.
   • En proyectos altamente dinámicos (ágiles), puede llegar al 20%, mientras que en proyectos más estables
   (cascada) suele ser suficiente un 5-10%.
   CONCLUSIONES
   • La gestión de requerimientos es un proceso esencial en el desarrollo de software, ya que permite
   mantener claridad, trazabilidad y control sobre las necesidades del cliente y los cambios que surgen
   durante el ciclo de vida del proyecto.
   • La implementación de herramientas como Scarab o i-doit facilita la administración de cambios, el
   seguimiento de requerimientos y la generación de reportes, contribuyendo a una mayor transparencia
   en el trabajo en equipo.
   • Los resultados obtenidos en la práctica muestran que el uso de estados de ciclo de vida (pendiente,
   revisión, desarrollo, pruebas y cerrado) asegura un flujo ordenado de trabajo, minimizando riesgos de
   pérdida de información y retrasos.
   • Destinar un porcentaje adecuado de recursos a la gestión de cambios (entre 10% y 15%) resulta
   indispensable para garantizar la adaptación del proyecto a nuevas necesidades sin comprometer los
   tiempos ni la calidad final del software.
   METODOLOGÍA DE TRABAJO
   Colocar la metodología de trabajo que ha utilizado el estudiante o el grupo para resolver la práctica, es decir el
   procedimiento/secuencia de pasos en forma general.
   REFERENCIAS Y BIBLIOGRAFÍA
8. Colocare las referencias utilizadas para el desarrollo de la práctica en formato IEEE Baufest. (2003).
   Administración de requerimientos en proyectos de software. Baufest.
9. Guerrero, C. (2003). Requisitos de software y el proceso RUP. Editorial Universitaria.
10. Torres, J. M. (2002). Gestión de requerimientos en ingeniería de software. Universidad Nacional
    Autónoma de México.
11. Pressman, R. S., & Maxim, B. R. (2020). Ingeniería del software: Un enfoque práctico (8.ª ed.). McGraw-
    Hill.
