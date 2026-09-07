== III. Resultados Obtenidos: Ejercicios Resueltos

=== a) Norma IEEE Std 830-1998 y Proceso de Aplicación

La norma IEEE Std 830-1998 @ieee830 establece los lineamientos para estructurar la Especificación de Requisitos Software. Su proceso de aplicación comprende:

*Términos y Roles:*
- *Contrato:* Documento obligatorio entre cliente y proveedor que define alcance técnico, costos, plazos y compromisos.
- *Cliente:* Persona o entidad que financia el producto y establece los requisitos generales.
- *Proveedor:* Organización o equipo responsable del desarrollo del software.
- *Usuario:* Persona que opera de manera directa el producto final.

*Propiedades de Calidad de una Especificación:*
- *Correcta:* Cada enunciado responde a una necesidad real del sistema.
- *No ambigua:* Admite una única interpretación verificable.
- *Completa:* Contiene todos los requerimientos funcionales, de rendimiento y restricciones.
- *Consistente:* No presenta contradicciones entre requerimientos.
- *Clasificada por importancia y estabilidad:* Cuenta con priorización y nivel de volatilidad asignados.
- *Verificable:* Existe un método técnico finito para comprobar su cumplimiento.
- *Modificable:* Su estructura tolera cambios manteniendo la consistencia.
- *Trazable:* Mantiene vínculos explícitos hacia su origen y hacia los artefactos de diseño.

=== b) Identificación de Requerimientos del Caso de Estudio

Para el sistema de gestión y calidad de software se definieron las siguientes entidades:

#table(
  columns: (1.2fr, 2.5fr, 1.5fr),
  align: (center, left, center),
  table.header([*Código*], [*Nombre*], [*Rol*]),
  [STK-01], [Docente Instructor], [Cliente y Evaluador],
  [STK-02], [Equipo de Desarrollo], [Analistas y Desarrolladores],
  [STK-03], [Auditor de Calidad], [Auditor y Evaluador QA],
  [STK-04], [Usuario Final], [Operador del Sistema],
  [ACT-01], [Administrador del Sistema], [Gestión y Accesos],
  [ACT-02], [Ingeniero de Requisitos], [Elicitación y Modelado],
  [ACT-03], [Auditor de Calidad], [Verificación y Validación],
)

*Objetivos del Sistema:*
- *OBJ-01: Estandarización de Requisitos:* Estandarizar la especificación de requisitos del software bajo la norma IEEE Std 830-1998. Prioridad vital, urgencia inmediata, estabilidad alta y estado validado.
- *OBJ-02: Garantizar Trazabilidad Bidireccional:* Asegurar la trazabilidad completa entre objetivos de negocio, requisitos funcionales y casos de uso. Prioridad vital, urgencia inmediata, estabilidad alta y estado validado.
- *OBJ-03: Automatización de Informes y Auditoría:* Automatizar la generación de matrices de trazabilidad y documentación técnica. Prioridad importante, urgencia media, estabilidad media y estado validado.
- *OBJ-04: Gestión de Cambios y Conflictos:* Facilitar el control de versiones y resolución de discrepancias en los requerimientos. Prioridad importante, urgencia media, estabilidad alta y estado validado.

*Requisitos de Información:*
- *RI-01: Registro de Partes Interesadas:* Almacena información de contacto, responsabilidades y clasificación de los participantes.
- *RI-02: Especificación de Requisitos y Atributos:* Ficha técnica con código, nombre, descripción, importancia, urgencia, estabilidad, estado y versión.
- *RI-03: Historial de Cambios y Versionado:* Registro cronológico de modificaciones y solicitudes de cambio.

=== c) Instalación y Configuración de Herramientas CASE

Se analizaron tres herramientas para la gestión de requerimientos:

+ *REM:* Herramienta de escritorio orientada a la norma IEEE Std 830-1998 con persistencia en bases de datos relacionales, validación formal mediante esquemas DTD y generación de matrices de trazabilidad.
+ *OSRMT:* Herramienta de código abierto basada en Java y motores SQL para la gestión jerárquica de requerimientos y casos de uso.
+ *Gatherspace:* Plataforma web en la nube orientada a la gestión ágil de casos de uso e historias de usuario.

#figure(
  image("/l1/img/interfaz-general.jpeg", width: 85%),
  caption: [Interfaz principal de REM con el catálogo de requisitos del proyecto.],
) <fig-rem-arbol>
