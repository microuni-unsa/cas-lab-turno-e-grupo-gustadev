== III. Resultados Obtenidos: Ejercicios Resueltos e Instalación

=== a) Instalación de Subversion y cliente gráfico en Arch Linux

En Arch Linux el servidor y cliente se proveen del paquete `extra/subversion` @subversion-arch y por preferencia se usó RabbitVCS con Thunar y Meld para la evidencia gráfica @rabbitvcs (menú contextual en Thunar, diálogos Checkout/Commit/Log/Browser y comparación con Meld).

Se ejecutó:

```bash
yay -S subversion meld thunar
yay -S rabbitvcs rabbitvcs-thunar thunarx-python python-pysvn
svn --version  # 1.14.5 (r1922182)
thunar -q; thunar ~/lab5-svn &
```

#figure(
  image("/l5/img/yay_install_subversion.png", width: 90%),
  caption: [Instalación de `subversion-1.14.5-5` y `meld` con `yay`.],
) <fig-yay-svn>

#figure(
  image("/l5/img/terminal_subversion_version.png", width: 90%),
  caption: [`svn --version`: cliente 1.14.5 con módulos ra_svn, ra_local (`file://`) y ra_serf (`http://`).],
) <fig-svn-version>

#figure(
  image("/l5/img/yay_install_rabbit_vcs.png", width: 90%),
  caption: [Instalación AUR de `rabbitvcs-0.19`, `rabbitvcs-thunar`, `thunarx-python` y `python-pysvn`.],
) <fig-yay-rabbit>

#figure(
  image("/l5/img/thunar_rabbit_submenu.png", width: 85%),
  caption: [Thunar en `~/lab5-svn/`: menú contextual RabbitVCS SVN/Git/Hg, equivalente al shell-extension de TortoiseSVN.],
) <fig-thunar-menu>

=== b) Funciones elementales del Manual EJIE (import / checkout / commit / log)

Se replicaron las §§3.1–3.4 del Manual EJIE @ejie-tortoise-manual con repositorio local `file://` (sin necesidad de servidor remoto):

```bash
mkdir -p ~/lab5-svn/proyecto-demo/trunk ~/lab5-svn/wc-empty
echo "Hola SVN L5" > ~/lab5-svn/proyecto-demo/trunk/app.txt
echo "int main(){}" > ~/lab5-svn/proyecto-demo/trunk/main.c
svnadmin create ~/lab5-svn/repo
svn import ~/lab5-svn/proyecto-demo file:///home/$USER/lab5-svn/repo -m "import inicial"
svn checkout file:///home/$USER/lab5-svn/repo/trunk ~/lab5-svn/wc
```

#figure(
  image("/l5/img/terminal_subversion_test_commands.png", width: 90%),
  caption: [`svnadmin create` + `svn import` (r1) + `svn checkout` de `trunk` en `~/lab5-svn/wc`.],
) <fig-import-checkout>

#figure(
  image("/l5/img/subversion_checkout_repo_trunk.png", width: 85%),
  caption: [Diálogo Checkout de RabbitVCS: `Updating/Added app.txt, main.c`, `Revision 1 Finished`. Equivale a Tortoise Checkout.],
) <fig-checkout-gui>

#figure(
  image("/l5/img/thunar_rabbit_browser_repo.png", width: 85%),
  caption: [Repo-browser RabbitVCS sobre `file:///.../repo` con selector URL y Revisión HEAD. Equivale al Repository Browser de TortoiseSVN.],
) <fig-repo-browser-empty>

Luego se probó el ciclo `add / status / commit / log / info` (EJIE §§3.3, 3.6–3.9):

```bash
cd ~/lab5-svn/wc
echo "cambio v2" >> app.txt
echo "nuevo" > feature.txt
svn add feature.txt
svn status
svn commit -m "cambio 1"        # -> Committed revision 2
svn log -v -l 3; svn info
```

#figure(
  image("/l5/img/terminal_wc.png", width: 90%),
  caption: [`A feature.txt`, `M app.txt`, commit r2, `svn log -v` y `svn info` (`URL: .../trunk`, `Repository Root: file:///.../repo`).],
) <fig-wc-terminal>

#figure(
  image("/l5/img/commit_dialog_wc.png", width: 85%),
  caption: [Diálogo Commit de RabbitVCS (`Commit to: .../repo/trunk`, mensaje, Changed Files). Equivale al diálogo Commit de TortoiseSVN §4.4.],
) <fig-commit-dialog>

#figure(
  image("/l5/img/commit_log_wc.png", width: 85%),
  caption: [Revisions Table: r2 `cambio 1` y r1 `import inicial` por `gustadev121`. Equivale al diálogo Show Log de TortoiseSVN.],
) <fig-commit-log>
