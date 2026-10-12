== IV. Análisis de Resultados

+ *Cliente gráfico usado:*
  Por preferencia se usó RabbitVCS con Thunar y Meld: reproduce los diálogos clave del Manual EJIE @ejie-tortoise-manual (Checkout, Commit, Log/Revisions Table, Repo-browser, Diff) y Meld cubre la comparación/fusión. El CLI (`svn status/info/log`) se usó como verificación autoritativa junto a la evidencia gráfica.

+ *Atomicidad y revisiones globales:*
  Cada `svn commit` creó una revisión atómica del árbol (`r1 import`, `r2 cambio 1`, `r3 alice`). A diferencia de Git, no hay staging ni hash por archivo: `svn log -v` muestra `A/M` por path y `svn info` fija `URL` y `Repository Root`, lo que simplifica auditoría pero impide trabajo offline con historia.

+ *Conflictos como mecanismo pedagógico:*
  El conflicto `C app.txt` demuestra el modelo copiar-modificar-fusionar @svnbook: `update` no sobrescribe, genera `.mine/.rOLD/.rNEW` y exige edición manual + `resolved`. Meld hizo visible la triple marca, confirmando que la fusión es responsabilidad del desarrollador, no del servidor.

+ *Trazabilidad para el cuestionario:*
  `repo-browser (r3/r2/r1)` y `diff HEAD vs r1 (delete/modified)` prueban recuperación arbitraria (`cat/checkout -r`) y la convención `trunk/branches/tags + rN`, base de las respuestas del cuestionario.

+ *SVN vs GitHub:*
  SVN gana en simplicidad operativa, control de acceso por path y binarios; GitHub gana en branching liviano, trabajo offline y resiliencia (cada clon es backup) @github-svn-diffs. Para gestión de configuración centralizada de laboratorio, SVN es suficiente; para equipos distribuidos grandes, Git es superior.
