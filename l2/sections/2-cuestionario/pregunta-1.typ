= Pregunta 1: Actividades frecuentes vs. esenciales en la gestión de requerimientos

*Enunciado:* _Actualmente, ¿qué actividades utiliza con frecuencia en la gestión de requerimientos y cuáles considera que son esenciales?_

En la práctica profesional de la ingeniería de software se distinguen dos grupos de actividades:

*1. Actividades utilizadas con frecuencia en el proceso de requerimientos:*
- *Elicitación y Descubrimiento:* Entrevistas a partes interesadas (_stakeholders_), sesiones conjuntas de diseño (JAD), cuestionarios y lluvia de ideas.
- *Documentación y Especificación:* Redacción estructurada de requisitos funcionales y no funcionales mediante fichas técnicas normativas (IEEE Std 830, ISO/IEC/IEEE 29148 @iso29148) o historias de usuario en marcos ágiles.
- *Priorización:* Clasificación por valor de negocio y urgencia utilizando técnicas como MoSCoW, matriz de valor vs. esfuerzo o priorización numérica.
- *Validación y Revisiones de Pares:* Inspecciones formales conjuntas con el cliente para verificar ausencia de ambigüedades.
- *Control de Versiones y Gestión de Cambios:* Registro sistemático de solicitudes de cambio (RFC) y actualización del historial de modificaciones.

*2. Actividades consideradas estrictamente esenciales:*
De acuerdo con Pressman y Maxim @pressman2020 y Sommerville @sommerville2011, tres actividades constituyen el núcleo irremplazable para el éxito del proyecto:
- *Definición Clara y No Ambigua de Requisitos:* Si los requisitos iniciales son defectuosos o ambiguos, cualquier esfuerzo posterior de diseño o codificación amplificará el costo de corrección de forma exponencial.
- *Gestión y Control Formal de Cambios:* El cambio en los requisitos es inevitable a medida que los clientes maduran su visión del producto @baufest2003. Sin un mecanismo de evaluación de impacto y aprobación previa por un comité (CCB), el proyecto sufre de corrupción de alcance (_scope creep_) y desfase presupuestal.
- *Trazabilidad Bidireccional:* Permite vincular cada necesidad del negocio con su correspondiente requisito, componente de arquitectura, caso de prueba y commit de código fuente. Garantiza que no existan requerimientos huérfanos ni código superfluo no solicitado @torres2002.
