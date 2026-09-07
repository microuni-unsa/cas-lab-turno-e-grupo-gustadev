== I. Objetivos

+ Utilizar adecuadamente herramientas y componentes para generar, administrar y hacer seguimiento a los requerimientos de software a lo largo de su ciclo de vida.
+ Aplicar el ciclo completo de gestión de cambios sobre un sistema informático, asegurando la trazabilidad desde la solicitud inicial (RFC) hasta su despliegue y cierre en la línea base.
+ Desplegar y configurar la herramienta empresarial *i-doit* mediante entornos contenerizados en Docker con soporte de base de datos relacional y servidor web.
+ Implementar un flujo moderno y colaborativo de gestión de cambios empleando *GitHub Projects* y la CLI oficial de GitHub (`gh`) vinculado a una organización académica.
+ Evaluar comparativamente las herramientas de gestión de cambios bajo criterios arquitectónicos, metodológicos y operativos según estándares de la industria @ieee828 @iso29148.

== II. Descripción del Procedimiento Realizado

El desarrollo de la práctica comprendió las siguientes etapas metodológicas:

+ *Aprovisionamiento de Infraestructura Contenerizada (i-doit):*
  Se construyó una imagen personalizada basada en `php:8.3-apache` provista de las extensiones requeridas (`mysqli`, `pdo_mysql`, `ldap`, `gd`, `zip`, entre otras) y se orquestó junto a un contenedor `mariadb:10.11` mediante Docker Compose, habilitando reescritura de URLs (`mod_rewrite`) y persistencia de datos.

+ *Configuración del Repositorio y Entorno Moderno (GitHub Projects):*
  Se inicializó y vinculó el repositorio institucional en la organización `microuni-unsa` (`cas-lab-turno-e-grupo-gustadev`), configurando mediante GitHub CLI (`gh`) las etiquetas, taxonomías y columnas representativas de cada fase del ciclo de vida de un cambio.

+ *Modelado del Flujo de Gestión de Cambios:*
  Se analizó el flujo de control de cambios descrito en el Anexo 21 y la práctica de _Change Enablement_ de ITIL 4 @itil4, formalizando las transiciones válidas: _Pendiente_ $->$ _En revisión_ $->$ _En desarrollo_ $->$ _En pruebas_ $->$ _Cerrado_.

+ *Registro y Trazabilidad de Cambios del Laboratorio 1:*
  A partir de los requerimientos funcionales y de seguridad elaborados en el Laboratorio 1, se redactaron cinco Solicitudes de Cambio (RFC/CHG) con justificación técnica, impacto, responsable asignado y fecha límite, registrándose tanto en la CMDB de i-doit como en el tablero de GitHub Projects.

+ *Simulación del Ciclo de Vida y Generación de Reportes:*
  Se ejecutó el avance y transición de los cambios conforme al progreso de desarrollo y pruebas, emitiendo reportes consolidados para el seguimiento del Comité de Control de Cambios (CCB).
