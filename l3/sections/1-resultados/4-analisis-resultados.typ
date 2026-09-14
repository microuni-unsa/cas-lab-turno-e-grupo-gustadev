== IV. Análisis de Resultados

=== a) Reducción de la Ambigüedad: Notación UML vs. Lenguaje Natural

La especificación puramente textual adolece frecuentemente de imprecisiones semánticas e interpretaciones dispares @iso29148. La transición hacia artefactos estandarizados de modelado UML y la adhesión al estándar IEEE Std. 1016-2009 permitieron evidenciar ventajas operativas claras:

1. *Claridad en la Lógica Concurrente:* En el procesamiento de órdenes, la anotación de disparador múltiple por artículo de línea y la condición de sincronización compuesta adquieren precisión matemática en el diagrama de actividades. Se explicita sin ambigüedad que el despacho requiere tanto la asignación total de existencias como la autorización bancaria del pago.
2. *Rigor en Restricciones e Invariantes Complejas:* La estructura de pedidos simples y compuestos reúne múltiples reglas acopladas: tope de 20 unidades, homogeneidad de titularidad y atomicidad en el cobro diario. El diagrama de clases bajo el patrón Composite, asistido por restricciones OCL, formaliza la arquitectura eliminando cualquier vacío de implementación.

=== b) Eficacia Preventiva del Estándar IEEE Std. 1016-2009 y la Curva de Boehm

El valor central de estructurar un SDD radica en su función preventiva antes de la construcción técnica. De acuerdo con el modelo de costos de corrección de Boehm @pressman2020:
- Detectar y corregir un defecto en la fase de análisis y diseño representa un costo base ($1 times$).
- Si el defecto trasciende a la etapa de construcción, su costo de corrección asciende a $5 times$ - $10 times$.
- Si se detecta durante las pruebas de integración o QA, escala a $20 times$ - $50 times$.
- Si el defecto alcanza un entorno productivo financiero (por ejemplo, permitir débitos inconsistentes en pedidos compuestos o dispensar efectivo sin validar el saldo interbancario), el costo de remediación supera los $100 times$, ocasionando pérdidas financieras y sanciones regulatorias.

La aplicación de la lista de verificación formal (Checklist Fagan / IEEE 1028) permitió validar exhaustivamente que los puntos de vista estuvieran representados y que ninguna entidad careciera de contratos e interfaces explícitas.

=== c) Paradigma Diagrams as Code frente al Modelado Tradicional

El paradigma *Diagrams as Code* (Mermaid y PlantUML) supera las barreras de las herramientas CASE convencionales:
- *Trazabilidad en Control de Versiones:* Los diagramas residen en el repositorio junto a la documentación técnica, permitiendo auditar el historial de cambios y cambios en la arquitectura.
- *Mantenibilidad Continua:* La regeneración vectorial previene la degradación documental (_architecture erosion_), asegurando que los diagramas del sistema permanezcan sincronizados con la evolución del diseño.
