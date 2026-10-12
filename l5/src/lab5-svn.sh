#!/usr/bin/env bash
# Lab 05 - Gestión de Configuración con Subversion (Arch Linux)
# Reproduce las evidencias del informe. Requiere: yay -S subversion meld
set -euo pipefail
BASE=~/lab5-svn
REPO="file://$HOME/lab5-svn/repo"

mkdir -p "$BASE/proyecto-demo/trunk" "$BASE/wc-empty"
echo "Hola SVN L5" > "$BASE/proyecto-demo/trunk/app.txt"
echo "int main(){}" > "$BASE/proyecto-demo/trunk/main.c"
svnadmin create "$BASE/repo"
svn import "$BASE/proyecto-demo" "$REPO" -m "import inicial"          # -> r1
svn checkout "$REPO/trunk" "$BASE/wc"                                 # EJIE 3.2

cd "$BASE/wc"
echo "cambio v2" >> app.txt
echo "nuevo" > feature.txt
svn add feature.txt
svn status
svn commit -m "cambio 1"                                              # -> r2
svn log -v -l 3; svn info

# Escenario concurrencia SVN-BOOK Cap.3 (EJIE 3.5)
svn checkout "$REPO/trunk" "$BASE/wc-alice"
svn checkout "$REPO/trunk" "$BASE/wc-bob"
echo "alice" >> "$BASE/wc-alice/app.txt"; svn commit "$BASE/wc-alice" -m "alice"  # -> r3
echo "bob" >> "$BASE/wc-bob/app.txt"; svn update "$BASE/wc-bob"                   # -> C app.txt
# Resolver a mano (meld) y luego:
# svn resolved "$BASE/wc-bob/app.txt"
# svn commit "$BASE/wc-bob" -m "bob resuelve"

# Historia / etiquetas (cuestionario a-b)
svn log -v --stop-on-copy "$REPO"
svn cat -r 1 "$REPO/trunk/app.txt"
svn diff -r 1:HEAD "$REPO/trunk" || true
svn copy "$REPO/trunk" "$REPO/branches/feat-x" -m "branch" || true
svn copy "$REPO/trunk" "$REPO/tags/v1.0" -m "tag v1.0" || true
svn list -R "$REPO"
