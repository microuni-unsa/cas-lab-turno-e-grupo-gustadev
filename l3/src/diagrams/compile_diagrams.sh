#!/usr/bin/env bash
set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
IMG_DIR="$(cd "$SCRIPT_DIR/../../img" && pwd)"

mkdir -p "$IMG_DIR"

echo "========================================================"
echo "Compilando diagramas Mermaid a SVG y PNG con mmdc..."
echo "========================================================"

for file in "$SCRIPT_DIR"/*.mmd; do
    basename=$(basename "$file" .mmd)
    echo "Procesando: $basename"
    
    # Generar versión SVG (vectorial nítida para Typst)
    mmdc -i "$file" -o "$IMG_DIR/${basename}.svg" -b transparent
    
    # Generar versión PNG de alta resolución (scale 2)
    mmdc -i "$file" -o "$IMG_DIR/${basename}.png" -b white -s 2
done

echo "========================================================"
echo "Compilación de diagramas completada exitosamente en:"
echo "$IMG_DIR"
echo "========================================================"
