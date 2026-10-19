== I. Objetivo

Instalar, probar, evaluar y utilizar una herramienta de gestión de riesgos sobre un sistema académico ficticio, aplicando la metodología MAGERIT y el esquema ISO 31000/27005: identificar activos, estimar probabilidad e impacto (P×I), tratar los riesgos y decidir sobre el riesgo residual frente al umbral docente R≤4 @magerit-v3-libro1 @iso31000 @iso27005.

== II. Descripción del Procedimiento

El trabajo se limitó a un caso único, desligado de laboratorios previos: *sistema académico ficticio, alcance matrícula/notas/consulta, horizonte 12 meses*. Se trabajó sobre la copia `src/Plan_Riesgo_L6.xlsx` (el original de `guide/` quedó intacto) y sobre Pirani @pirani-app. El portal PILAR del CCN-CERT resultó inaccesible durante el laboratorio, por lo que Pirani se adoptó como herramienta sustituta y dicha limitación se documenta en el informe.

+ *Inventario:* se completaron los activos A1–A6 (propietario, dependencias, valoración C/I/D/A de 1–5 y justificación).
+ *Escenarios:* se definieron 8 cruces R1–R8 (activo × amenaza T1–T10, con vulnerabilidad, dimensión, controles actuales, P/I y justificación). Solo se editaron celdas amarillas de entrada; las fórmulas azules quedaron intactas.
+ *Tratamiento:* para cada riesgo se registró acción concreta, responsable, plazo, estado y evidencia (`Supuesto didáctico` al tratarse de una propuesta), además de P/I residual y la decisión correspondiente (`Solicitar aceptación` si R≤4, `Nuevo tratamiento` si >4).
+ *Herramienta:* el mismo conjunto de riesgos se replicó en Pirani para contrastar la matriz y los niveles de riesgo con los del Excel.
