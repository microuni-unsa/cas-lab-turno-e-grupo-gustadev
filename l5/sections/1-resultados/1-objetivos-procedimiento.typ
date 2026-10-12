== I. Objetivos

+ Instalar, probar y evaluar Subversion como herramienta de control de versiones para la gestión de configuración de software, siguiendo el objetivo del laboratorio.
+ Analizar el modelo centralizado de Subversion (repositorio, copia de trabajo, revisiones) frente al modelo distribuido de Git/GitHub @svnbook @github-svn-diffs.
+ Ejecutar el ciclo básico SVN-BOOK (import, checkout, add, commit, update, status, diff, log, revert, branch/tag, merge) y las funciones elementales del Manual EJIE v1.1 @ejie-tortoise-manual en Arch Linux con cliente CLI + RabbitVCS con Thunar y Meld por preferencia @rabbitvcs @subversion-arch.
+ Responder el cuestionario de recuperación de versiones, etiquetado y diagrama de actualización.

== II. Descripción del Procedimiento

El desarrollo se organizó en cuatro fases; por preferencia se usó RabbitVCS con Thunar y Meld para la evidencia gráfica:

+ *Instalación y verificación:* `yay -S subversion meld` y `yay -S rabbitvcs rabbitvcs-thunar thunarx-python python-pysvn`. Verificación con `svn --version` y menú contextual RabbitVCS SVN en Thunar.
+ *Ejercicio resuelto + Manual EJIE §§3.1–3.11:* creación de repositorio local `svnadmin create`, `svn import`, `svn checkout`, ciclo `add / status / commit / update / info / log / diff / revert / cleanup`, con evidencia CLI y GUI (Commit dialog, Revisions Table, Repo-browser).
+ *Actividades SVN-BOOK Cap. 2–3:* escenario de concurrencia con dos copias `wc-alice` y `wc-bob` sobre `app.txt` para provocar conflicto `C`, resolución con Meld (equivalente a TortoiseMerge) y `svn resolved`, más inspección histórica `cat -r`, `diff HEAD vs r1` y estructura `trunk/branches/tags`.
+ *Comparativa y cuestionario:* cuadro SVN vs Git/GitHub y respuestas sobre nivel de recuperación, convención de nombres/etiquetas y diagrama de actualización.
