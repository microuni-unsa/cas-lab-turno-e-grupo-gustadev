= Pregunta a): ¿Hasta qué nivel de recuperación de versiones pasadas se puede realizar?

Hasta cualquier revisión persistida en el repositorio, desde `r0` (raíz vacía) hasta `HEAD`, sin límite de profundidad impuesto por Subversion @svnbook @ejie-tortoise-manual:

+ *Nivel archivo:* `svn cat -r N URL`, `svn diff -r M:N`, `svn checkout -r N URL /tmp/rN`.
+ *Nivel copia de trabajo:* `svn update -r N` (time-travel de todo el árbol), `svn revert` (descarta cambios no commiteados volviendo a la copia prístina `.svn`).
+ *Nivel histórico:* `svn log -v --stop-on-copy` lista cada transacción atómica con autor, fecha y paths `A/M/D`.

En la práctica del laboratorio se verificó con `app.txt r1 -> r2 -> r3`: el Repo-browser mostró las tres revisiones vivas (@fig-repo-browser) y el diff `HEAD vs 1` reportó `feature.txt delete / app.txt modified` (@fig-diff), demostrando que `r1` sigue recuperable aun después de `r3`. El único límite real es operativo: disponibilidad del repositorio (backup de `db/`), políticas de `svnadmin dump/filter` y espacio en disco. A diferencia de Git, no hay `rebase` destructivo por defecto: la historia central es inmutable y lineal.

#figure(
  image("/l5/img/repo_browser_wc.png", width: 78%),
  caption: [Evidencia: `app.txt` recuperable en r1, r2 y r3 desde el Repo-browser.],
)
