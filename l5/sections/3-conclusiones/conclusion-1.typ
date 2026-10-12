= V. Conclusiones

+ *Gestión de configuración con el cliente preferido:*
  Por preferencia se usó RabbitVCS con Thunar y Meld: la combinación `subversion 1.14.5 CLI + RabbitVCS/Thunar + Meld` cubre todas las funciones elementales del Manual EJIE (import, checkout, commit, update, log, diff, revert, browser). La instalación vía `yay` quedó documentada y reproducible.

+ *Dominio del ciclo centralizado:*
  La ejecución del SVN-BOOK (revisiones atómicas `r1–r3`, conflicto `C`, `resolved`, inspección `cat/diff -r` y layout `trunk/branches/tags`) demuestra que el cambio es trazable y recuperable a cualquier nivel, que la revisión es global al árbol y que la etiqueta legible se construye con `svn copy` a `tags/vX.Y.Z`.

+ *Criterio SVN vs GitHub:*
  SVN es adecuado cuando se requiere control centralizado simple, permisos por path y manejo de binarios; Git/GitHub es superior para trabajo offline, branching masivo y resiliencia distribuida. La elección depende del contexto organizacional, no de superioridad absoluta @github-svn-diffs @sommerville2011 @pressman2014.
