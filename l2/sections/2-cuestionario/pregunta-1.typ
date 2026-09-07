== Pregunta 1: Actividades Frecuentes vs. Esenciales en la Ingeniería de Requerimientos

*Enunciado:* _Actualmente, ¿qué actividades utiliza con frecuencia en la gestión de requerimientos y cuáles considera que son esenciales?_

En la práctica contemporánea de la ingeniería de software se diferencian con claridad las actividades instrumentales rutinarias de aquellas que resultan estrictamente críticas para la viabilidad del proyecto:

*1. Actividades Utilizadas con Frecuencia en el Ciclo de Vida de Requerimientos:*
- *Elicitación Multicanal:* Entrevistas semiestructuradas a partes interesadas (_stakeholders_), talleres conjuntos de diseño de aplicaciones (JAD), observación directa y análisis de documentación preexistente.
- *Especificación Estandarizada:* Modelado formal de requerimientos funcionales y no funcionales conforme a estándares internacionales como ISO/IEC/IEEE 29148:2018 @iso29148 y redacción de historias de usuario con criterios de aceptación Gherkin en marcos ágiles.
- *Priorización Basada en Valor:* Ponderación de criticidad y retorno de inversión utilizando marcos como MoSCoW, modelo Kano y matrices de impacto versus esfuerzo.
- *Validación Cruzada:* Revisiones formales por pares e inspecciones de software para detectar tempranamente inconsistencias, ambigüedades u omisiones antes de la fase de diseño.
- *Gestión Sistemática de Modificaciones:* Registro de Solicitudes de Cambio (RFC) para gobernar las desviaciones respecto a la línea base acordada.

*2. Actividades Consideradas Estrictamente Esenciales:*
Siguiendo los fundamentos de Pressman & Maxim @pressman2020 y Sommerville @sommerville2011, tres actividades constituyen el núcleo irremplazable para salvaguardar el éxito de cualquier producto software:
- *Definición No Ambigua y Verificable de Requerimientos:* Todo requisito debe poseer criterios de aceptación cuantificables y comprobables mediante pruebas. La ambigüedad en etapas tempranas multiplica de forma exponencial el costo de mitigación durante las fases de integración y despliegue @torres2002.
- *Gobernanza y Control Formal de Cambios:* El cambio es una constante biológica del software provocada por la maduración del entendimiento del cliente @baufest2003. Sin un mecanismo formal de análisis de impacto técnico y aprobación por un Comité de Control de Cambios (CCB), el proyecto sucumbe ante la corrupción de alcance y desbordes presupuestarios.
- *Trazabilidad Bidireccional Integral:* Capacidad de rastrear un requisito desde su necesidad de negocio de origen hasta su componente arquitectónico, caso de prueba automatizado y confirmación de código fuente (_commit_). Garantiza la detección inmediata de requerimientos huérfanos y elimina código superfluo no autorizado @ieee828.
