== III (cont.). Resultados: Ejercicios Propuestos (SVN-BOOK)

=== c) Concurrencia y resolución de conflictos (SVN-BOOK Cap. 3, EJIE §3.5)

Se reprodujo el ejemplo canónico `sandwich.txt` del SVN-BOOK @svnbook con dos copias de trabajo simulando dos desarrolladores sobre el mismo `app.txt`:

```bash
svn checkout file:///home/$USER/lab5-svn/repo/trunk ~/lab5-svn/wc-alice  # r2
svn checkout file:///home/$USER/lab5-svn/repo/trunk ~/lab5-svn/wc-bob    # r2
echo "alice" >> ~/lab5-svn/wc-alice/app.txt; svn commit ~/lab5-svn/wc-alice -m "alice"  # -> r3
echo "bob" >> ~/lab5-svn/wc-bob/app.txt; svn update ~/lab5-svn/wc-bob
# Updating '.../wc-bob':
# C .../wc-bob/app.txt
# Updated to revision 3. / Summary of conflicts: Text conflicts: 1
```

#figure(
  image("/l5/img/merge_conflict_wc.png", width: 90%),
  caption: [Conflicto de texto provocado: `C app.txt`, `Updated to revision 3`, `Text conflicts: 1`, prompt `(p/df/e/m/s)`.],
) <fig-conflict>

La resolución se hizo a mano con Meld como sustituto de TortoiseMerge, y luego `svn resolved` como indica el SVN-BOOK:

#figure(
  image("/l5/img/meld_wc_conflicts.png", width: 90%),
  caption: [Meld con marcadores `<<<<<<< .mine / ||||||| .r2 / ======= / >>>>>>> .r3`: izquierda remote (alice), centro fusionado, derecha local (bob).],
) <fig-meld>

```bash
svn resolved ~/lab5-svn/wc-bob/app.txt
svn commit ~/lab5-svn/wc-bob -m "bob resuelve"
```

=== d) Inspección histórica y estructura del repositorio (EJIE §§3.7–3.8)

#figure(
  image("/l5/img/repo_browser_wc.png", width: 85%),
  caption: [Repo-browser en `.../repo/trunk`: `app.txt` r3 (28 bytes), `feature.txt` r2, `main.c` r1. Prueba de trazabilidad por revisión.],
) <fig-repo-browser>

#figure(
  image("/l5/img/cat_show_diff_wc.png", width: 85%),
  caption: [Comparación `HEAD` vs `Number 1`: `feature.txt delete`, `app.txt modified`. Equivale a TortoiseUDiff / Show differences.],
) <fig-diff>

Comandos equivalentes CLI usados para el cuestionario:

```bash
svn log -v --stop-on-copy
svn cat -r 1 file:///home/$USER/lab5-svn/repo/trunk/app.txt
svn diff -r 1:HEAD file:///home/$USER/lab5-svn/repo/trunk
svn copy .../trunk .../branches/feat-x -m "branch"
svn copy .../trunk .../tags/v1.0 -m "tag"
```

=== e) Cuadro comparativo Subversion vs GitHub

#table(
  columns: (1.2fr, 2fr, 2fr),
  align: (center, left, left),
  table.header([*Aspecto*], [*Subversion (este lab)*], [*Git / GitHub*]),
  [Modelo], [Centralizado: un repositorio `file://`, copias `wc` sin historia @svnbook.], [Distribuido: cada clon `.git` lleva historia completa @github-svn-diffs.],
  [Ramas/tags], [Directorios `trunk/branches/tags`, copia `svn copy`. Revisiones `rN` globales.], [Punteros livianos `main/feature`, `tag vX.Y.Z`, hash SHA. Directorio no cambia, cambia contenido.],
  [Flujo], [`checkout -> edit -> update -> commit` directo al central.], [`clone -> commit local -> push/pull-request -> merge`. Requiere sincronización explícita.],
  [Offline], [Limitado: `status/diff` local sí, `commit/log` exige conexión al central.], [Total: `commit/log/diff/branch` locales sin red.],
  [Permisos], [Granular por path (`authz` por directorio).], [Por repositorio completo; sin restricción por subdirectorio.],
  [Binarios], [Eficiente (delta + `svn:needs-lock`). Bueno para assets grandes.], [Costoso (cada cambio engorda `.git`; se necesita LFS).],
  [Historia], [Inmutable y lineal `r1 < r2 < r3`. `svn log` solo en servidor.], [Reescribible (`rebase --amend`). Cada clon es respaldo.],
)
