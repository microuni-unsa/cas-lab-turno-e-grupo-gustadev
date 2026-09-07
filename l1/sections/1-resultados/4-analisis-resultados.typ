== IV. Análisis de Resultados

El análisis del proceso de especificación y trazabilidad de requerimientos arrojó los siguientes resultados técnicos:

+ *Mitigación de Problemas de Elicitación:*
  - *Problemas de Alcance:* Se delimitaron mediante la definición previa de objetivos del sistema y actores, estableciendo las fronteras del software antes de redactar los requerimientos funcionales detallados.
  - *Problemas de Comprensión:* Se resolvieron aplicando la estructura de la norma IEEE Std 830-1998, asignando criterios de aceptación cuantificables en los requisitos no funcionales y estados formales de ciclo de vida.
  - *Problemas de Volatilidad:* Se mitigaron mediante el control de versiones en cada entidad, el registro de modificaciones y la clasificación por estabilidad.

+ *Eficacia de la Matriz de Trazabilidad Bidireccional:*
  - La vinculación entre objetivos, requerimientos funcionales, no funcionales, restricciones y casos de uso asegura una cobertura total sin presencia de requerimientos huérfanos.

*Matriz de Trazabilidad Bidireccional:*

#table(
  columns: (1.8fr, 0.8fr, 0.8fr, 0.8fr, 0.8fr, 0.8fr, 0.8fr, 0.8fr, 0.8fr, 0.8fr, 1.2fr),
  align: (left, center, center, center, center, center, center, center, center, center, center),
  table.header(
    [*Objetivo del Sistema*],
    [*RF-01*], [*RF-02*], [*RF-03*], [*RF-04*], [*RF-05*], [*RF-06*],
    [*CU-01*], [*CU-02*], [*CU-03*],
    [*Cobertura*]
  ),
  [OBJ-01: Estandarización IEEE 830], [X], [X], [ ], [ ], [ ], [ ], [X], [ ], [ ], [100%],
  [OBJ-02: Trazabilidad Bidireccional], [ ], [ ], [X], [ ], [ ], [ ], [ ], [X], [ ], [100%],
  [OBJ-03: Automatización de Informes], [ ], [ ], [ ], [ ], [X], [X], [ ], [ ], [X], [100%],
  [OBJ-04: Gestión de Cambios y Versiones], [ ], [ ], [ ], [X], [ ], [ ], [X], [ ], [ ], [100%],
)

*Mapeo y Dependencias Directas entre Artefactos:*

#table(
  columns: (1.2fr, 1.8fr, 1.2fr, 1.4fr, 2.4fr),
  align: (center, left, center, center, left),
  table.header([*Objetivo*], [*Requerimiento Funcional*], [*Caso de Uso*], [*RNF / Restricción*], [*Criterio de Verificación y Justificación*]),
  [OBJ-01], [RF-01: Elicitación y Registro \ RF-02: Clasificación IEEE 830], [CU-01], [RNF-03: Usabilidad \ RC-01: Esquema REM], [Garantiza el ingreso no ambiguo y estandarizado de requisitos.],
  [OBJ-02], [RF-03: Trazabilidad Bidireccional], [CU-02], [RNF-01: Rendimiento], [Permite auditar el impacto hacia atrás y adelante en menos de 2 segundos.],
  [OBJ-03], [RF-05: Validación Automática \ RF-06: Exportación ERS], [CU-03], [RC-01: Esquema XML], [Comprueba consistencia estructural y exporta documentos formales.],
  [OBJ-04], [RF-04: Control de Cambios y Versiones], [CU-01], [RNF-02: Seguridad RBAC], [Asegura la trazabilidad histórica de versiones aprobadas.],
)
